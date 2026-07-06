@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-2">{{ __('tracking.court.return_title') }}</h3>
    <p class="text-muted mb-3">{{ __('tracking.court.return_subtitle') }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.tracking.court.return.store') }}" class="card p-4">
        @csrf

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
            <label for="barcode_input" class="form-label">{{ __('tracking.receive.identifier_label') }}</label>
            <div class="input-group">
                <input type="text" id="barcode_input" class="form-control form-control-lg" placeholder="{{ __('tracking.receive.identifier_placeholder') }}" aria-describedby="barcodeInputError" autofocus>
                <button type="button" id="addBarcodeBtn" class="btn btn-brand">{{ __('tracking.receive.add_barcode') }}</button>
            </div>
            <div id="barcodeInputError" class="text-danger fw-semibold mt-2 d-none" role="alert"></div>
            <input type="hidden" id="barcodes" name="barcodes" value="{{ old('barcodes') }}">
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm align-middle mb-0">
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

        <button type="submit" class="btn btn-brand mt-3">{{ __('tracking.court.return_submit') }}</button>

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
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
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

        function syncHiddenField() {
            hiddenBarcodes.value = barcodes.join('\n');
        }

        function drawRows() {
            rows.innerHTML = '';
            if (barcodes.length === 0) {
                rows.innerHTML = '<tr id="emptyRow"><td colspan="3" class="text-center text-muted">{{ __('tracking.receive.none') }}</td></tr>';
                return;
            }

            barcodes.forEach((code, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${code}</td>
                    <td><button type="button" class="btn btn-sm btn-danger removeBarcodeBtn" data-index="${index}">{{ __('tracking.receive.remove_barcode') }}</button></td>
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

            if (barcodes.includes(code)) {
                barcodeInput.value = '';
                barcodeInput.focus();
                return;
            }
            barcodes.push(code);
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
