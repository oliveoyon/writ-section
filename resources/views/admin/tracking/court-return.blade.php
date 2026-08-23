@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 court-return-page">
    <div class="return-header mb-3">
        <div>
            <div class="system-mark">RTFTS Court</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: {{ __('tracking.court.return_title') }}</h4>
            <small>{{ __('tracking.court.return_subtitle') }}</small>
        </div>
        <div class="return-actions">
            <a href="{{ route('admin.tracking.court.batches.index') }}" class="btn btn-report btn-sm">
                <i class="bi bi-collection" aria-hidden="true"></i> Batches
            </a>
            <a href="{{ route('admin.tracking.section.receive') }}" class="btn btn-receive btn-sm">
                <i class="bi bi-upc-scan" aria-hidden="true"></i> {{ __('messages.section_receive') }}
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

    <form method="POST" action="{{ route('admin.tracking.court.return.store') }}" class="admin-panel">
        @csrf
        <div class="panel-heading">
            <div>
                <h5>{{ __('tracking.court.return_title') }}</h5>
                <span>{{ __('tracking.court.select_court') }}</span>
            </div>
        </div>

        <div class="panel-body">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">{{ __('tracking.court.court') }}</label>
                <select name="court_id" class="form-select" required>
                    <option value="">{{ __('tracking.court.select_court') }}</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" @selected((string)old('court_id') === (string)$court->id)>
                            {{ app()->getLocale() === 'bn' ? $court->name_bn : $court->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3">
            <label for="barcode_input" class="visually-hidden">{{ __('tracking.receive.identifier_label') }}</label>
            <div class="input-group return-scan-focus">
                <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <input type="text" id="barcode_input" class="form-control form-control-lg" placeholder="{{ __('tracking.receive.identifier_placeholder') }}" aria-describedby="barcodeInputError" autofocus>
                <button type="button" id="addBarcodeBtn" class="btn btn-brand px-4">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('tracking.receive.add_barcode') }}
                </button>
            </div>
            <div id="barcodeInputError" class="text-danger fw-semibold mt-2 d-none" role="alert"></div>
            <input type="hidden" id="barcodes" name="barcodes" value="{{ old('barcodes') }}">
        </div>

        <div class="table-responsive return-queue mt-3">
            <table class="table table-sm align-middle mb-0">
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

        <div class="mt-3">
            <label class="form-label">{{ __('tracking.court.notes_label') }}</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div class="submit-bar">
            <button type="submit" class="btn btn-brand btn-lg return-submit">
                <i class="bi bi-building-check me-2" aria-hidden="true"></i>{{ __('tracking.court.return_submit') }}
            </button>

            @if(session('court_batch_id'))
                <a class="btn btn-gold btn-lg" target="_blank" href="{{ route('admin.tracking.court.batch.pdf', session('court_batch_id')) }}">
                    <i class="bi bi-printer"></i> {{ __('tracking.court.print_batch_pdf') }}
                </a>
            @endif
        </div>
        </div>
    </form>

    @if(session('court_processed'))
        <div class="admin-panel result-panel mt-3">
            <div class="panel-heading success">
                <div>
                    <h5>{{ __('tracking.court.processed_files') }}</h5>
                    <span>{{ count(session('court_processed', [])) }} file(s)</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table result-table table-sm mb-0">
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
                                <td><span class="section-badge">{{ $item['from_section'] }}</span></td>
                                <td><span class="section-badge">{{ $item['to_section'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(session('court_failed'))
        <div class="admin-panel result-panel mt-3">
            <div class="panel-heading danger">
                <div>
                    <h5>{{ __('tracking.receive.failed_files') }}</h5>
                    <span>{{ count(session('court_failed', [])) }} file(s)</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table result-table table-sm mb-0">
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
    .court-return-page { max-width: 1040px; }
    .return-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .return-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .return-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .return-actions { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .admin-panel {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading.success { border-top-color: #21854a; }
    .panel-heading.danger { border-top-color: #a93b2d; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .form-label { color: #374151; font-size: .84rem; font-weight: 800; }
    .form-control, .form-select { border-radius: 4px; min-height: 42px; }
    .form-control:focus, .form-select:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .return-scan-focus { border: 2px solid #0f766e; border-radius: 4px; box-shadow: 0 0 0 4px rgba(15, 118, 110, .1); }
    .return-scan-focus .input-group-text,
    .return-scan-focus .form-control,
    .return-scan-focus .btn { min-height: 58px; border: 0; }
    .return-scan-focus:focus-within { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .return-queue { min-height: 150px; border: 1px solid #e5e7eb; border-radius: 4px; }
    .return-queue thead th { background: #eef5fb; color: #00284d; font-size: .8rem; font-weight: 800; border-bottom: 0; }
    .return-queue td, .return-queue th { padding: .8rem; }
    .queue-barcode { margin-top: .15rem; color: #6b7280; font-size: .8rem; font-family: monospace; }
    .submit-bar { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: flex-end; margin-top: 1rem; }
    .return-submit { min-width: 220px; min-height: 54px; }
    .result-table thead th { background: #eef5fb; color: #00284d; font-weight: 800; border-bottom: 0; white-space: nowrap; }
    .result-table td, .result-table th { padding: .65rem .75rem; }
    .section-badge { display: inline-flex; border-radius: 4px; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; font-size: .76rem; font-weight: 800; padding: .18rem .45rem; white-space: nowrap; }
    .btn-receive, .btn-report { min-height: 38px; display: inline-flex; align-items: center; gap: .4rem; color: #fff; font-weight: 800; border-radius: 4px; }
    .btn-receive { background: #0f766e; border-color: #0f766e; }
    .btn-report { background: #2563eb; border-color: #2563eb; }
    .btn-receive:hover, .btn-receive:focus-visible { color: #fff; background: #0b5f59; border-color: #0b5f59; }
    .btn-report:hover, .btn-report:focus-visible { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    @media (max-width: 767.98px) {
        .court-return-page { padding-top: 1rem !important; }
        .return-header { align-items: stretch; flex-direction: column; }
        .return-actions { display: grid !important; grid-template-columns: 1fr 1fr; }
        .btn-receive, .btn-report { justify-content: center; }
        .return-scan-focus { flex-wrap: wrap; }
        .return-scan-focus .input-group-text { display: none; }
        .return-scan-focus .form-control { width: 100%; border-radius: .375rem !important; }
        .return-scan-focus .btn { width: 100%; margin-top: .5rem; border-radius: .375rem !important; }
        .return-submit, .submit-bar .btn { width: 100%; }
        .result-table { min-width: 760px; }
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
