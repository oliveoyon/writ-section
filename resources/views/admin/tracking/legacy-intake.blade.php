@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 legacy-page">
    <div class="legacy-header mb-3">
        <div>
            <div class="system-mark">RTFTS Old Case</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: Receive Old Case</h4>
            <small>{{ $section }}</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.tracking.section.receive') }}" class="btn btn-soft">
                <i class="bi bi-upc-scan me-1"></i> Receive File
            </a>
            <a href="{{ route('admin.tracking.register-report') }}" class="btn btn-soft">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Report
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

    <div class="legacy-panel">
        <div class="panel-heading">
            <div>
                <h5>Scan Old File</h5>
                <span>Only RTFTS barcode or WRPET case number is accepted</span>
            </div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.tracking.old-case-receive.store') }}" id="legacyIntakeForm">
                @csrf

                <label for="identifier" class="visually-hidden">RTFTS barcode or case number</label>
                <div class="input-group legacy-scan-box">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-upc-scan"></i>
                    </span>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        class="form-control form-control-lg"
                        value="{{ old('identifier') }}"
                        placeholder="132026004788 or WRPET 4788/2026"
                        autocomplete="off"
                        autofocus
                        required
                    >
                    <button type="submit" class="btn btn-brand px-4">
                        <i class="bi bi-check2-circle me-1"></i> Receive File
                    </button>
                </div>
                <div id="identifierError" class="text-danger fw-semibold mt-2 d-none"></div>

                <div class="mt-3">
                    <label for="notes" class="form-label">Status / Notes</label>
                    <input id="notes" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional">
                </div>
            </form>
        </div>
    </div>

    @php($result = session('legacy_intake_result'))
    @if($result && is_array($result))
        <div class="legacy-result mt-3">
            <div class="result-status {{ $result['status'] ?? '' }}">
                <i class="bi bi-check-circle-fill"></i>
                <span>
                    @if(($result['status'] ?? '') === 'created')
                        Old case added
                    @elseif(($result['status'] ?? '') === 'received')
                        Existing file received
                    @else
                        Already in your custody
                    @endif
                </span>
            </div>
            <div class="result-grid">
                <div><span>Case No</span><strong>{{ $result['case_no'] ?? '-' }}</strong></div>
                <div><span>Barcode</span><strong>{{ $result['barcode'] ?? '-' }}</strong></div>
                <div><span>From</span><strong>{{ $result['from_section'] ?? '-' }}</strong></div>
                <div><span>To</span><strong>{{ $result['to_section'] ?? '-' }}</strong></div>
                <div><span>Received By</span><strong>{{ $result['received_by'] ?? '-' }}</strong></div>
                <div><span>Time</span><strong>{{ $result['received_at'] ?? '-' }}</strong></div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .legacy-page { max-width: 980px; }
    .legacy-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .legacy-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .legacy-header small { color: #6b7280; font-weight: 700; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 900; }
    .header-actions { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .btn-soft { background: #eef5fb; color: #00284d; border: 1px solid #d6e3ef; font-weight: 800; }
    .btn-soft:hover { background: #dfeaf5; color: #00284d; border-color: #cbd9e7; }
    .legacy-panel,
    .legacy-result {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-heading {
        padding: .8rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 900; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 700; }
    .panel-body { padding: 1rem; }
    .legacy-scan-box {
        border: 2px solid #0f766e;
        border-radius: 4px;
        box-shadow: 0 0 0 4px rgba(15, 118, 110, .1);
    }
    .legacy-scan-box .form-control,
    .legacy-scan-box .input-group-text,
    .legacy-scan-box .btn { min-height: 62px; border: 0; }
    .legacy-scan-box .form-control { font-size: 1.15rem; font-weight: 800; }
    .legacy-scan-box:focus-within { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .form-label { color: #374151; font-size: .84rem; font-weight: 800; }
    .form-control { border-radius: 4px; }
    .form-control:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; font-weight: 900; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .result-status {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .85rem 1rem;
        background: #f2fbf8;
        color: #0f766e;
        border-bottom: 1px solid #d9eee8;
        font-weight: 900;
    }
    .result-status.same_holder { background: #fff8e3; color: #9a710b; border-bottom-color: #f1dfaa; }
    .result-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        padding: 1rem;
    }
    .result-grid > div {
        border: 1px solid #e5e7eb;
        border-left: 4px solid #d4a017;
        border-radius: 4px;
        padding: .7rem;
        background: #fbfcfe;
    }
    .result-grid span { display: block; color: #6b7280; font-size: .76rem; font-weight: 900; text-transform: uppercase; }
    .result-grid strong { display: block; color: #111827; margin-top: .25rem; word-break: break-word; }
    @media (max-width: 575.98px) {
        .legacy-header { align-items: stretch; flex-direction: column; }
        .header-actions { display: grid; grid-template-columns: 1fr 1fr; }
        .legacy-scan-box { flex-wrap: wrap; }
        .legacy-scan-box .input-group-text { display: none; }
        .legacy-scan-box .form-control,
        .legacy-scan-box .btn { width: 100%; border-radius: .375rem !important; }
        .legacy-scan-box .btn { margin-top: .5rem; }
        .result-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        const form = document.getElementById('legacyIntakeForm');
        const input = document.getElementById('identifier');
        const error = document.getElementById('identifierError');
        const maxYear = @json((int) date('Y') + 1);
        const barcodePattern = /^13(\d{4})(\d{6})$/;
        const casePattern = /^(?:(?:WRPET|WRITPET)\s*)?0*(\d{1,6})\s*\/\s*(\d{4})$/i;

        if (!form || !input || !error) {
            return;
        }

        function isValidYear(year) {
            const numericYear = Number(year);
            return numericYear >= 1971 && numericYear <= maxYear;
        }

        function isValidIdentifier(value) {
            const barcodeMatch = value.match(barcodePattern);
            if (barcodeMatch) {
                return isValidYear(barcodeMatch[1]) && Number(barcodeMatch[2]) > 0;
            }

            const caseMatch = value.match(casePattern);
            if (caseMatch) {
                return Number(caseMatch[1]) > 0 && isValidYear(caseMatch[2]);
            }

            return false;
        }

        form.addEventListener('submit', function (event) {
            const value = input.value.trim().replace(/\s+/g, ' ');

            if (isValidIdentifier(value)) {
                error.classList.add('d-none');
                input.value = value;
                return;
            }

            event.preventDefault();
            error.textContent = 'Invalid RTFTS barcode or case number. Example: 132026004788 or WRPET 4788/2026.';
            error.classList.remove('d-none');
            input.select();
        });
    })();
</script>
@endpush
