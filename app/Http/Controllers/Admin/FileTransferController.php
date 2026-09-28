<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\FileTransferException;
use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use App\Services\FileTransferService;
use App\Services\RtftsCaseReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileTransferController extends Controller
{
    public function __construct(private readonly FileTransferService $transfers)
    {
    }

    public function workspace(Request $request): View
    {
        $user = $request->user();
        $section = $user->departmentRelation?->label ?? 'Unassigned Section';
        $isFiling = str_contains(strtolower($section), 'filing');

        $incomingPending = FileTransferItem::query()
            ->join('file_transfer_batches', 'file_transfer_batches.id', '=', 'file_transfer_items.batch_id')
            ->where('file_transfer_items.status', FileTransferItem::STATUS_PENDING)
            ->where('file_transfer_batches.recipient_user_id', $user->id)
            ->whereIn('file_transfer_batches.status', [
                FileTransferBatch::STATUS_PENDING,
                FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
            ])
            ->count();

        $outgoingPending = FileTransferItem::query()
            ->join('file_transfer_batches', 'file_transfer_batches.id', '=', 'file_transfer_items.batch_id')
            ->where('file_transfer_items.status', FileTransferItem::STATUS_PENDING)
            ->where('file_transfer_batches.sender_user_id', $user->id)
            ->whereIn('file_transfer_batches.status', [
                FileTransferBatch::STATUS_PENDING,
                FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
            ])
            ->count();

        $heldFiles = CourtCase::query()
            ->where('current_holder_user_id', $user->id)
            ->count();

        $incomingBatches = FileTransferBatch::query()
            ->select([
                'id',
                'batch_no',
                'sender_name',
                'sender_section',
                'sent_at',
                'status',
            ])
            ->where('recipient_user_id', $user->id)
            ->whereIn('status', [
                FileTransferBatch::STATUS_PENDING,
                FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
            ])
            ->whereHas('pendingItems')
            ->withCount('pendingItems')
            ->oldest('sent_at')
            ->limit(6)
            ->get();

        $hasMoreIncomingBatches = $incomingBatches->count() > 5;
        $incomingBatches = $incomingBatches->take(5);

        $outgoingBatches = FileTransferBatch::query()
            ->select([
                'id',
                'batch_no',
                'recipient_name',
                'recipient_section',
                'sent_at',
                'status',
            ])
            ->where('sender_user_id', $user->id)
            ->whereIn('status', [
                FileTransferBatch::STATUS_PENDING,
                FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
            ])
            ->whereHas('pendingItems')
            ->withCount('pendingItems')
            ->oldest('sent_at')
            ->limit(6)
            ->get();

        $hasMoreOutgoingBatches = $outgoingBatches->count() > 5;
        $outgoingBatches = $outgoingBatches->take(5);

        $receiveRoute = route('admin.tracking.section.receive');

        return view('admin.tracking.handover.workspace', compact(
            'section',
            'incomingPending',
            'outgoingPending',
            'heldFiles',
            'incomingBatches',
            'hasMoreIncomingBatches',
            'outgoingBatches',
            'hasMoreOutgoingBatches',
            'receiveRoute',
            'isFiling'
        ));
    }

    public function recipients(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q', ''));

        $recipients = $this->transfers
            ->eligibleRecipientQuery($user)
            ->select('users.id', 'users.name', 'users.employee_id', 'users.department')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$this->escapeLike($search).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('users.name', 'like', $like)
                        ->orWhere('users.employee_id', 'like', $like)
                        ->orWhereHas('departmentRelation', function ($department) use ($like) {
                            $department->where('name', 'like', $like)
                                ->orWhere('display_name', 'like', $like);
                        });
                });
            })
            ->orderBy('users.department')
            ->orderBy('users.name')
            ->paginate(24)
            ->withQueryString();

        return view('admin.tracking.handover.recipients', compact('recipients', 'search'));
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $direction = (string) $request->query('direction', 'incoming');
        $allowedDirections = $isSuperAdmin ? ['incoming', 'outgoing', 'all'] : ['incoming', 'outgoing'];
        if (!in_array($direction, $allowedDirections, true)) {
            $direction = 'incoming';
        }

        $search = trim((string) $request->query('q', ''));
        $searchCaseId = null;
        if ($search !== '' && ($parsed = RtftsCaseReference::parseIdentifier($search))) {
            $searchCaseId = CourtCase::query()
                ->where('permanent_barcode', $parsed['barcode'])
                ->value('id');
        }

        $batches = FileTransferBatch::query()
            ->select([
                'id',
                'batch_no',
                'sender_user_id',
                'recipient_user_id',
                'sender_name',
                'sender_section',
                'recipient_name',
                'recipient_section',
                'status',
                'sent_at',
                'completed_at',
            ])
            ->withCount([
                'items',
                'pendingItems',
                'items as received_items_count' => fn ($query) => $query->where('status', FileTransferItem::STATUS_RECEIVED),
                'items as cancelled_items_count' => fn ($query) => $query->where('status', FileTransferItem::STATUS_CANCELLED),
            ])
            ->when($direction === 'incoming', fn ($query) => $query->where('recipient_user_id', $user->id))
            ->when($direction === 'outgoing', fn ($query) => $query->where('sender_user_id', $user->id))
            ->when($search !== '', function ($query) use ($search, $searchCaseId) {
                $batchPrefix = $this->escapeLike($search).'%';
                $query->where(function ($inner) use ($batchPrefix, $searchCaseId) {
                    $inner->where('batch_no', 'like', $batchPrefix);
                    if ($searchCaseId) {
                        $inner->orWhereHas('items', fn ($items) => $items->where('case_id', $searchCaseId));
                    }
                });
            })
            ->latest('sent_at')
            ->latest('id')
            ->cursorPaginate(20)
            ->withQueryString();

        return view('admin.tracking.handover.index', compact(
            'batches',
            'direction',
            'search',
            'isSuperAdmin'
        ));
    }

    public function show(Request $request, FileTransferBatch $transferBatch): View
    {
        $this->ensureCanView($request->user(), $transferBatch);
        $transferBatch->loadCount([
            'items',
            'pendingItems',
            'items as received_items_count' => fn ($query) => $query->where('status', FileTransferItem::STATUS_RECEIVED),
            'items as cancelled_items_count' => fn ($query) => $query->where('status', FileTransferItem::STATUS_CANCELLED),
        ]);

        $items = FileTransferItem::query()
            ->with([
                'courtCase:id,final_case_number,permanent_barcode,current_section,current_holder_user_id',
                'receivedBy:id,name,employee_id',
                'cancelledBy:id,name,employee_id',
            ])
            ->where('batch_id', $transferBatch->id)
            ->orderBy('status')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $canCancel = (int) $transferBatch->sender_user_id === (int) $request->user()->id
            || $request->user()->hasRole('Super Admin');

        return view('admin.tracking.handover.show', compact('transferBatch', 'items', 'canCancel'));
    }

    public function cancelBatch(Request $request, FileTransferBatch $transferBatch): RedirectResponse
    {
        $this->ensureCanView($request->user(), $transferBatch);
        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        try {
            $this->transfers->cancelPendingBatch($request->user(), $transferBatch, $validated['reason']);
        } catch (FileTransferException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'All remaining pending files were cancelled.');
    }

    public function cancelItem(
        Request $request,
        FileTransferBatch $transferBatch,
        FileTransferItem $transferItem
    ): RedirectResponse {
        $this->ensureCanView($request->user(), $transferBatch);
        abort_unless((int) $transferItem->batch_id === (int) $transferBatch->id, 404);
        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        try {
            $this->transfers->cancelPendingItem(
                $request->user(),
                $transferBatch,
                $transferItem,
                $validated['reason']
            );
        } catch (FileTransferException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'The pending file was cancelled.');
    }

    public function create(Request $request, User $recipient): View
    {
        $recipient = $this->eligibleRecipient($request->user(), $recipient);

        return view('admin.tracking.handover.send', compact('recipient'));
    }

    public function validateIdentifier(Request $request, User $recipient): JsonResponse
    {
        $recipient = $this->eligibleRecipient($request->user(), $recipient);
        $identifier = trim((string) $request->query('identifier', ''));
        $case = $this->findPermanentCase($identifier);

        if (!$case) {
            return response()->json([
                'valid' => false,
                'message' => 'Valid RTFTS Case No. or barcode not found.',
            ], 422);
        }

        if ($message = $this->transfers->sendRestrictionMessage($case, $request->user())) {
            return response()->json(['valid' => false, 'message' => $message], 422);
        }

        return response()->json([
            'valid' => true,
            'case_id' => $case->id,
            'permanent_barcode' => $case->permanent_barcode,
            'case_number' => $case->case_reference,
            'recipient' => $recipient->name,
        ]);
    }

    public function store(Request $request, User $recipient): RedirectResponse
    {
        $recipient = $this->eligibleRecipient($request->user(), $recipient);
        $validated = $request->validate([
            'case_ids' => 'required|array|min:1|max:'.FileTransferService::MAX_FILES_PER_BATCH,
            'case_ids.*' => 'required|integer|distinct',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->transfers->send(
                $request->user(),
                $recipient,
                $validated['case_ids'],
                $validated['notes'] ?? null
            );
        } catch (FileTransferException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        /** @var FileTransferBatch|null $batch */
        $batch = $result['batch'];
        $sentCount = $result['sent']->count();
        $failedCount = count($result['failed']);

        $message = $sentCount.' file(s) sent to '.$recipient->name.'.';
        if ($failedCount > 0) {
            $message .= ' '.$failedCount.' file(s) could not be sent.';
        }

        if (!$batch) {
            $message = 'No files were sent. Please review the results below.';
        }

        return redirect()
            ->route('admin.tracking.handover.create', $recipient)
            ->with($batch ? 'success' : 'error', $message)
            ->with('transfer_summary', [
                'batch_no' => $batch?->batch_no,
                'recipient' => $recipient->name,
                'recipient_section' => $recipient->departmentRelation?->label,
                'sent_count' => $sentCount,
                'failed' => $result['failed'],
                'sent_at' => $batch?->sent_at?->format('d-m-Y h:i A'),
            ]);
    }

    private function eligibleRecipient(User $sender, User $recipient): User
    {
        return $this->transfers
            ->eligibleRecipientQuery($sender)
            ->whereKey($recipient->id)
            ->firstOrFail();
    }

    private function ensureCanView(User $user, FileTransferBatch $transferBatch): void
    {
        abort_unless(
            (int) $transferBatch->sender_user_id === (int) $user->id
                || (int) $transferBatch->recipient_user_id === (int) $user->id
                || $user->hasRole('Super Admin'),
            403
        );
    }

    private function findPermanentCase(string $identifier): ?CourtCase
    {
        $parsed = RtftsCaseReference::parseIdentifier($identifier);
        if (!$parsed) {
            return null;
        }

        return CourtCase::query()
            ->where('permanent_barcode', $parsed['barcode'])
            ->first();
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
