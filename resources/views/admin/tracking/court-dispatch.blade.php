@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 dispatch-page">
    <div class="dispatch-toolbar d-flex align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-up-right dispatch-heading-icon" aria-hidden="true"></i>
            <h3 class="dispatch-heading mb-0">{{ auth()->user()->name }}: {{ __('tracking.court.dispatch_title') }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tracking.section.receive') }}" class="btn btn-receive">
                <i class="bi bi-upc-scan" aria-hidden="true"></i> {{ __('messages.section_receive') }}
            </a>
            <a href="{{ route('admin.tracking.register-report') }}" class="btn btn-report">
                <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> {{ __('tracking.receive.report') }}
            </a>
        </div>
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

    <form method="POST" action="{{ route('admin.tracking.court.dispatch.store') }}" class="dispatch-workspace">
        @csrf

        <div class="row g-3 dispatch-details">
            <div class="col-lg-6">
                <label class="form-label">{{ __('tracking.court.court') }}</label>
                <select name="court_id" class="form-select form-select-lg" required>
                    <option value="">{{ __('tracking.court.select_court') }}</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" @selected((string)old('court_id') === (string)$court->id)>
                            {{ app()->getLocale() === 'bn' ? $court->name_bn : $court->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="form-label">{{ __('tracking.court.received_by_name') }}</label>
                <input type="text" class="form-control" name="received_by_name" value="{{ old('received_by_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('tracking.court.received_by_designation') }}</label>
                <input type="text" class="form-control" name="received_by_designation" value="{{ old('received_by_designation') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('tracking.court.received_by_phone') }}</label>
                <input type="text" class="form-control" name="received_by_phone" value="{{ old('received_by_phone') }}">
            </div>
        </div>

        <div class="mt-4">
            <label for="barcode_input" class="visually-hidden">{{ __('tracking.receive.identifier_label') }}</label>
            <div class="input-group dispatch-scan-focus">
                <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <input type="text" id="barcode_input" class="form-control form-control-lg" placeholder="{{ __('tracking.receive.barcode_placeholder') }}" aria-describedby="barcodeInputError" autofocus>
                <button type="button" id="addBarcodeBtn" class="btn btn-brand px-4">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('tracking.receive.add_barcode') }}
                </button>
            </div>
            <div id="barcodeInputError" class="text-danger fw-semibold mt-2 d-none" role="alert"></div>
            <input type="hidden" id="barcodes" name="barcodes" value="{{ old('barcodes') }}">
        </div>

        <div class="table-responsive dispatch-queue mt-4">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>{{ __('tracking.receive.barcode') }}</th>
                        <th style="width:120px;">{{ __('tracking.receive.action') }}</th>
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
            <label class="form-label">{{ __('tracking.court.notes_label') }}</label>
            <input name="notes" class="form-control" value="{{ old('notes') }}">
        </div>

        <button type="submit" class="btn btn-brand btn-lg dispatch-submit mt-4">
            <i class="bi bi-box-arrow-up-right me-2" aria-hidden="true"></i>{{ __('tracking.court.dispatch_submit') }}
        </button>

        @if(session('court_batch_id'))
            <a class="btn btn-gold mt-3 ms-2" target="_blank" href="{{ route('admin.tracking.court.batch.pdf', session('court_batch_id')) }}">
                {{ __('tracking.court.print_batch_pdf') }}
            </a>
        @endif
    </form>

    @if(session('court_processed'))
        <div class="card p-3 mt-3">
            <h6 class="mb-2">{{ __('tracking.court.processed_files') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('tracking.receive.barcode') }}</th>
                            <th>{{ __('tracking.register.case_no') }}</th>
                            <th>{{ __('tracking.register.from') }}</th>
                            <th>{{ __('tracking.register.to') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('court_processed') as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['barcode'] }}</td>
                                <td>{{ $item['case_no'] }}</td>
                                <td>{{ $item['from_section'] }}</td>
                                <td>{{ $item['to_section'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(session('court_failed'))
        <div class="card p-3 mt-3">
            <h6 class="mb-2">{{ __('tracking.receive.failed_files') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('tracking.receive.barcode') }}</th>
                            <th>{{ __('tracking.receive.fail_reason') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('court_failed') as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['barcode'] ?? '-' }}</td>
                                <td>{{ $item['reason'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .dispatch-page { max-width: 1040px; }
    .dispatch-heading { font-size: 1.35rem; font-weight: 650; color: #1f2937; }
    .dispatch-heading-icon { color: #0f766e; font-size: 1.45rem; }
    .dispatch-workspace { border-top: 1px solid #e5e7eb; padding-top: 1.5rem; }
    .dispatch-details .form-control, .dispatch-details .form-select { min-height: 48px; }
    .dispatch-scan-focus { border: 2px solid #0f766e; border-radius: .5rem; box-shadow: 0 0 0 4px rgba(15, 118, 110, .1); }
    .dispatch-scan-focus .input-group-text,
    .dispatch-scan-focus .form-control,
    .dispatch-scan-focus .btn { min-height: 58px; border: 0; }
    .dispatch-scan-focus:focus-within { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .dispatch-queue { min-height: 150px; border: 1px solid #e5e7eb; }
    .dispatch-queue thead th { background: #f7f8fa; color: #4b5563; font-size: .8rem; text-transform: uppercase; border-bottom-width: 1px; }
    .dispatch-queue td, .dispatch-queue th { padding: .8rem; }
    .queue-barcode { margin-top: .15rem; color: #6b7280; font-size: .8rem; font-family: monospace; }
    .dispatch-submit { min-width: 220px; min-height: 54px; }
    .btn-receive, .btn-report { min-height: 44px; display: inline-flex; align-items: center; gap: .4rem; color: #fff; font-weight: 600; }
    .btn-receive { background: #0f766e; border-color: #0f766e; }
    .btn-report { background: #2563eb; border-color: #2563eb; }
    .btn-receive:hover, .btn-receive:focus-visible { color: #fff; background: #0b5f59; border-color: #0b5f59; }
    .btn-report:hover, .btn-report:focus-visible { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    @media (max-width: 767.98px) {
        .dispatch-page { padding-top: 1rem !important; }
        .dispatch-toolbar { align-items: stretch !important; flex-direction: column; }
        .dispatch-toolbar > .d-flex:last-child { display: grid !important; grid-template-columns: 1fr 1fr; }
        .btn-receive, .btn-report { justify-content: center; }
        .dispatch-scan-focus { flex-wrap: wrap; }
        .dispatch-scan-focus .input-group-text { display: none; }
        .dispatch-scan-focus .form-control { width: 100%; border-radius: .375rem !important; }
        .dispatch-scan-focus .btn { width: 100%; margin-top: .5rem; border-radius: .375rem !important; }
        .dispatch-submit { width: 100%; }
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
        const form = hiddenBarcodes.closest('form');
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
                rows.innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center text-muted">{{ __('tracking.receive.none') }}</td></tr>';
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

        const oldValue = @json(old('barcodes', ''));
        if (oldValue) {
            oldValue.split(/[\r\n,\t]+/).forEach(code => {
                const c = code.trim();
                if (c) addBarcode(c);
            });
        }

        addBtn.addEventListener('click', () => addBarcode(barcodeInput.value));
        barcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBarcode(barcodeInput.value);
            }
        });

        rows.addEventListener('click', (e) => {
            const btn = e.target.closest('.removeBarcodeBtn');
            if (!btn) return;
            const idx = Number(btn.dataset.index);
            if (Number.isNaN(idx)) return;
            barcodes.splice(idx, 1);
            syncHiddenField();
            drawRows();
            barcodeInput.focus();
        });

        form.addEventListener('submit', (e) => {
            if (barcodes.length === 0) {
                e.preventDefault();
                alert(@json(__('tracking.receive.at_least_one')));
                barcodeInput.focus();
            }
        });
    })();
</script>
@endpush
