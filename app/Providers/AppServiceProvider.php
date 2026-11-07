<?php

namespace App\Providers;

use App\Models\CourtCase;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(125);
        Paginator::useBootstrapFive();
        
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        View::composer('admin.layouts.adminlayout', function ($view): void {
            $user = Auth::user();
            $showWorkspaceBar = $user
                && in_array($user->user_type, ['admin', 'staff'], true)
                && ! $user->hasRole('Super Admin')
                && $user->department !== null;

            if (! $showWorkspaceBar) {
                $view->with('workspaceBar', null);

                return;
            }

            $viewData = $view->getData();
            $incomingCount = array_key_exists('incomingPending', $viewData)
                ? (int) $viewData['incomingPending']
                : FileTransferItem::query()
                    ->join('file_transfer_batches', 'file_transfer_batches.id', '=', 'file_transfer_items.batch_id')
                    ->where('file_transfer_items.status', FileTransferItem::STATUS_PENDING)
                    ->where('file_transfer_batches.recipient_user_id', $user->id)
                    ->whereIn('file_transfer_batches.status', [
                        FileTransferBatch::STATUS_PENDING,
                        FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
                    ])
                    ->count();

            $custodyCount = array_key_exists('heldFiles', $viewData)
                ? (int) $viewData['heldFiles']
                : CourtCase::where('current_holder_user_id', $user->id)->count();

            $view->with('workspaceBar', [
                'custody_count' => $custodyCount,
                'incoming_count' => $incomingCount,
            ]);
        });
    }
}
