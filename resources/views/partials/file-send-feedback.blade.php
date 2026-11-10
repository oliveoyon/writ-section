@php
    $sendSummary = session('transfer_summary', []);
    $isHandoverResult = request()->routeIs('admin.tracking.handover.create') && is_array($sendSummary) && !empty($sendSummary);
    $isCourtResult = request()->routeIs('admin.tracking.court.dispatch.index') && session()->has('court_processed');
    $showSendFeedback = session('success') && ($isHandoverResult || $isCourtResult);
@endphp
@if($showSendFeedback)
    @php
        $sentCount = $isHandoverResult ? (int) ($sendSummary['sent_count'] ?? 0) : count(session('court_processed', []));
        $failedCount = count($isHandoverResult ? ($sendSummary['failed'] ?? []) : session('court_failed', []));
        $hasSendFailures = $failedCount > 0;
    @endphp
    <div class="modal fade" id="fileSendFeedback" tabindex="-1" aria-labelledby="fileSendFeedbackTitle"
        aria-describedby="fileSendFeedbackMessage" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-body text-center p-4 p-sm-5">
                    <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 88px; height: 88px; background: {{ $hasSendFailures ? '#fff3cd' : '#dcfce7' }}; color: {{ $hasSendFailures ? '#854d0e' : '#166534' }};">
                        @if($hasSendFailures)
                            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 5v9"/><circle cx="12" cy="19" r="1" fill="currentColor" stroke="none"/></svg>
                        @else
                            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        @endif
                    </div>
                    <h2 id="fileSendFeedbackTitle" class="fw-bold mb-3" style="font-size: clamp(1.6rem, 5vw, 2rem);">
                        {{ __($hasSendFailures ? 'messages.send_partial_title' : 'messages.send_success_title') }}
                    </h2>
                    <div id="fileSendFeedbackMessage" style="font-size: 1.2rem; line-height: 1.6; overflow-wrap: anywhere;">
                        <p class="fw-bold mb-2">{{ __('messages.send_count', ['count' => $sentCount]) }}</p>
                        <p class="mb-3">{{ session('success') }}</p>
                        @if($hasSendFailures)
                            <p class="fw-bold p-3 rounded" style="background: #fff3cd; color: #854d0e;">{{ __('messages.send_failed_count', ['count' => $failedCount]) }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn btn-lg w-100 fw-bold mt-2" data-bs-dismiss="modal"
                        style="background: #166534; color: white; min-height: 56px; font-size: 1.25rem;">
                        {{ __('messages.send_feedback_ok') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const feedback = document.getElementById('fileSendFeedback');
            if (!feedback || !window.bootstrap) return;
            feedback.addEventListener('shown.bs.modal', function () {
                feedback.querySelector('[data-bs-dismiss="modal"]').focus();
            });
            feedback.addEventListener('hidden.bs.modal', function () {
                const nextInput = document.getElementById('barcodeInput');
                if (nextInput) nextInput.focus();
            });
            bootstrap.Modal.getOrCreateInstance(feedback).show();
        });
    </script>
@endif
