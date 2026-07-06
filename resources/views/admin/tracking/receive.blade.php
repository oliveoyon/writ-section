@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 receive-page">
    @php($isOfficeAssistant = str_contains(strtolower($section), 'office assistant'))
    <div class="receive-toolbar d-flex align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan receive-heading-icon" aria-hidden="true"></i>
            <h3 class="receive-heading mb-0">{{ auth()->user()->name }}: {{ __('tracking.receive.title') }}</h3>
        </div>
        @if($isOfficeAssistant)
            <div class="kiosk-actions d-flex gap-2">
                <a href="{{ route('admin.tracking.court.dispatch.index') }}" class="btn kiosk-action btn-send">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    <span>{{ __('tracking.receive.send_to_court') }}</span>
                </a>
                <a href="{{ route('admin.tracking.register-report') }}" class="btn kiosk-action btn-report">
                    <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
                    <span>{{ __('tracking.receive.report') }}</span>
                </a>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="receive-workspace">
        <form method="POST" action="{{ route('admin.tracking.section.receive.store') }}">
                @csrf
                <input type="hidden" name="action" value="receive">

                <label for="barcode_input" class="visually-hidden">{{ __('tracking.receive.identifier_label') }}</label>
                <div class="input-group kiosk-input-group kiosk-scan-focus">
                    <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                    <input type="text" id="barcode_input" class="form-control form-control-lg" placeholder="{{ __('tracking.receive.barcode_placeholder') }}" aria-describedby="barcodeInputError" autofocus>
                    <button type="button" id="addBarcodeBtn" class="btn btn-brand px-4">
                        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('tracking.receive.add_barcode') }}
                    </button>
                </div>
                <div id="barcodeInputError" class="text-danger fw-semibold mt-2 d-none" role="alert"></div>

                <input type="hidden" id="barcodes" name="barcodes" value="{{ old('barcodes') }}">

                <div class="table-responsive receive-queue mt-4">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>{{ __('tracking.receive.barcode') }}</th>
                                <th style="width: 120px;">{{ __('tracking.receive.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="barcodeRows">
                            <tr id="emptyRow">
                                <td colspan="3" class="text-center text-muted">{{ __('tracking.receive.none') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <label for="reason_receive" class="form-label">{{ __('tracking.receive.reason_optional') }}</label>
                    <input id="reason_receive" name="reason" class="form-control" value="{{ old('reason') }}">
                </div>

                <button type="submit" class="btn btn-brand btn-lg receive-submit mt-4">
                    <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>{{ __('tracking.receive.submit_bulk') }}
                </button>
        </form>
    </div>

    @if ($isAffidavit)
        <div class="reject-workspace mt-4 pt-4 border-top">
                <form method="POST" action="{{ route('admin.tracking.section.receive.store') }}">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <h5 class="mb-3">{{ __('tracking.receive.reject_title') }}</h5>

                    <label for="barcode" class="form-label">{{ __('tracking.receive.identifier_label') }}</label>
                    <input
                        type="text"
                        id="barcode"
                        name="barcode"
                        class="form-control"
                        placeholder="{{ __('tracking.receive.identifier_placeholder') }}"
                        pattern="(?:13[0-9]{10}|WRPET\s+[0-9]+/[0-9]{4})"
                        title="{{ __('tracking.receive.invalid_identifier_format') }}"
                        required
                    >

                    <div class="mt-3">
                        <label for="reason" class="form-label">{{ __('tracking.receive.reason_optional') }}</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg mt-3">{{ __('tracking.receive.submit_reject') }}</button>
                </form>
        </div>
    @endif

    @php($summary = session('receive_summary'))
    @if($summary && is_array($summary))
        <div class="card p-4 mt-3" id="receivePrintArea">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">{{ __('tracking.receive.receipt_title') }}</h5>
                </div>
                <button type="button" class="btn btn-gold no-print" onclick="window.print()">
                    {{ __('tracking.receive.print_copy') }}
                </button>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_section') }}:</strong> {{ $summary['section'] ?? '-' }}</div>
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_user') }}:</strong> {{ $summary['received_by'] ?? '-' }}</div>
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_time') }}:</strong> {{ $summary['received_at'] ?? '-' }}</div>
            </div>

            <h6 class="mb-2">{{ __('tracking.receive.received_files') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('tracking.receive.barcode') }}</th>
                            <th>{{ __('tracking.receive.receipt_case_no') }}</th>
                            <th>{{ __('tracking.receive.receipt_from') }}</th>
                            <th>{{ __('tracking.receive.receipt_to') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($summary['received'] ?? []) as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['barcode'] ?? '-' }}</td>
                                <td>{{ $item['case_no'] ?? '-' }}</td>
                                <td>{{ $item['from_section'] ?? '-' }}</td>
                                <td>{{ $item['to_section'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ __('tracking.receive.none') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($summary['failed']))
                <h6 class="mt-3 mb-2">{{ __('tracking.receive.failed_files') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('tracking.receive.barcode') }}</th>
                                <th>{{ __('tracking.receive.fail_reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['failed'] as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['barcode'] ?? '-' }}</td>
                                    <td>{{ $item['reason'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="row mt-4 pt-4 border-top">
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div>{{ __('tracking.receive.receiver_signature') }}</div>
                </div>
                <div class="col-md-6 text-center mt-4 mt-md-0">
                    <div class="signature-line"></div>
                    <div>{{ __('tracking.receive.supervisor_signature') }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .receive-page { max-width: 1040px; }
    .receive-heading { font-size: 1.35rem; font-weight: 650; color: #1f2937; }
    .receive-heading-icon { color: #00284d; font-size: 1.45rem; }
    .kiosk-action { min-height: 44px; display: inline-flex; align-items: center; gap: .45rem; color: #fff; font-weight: 600; }
    .kiosk-action:hover, .kiosk-action:focus-visible { color: #fff; }
    .btn-send { background: #0f766e; border-color: #0f766e; }
    .btn-report { background: #2563eb; border-color: #2563eb; }
    .btn-send:hover, .btn-send:focus-visible { background: #0b5f59; border-color: #0b5f59; }
    .btn-report:hover, .btn-report:focus-visible { background: #1d4ed8; border-color: #1d4ed8; }
    .receive-workspace { border-top: 1px solid #e5e7eb; padding-top: 1.5rem; }
    .kiosk-input-group .form-control,
    .kiosk-input-group .input-group-text,
    .kiosk-input-group .btn { min-height: 58px; }
    .kiosk-input-group .form-control { font-size: 1.1rem; }
    .kiosk-scan-focus { border: 2px solid #0f766e; border-radius: .5rem; box-shadow: 0 0 0 4px rgba(15, 118, 110, .1); }
    .kiosk-scan-focus .input-group-text,
    .kiosk-scan-focus .form-control,
    .kiosk-scan-focus .btn { border: 0; }
    .kiosk-scan-focus:focus-within { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .receive-queue { min-height: 150px; border: 1px solid #e5e7eb; }
    .receive-queue thead th { background: #f7f8fa; color: #4b5563; font-size: .8rem; text-transform: uppercase; border-bottom-width: 1px; }
    .receive-queue td, .receive-queue th { padding: .8rem; }
    .queue-barcode { margin-top: .15rem; color: #6b7280; font-size: .8rem; font-family: monospace; }
    .receive-submit { min-width: 210px; min-height: 54px; }
    .reject-workspace { max-width: 680px; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    .signature-line { border-bottom: 1px solid #222; height: 28px; margin-bottom: 8px; }
    @media (max-width: 575.98px) {
        .receive-page { padding-top: 1rem !important; }
        .receive-toolbar { align-items: stretch !important; flex-direction: column; }
        .kiosk-actions { display: grid !important; grid-template-columns: 1fr 1fr; }
        .kiosk-action { justify-content: center; }
        .kiosk-input-group { flex-wrap: wrap; }
        .kiosk-input-group .input-group-text { display: none; }
        .kiosk-input-group .form-control { width: 100%; border-radius: .375rem !important; }
        .kiosk-input-group .btn { width: 100%; margin-top: .5rem; border-radius: .375rem !important; }
        .receive-submit { width: 100%; }
    }
    @media print {
        .navbar, footer, .no-print { display: none !important; }
        main { padding-top: 0 !important; }
        #receivePrintArea { border: 0 !important; box-shadow: none !important; }
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        const barcodeInput = document.getElementById('barcode_input');
        const addBtn = document.getElementById('addBarcodeBtn');
        const rows = document.getElementById('barcodeRows');
        const hiddenBarcodes = document.getElementById('barcodes');
        const receiveForm = hiddenBarcodes.closest('form');
        const emptyRowId = 'emptyRow';
        const barcodes = [];
        const inputError = document.getElementById('barcodeInputError');
        const permanentBarcodePattern = /^13\d{10}$/;
        const finalCasePattern = /^WRPET\s+\d+\/\d{4}$/i;
        const validateIdentifierUrl = @json(route('admin.tracking.movement.validate-identifier'));

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function syncHiddenField() {
            hiddenBarcodes.value = barcodes.map(item => item.barcode).join('\n');
        }

        function drawRows() {
            rows.innerHTML = '';

            if (barcodes.length === 0) {
                const tr = document.createElement('tr');
                tr.id = emptyRowId;
                tr.innerHTML = '<td colspan="3" class="text-center text-muted">{{ __('tracking.receive.none') }}</td>';
                rows.appendChild(tr);
                return;
            }

            barcodes.forEach((item, index) => {
                const secondary = item.label !== item.barcode
                    ? `<div class="queue-barcode">${escapeHtml(item.barcode)}</div>`
                    : '';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td><strong>${escapeHtml(item.label)}</strong>${secondary}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger removeBarcodeBtn" data-index="${index}" title="{{ __('tracking.receive.remove_barcode') }}">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                            <span class="visually-hidden">{{ __('tracking.receive.remove_barcode') }}</span>
                        </button>
                    </td>
                `;
                rows.appendChild(tr);
            });
        }

        async function addBarcode(raw) {
            let code = (raw || '').trim().replace(/\s+/g, ' ');
            let caseNumber = null;
            if (!code) return;
            if (!permanentBarcodePattern.test(code) && !finalCasePattern.test(code)) {
                inputError.textContent = @json(__('tracking.receive.invalid_identifier_format'));
                inputError.classList.remove('d-none');
                barcodeInput.select();
                return;
            }

            inputError.classList.add('d-none');
            addBtn.disabled = true;
            try {
                const response = await fetch(`${validateIdentifierUrl}?identifier=${encodeURIComponent(code)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok || !result.valid) {
                    inputError.textContent = result.message || @json(__('tracking.receive.identifier_not_found'));
                    inputError.classList.remove('d-none');
                    barcodeInput.select();
                    return;
                }
                code = result.permanent_barcode;
                caseNumber = result.case_number;
            } catch (error) {
                inputError.textContent = @json(__('tracking.receive.identifier_not_found'));
                inputError.classList.remove('d-none');
                return;
            } finally {
                addBtn.disabled = false;
            }

            if (barcodes.some(item => item.barcode === code)) {
                barcodeInput.value = '';
                barcodeInput.focus();
                return;
            }

            barcodes.push({
                barcode: code,
                label: caseNumber || code
            });
            syncHiddenField();
            drawRows();
            barcodeInput.value = '';
            barcodeInput.focus();
        }

        function seedFromOldInput() {
            const oldValue = @json(old('barcodes', ''));
            if (!oldValue) return;
            oldValue.split(/[\r\n,\t]+/).forEach(code => {
                const c = code.trim();
                if (c) addBarcode(c);
            });
        }

        addBtn.addEventListener('click', function () {
            addBarcode(barcodeInput.value);
        });

        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBarcode(barcodeInput.value);
            }
        });

        rows.addEventListener('click', function (e) {
            const btn = e.target.closest('.removeBarcodeBtn');
            if (!btn) return;
            const idx = Number(btn.getAttribute('data-index'));
            if (Number.isNaN(idx)) return;
            barcodes.splice(idx, 1);
            syncHiddenField();
            drawRows();
            barcodeInput.focus();
        });

        receiveForm.addEventListener('submit', function (e) {
            if (barcodes.length === 0) {
                e.preventDefault();
                alert(@json(__('tracking.receive.at_least_one')));
                barcodeInput.focus();
            }
        });

        seedFromOldInput();
    })();
</script>
@endpush
