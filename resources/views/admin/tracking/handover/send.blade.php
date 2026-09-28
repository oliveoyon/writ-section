@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 send-page">
    <div class="send-header mb-3">
        <div>
            <div class="system-mark">RTFTS Send</div>
            <h4 class="mb-0">Send Files</h4>
            <small>{{ auth()->user()->name }}</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.tracking.handover.recipients') }}" class="btn btn-light btn-sm border">
                <i class="bi bi-person" aria-hidden="true"></i> Change Person
            </a>
            <a href="{{ route('admin.tracking.handover.workspace') }}" class="btn btn-light btn-sm border">
                <i class="bi bi-grid" aria-hidden="true"></i> File Desk
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.tracking.handover.store', $recipient) }}" class="admin-panel" id="sendFilesForm">
        @csrf
        <div class="recipient-band">
            <span class="recipient-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
            <span>
                <small>Send to</small>
                <strong>{{ $recipient->name }}</strong>
                <em>{{ $recipient->departmentRelation?->label }} &middot; {{ $recipient->employee_id ?: '-' }}</em>
            </span>
        </div>

        <div class="panel-body">
            <label for="barcode_input" class="visually-hidden">Case No. or barcode</label>
            <div class="input-group scan-focus">
                <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <input type="text" id="barcode_input" class="form-control form-control-lg" placeholder="Scan barcode or type Case No." aria-describedby="barcodeInputError" autocomplete="off" autofocus>
                <button type="button" id="addBarcodeBtn" class="btn btn-brand px-4">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add
                </button>
            </div>
            <div id="barcodeInputError" class="text-danger fw-semibold mt-2 d-none" role="alert"></div>
            <div id="caseIdFields"></div>

            <div class="table-responsive send-queue mt-4">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Case No.</th>
                            <th style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="caseRows">
                        <tr><td colspan="3" class="text-center text-muted">No files added</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <label for="notes" class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                <input id="notes" name="notes" class="form-control" maxlength="1000" value="{{ old('notes') }}">
            </div>

            <div class="submit-bar">
                <span id="fileCount" class="file-count">0 files</span>
                <button type="submit" class="btn btn-send btn-lg" id="sendSubmit">
                    <i class="bi bi-send me-2" aria-hidden="true"></i>Send to {{ $recipient->name }}
                </button>
            </div>
        </div>
    </form>

    @php($summary = session('transfer_summary'))
    @if($summary && is_array($summary))
        <div class="admin-panel result-panel mt-3">
            <div class="result-heading">
                <strong>{{ $summary['batch_no'] ?: 'Send Result' }}</strong>
                <span>{{ $summary['sent_at'] ?: now()->format('d-m-Y h:i A') }}</span>
            </div>
            <div class="result-facts">
                <div><span>Recipient</span><strong>{{ $summary['recipient'] }}</strong></div>
                <div><span>Department</span><strong>{{ $summary['recipient_section'] ?: '-' }}</strong></div>
                <div><span>Files Sent</span><strong>{{ $summary['sent_count'] }}</strong></div>
            </div>
            @if(!empty($summary['failed']))
                <div class="table-responsive border-top">
                    <table class="table table-sm align-middle mb-0 result-table">
                        <thead><tr><th>Case No.</th><th>Not Sent</th></tr></thead>
                        <tbody>
                            @foreach($summary['failed'] as $failure)
                                <tr><td>{{ $failure['case_no'] }}</td><td class="text-danger">{{ $failure['reason'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .send-page { max-width:1040px; }
    .send-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.85rem 1rem; background:#fff; border:1px solid #e3e8ef; border-top:3px solid #00284d; border-bottom-color:#d4a017; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.08); }
    .send-header h4 { color:#00284d; font-size:1.15rem; font-weight:800; }
    .send-header small { color:#6b7280; font-weight:700; }
    .system-mark { color:#b87d08; font-size:.82rem; font-weight:800; }
    .header-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
    .admin-panel { background:#fff; border:1px solid #e3e8ef; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.07); overflow:hidden; }
    .recipient-band { display:flex; align-items:center; gap:.75rem; padding:.8rem 1rem; color:#fff; background:#0b5f78; border-bottom:3px solid #d4a017; }
    .recipient-band > span:last-child { min-width:0; }
    .recipient-band small, .recipient-band strong, .recipient-band em { display:block; letter-spacing:0; }
    .recipient-band small { color:rgba(255,255,255,.78); font-size:.75rem; font-weight:800; text-transform:uppercase; }
    .recipient-band strong { font-size:1.05rem; font-weight:800; }
    .recipient-band em { color:rgba(255,255,255,.85); font-size:.8rem; font-style:normal; font-weight:600; }
    .recipient-icon { display:grid; place-items:center; flex:0 0 42px; width:42px; height:42px; border-radius:50%; background:rgba(255,255,255,.14); font-size:1.25rem; }
    .panel-body { padding:1rem; }
    .scan-focus { border:2px solid #0f766e; border-radius:4px; box-shadow:0 0 0 4px rgba(15,118,110,.1); }
    .scan-focus .input-group-text, .scan-focus .form-control, .scan-focus .btn { min-height:58px; border:0; }
    .scan-focus:focus-within { border-color:#0b5f59; box-shadow:0 0 0 5px rgba(15,118,110,.18); }
    .btn-brand { background:#00284d; color:#fff; border-color:#00284d; font-weight:800; }
    .btn-brand:hover { background:#001e3a; color:#fff; border-color:#001e3a; }
    .send-queue { min-height:150px; border:1px solid #e5e7eb; border-radius:4px; }
    .send-queue thead th, .result-table thead th { background:#eef5fb; color:#00284d; font-size:.8rem; font-weight:800; border-bottom:0; }
    .send-queue td, .send-queue th, .result-table td, .result-table th { padding:.75rem; }
    .queue-barcode { margin-top:.15rem; color:#6b7280; font-size:.78rem; font-family:monospace; }
    .form-label { color:#374151; font-size:.84rem; font-weight:800; }
    .form-control { border-radius:4px; }
    .form-control:focus { border-color:#d4a017; box-shadow:0 0 0 .15rem rgba(212,160,23,.15); }
    .submit-bar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1rem; }
    .file-count { color:#52606d; font-weight:800; }
    .btn-send { min-width:240px; min-height:54px; color:#fff; background:#0f766e; border-color:#0f766e; font-weight:800; border-radius:4px; }
    .btn-send:hover, .btn-send:focus-visible { color:#fff; background:#0b5f59; border-color:#0b5f59; }
    .result-heading { display:flex; justify-content:space-between; gap:1rem; padding:.75rem 1rem; border-top:3px solid #21854a; background:#fbfcfe; }
    .result-heading strong { color:#1f2937; }
    .result-heading span { color:#6b7280; font-size:.82rem; font-weight:700; }
    .result-facts { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; padding:1rem; }
    .result-facts div { padding:.65rem; border-left:4px solid #d4a017; background:#f8fafc; }
    .result-facts span, .result-facts strong { display:block; }
    .result-facts span { color:#6b7280; font-size:.74rem; font-weight:800; text-transform:uppercase; }
    .result-facts strong { margin-top:.15rem; color:#1f2937; }
    @media (max-width:767.98px) { .send-page { padding-top:1rem !important; } .send-header { align-items:stretch; flex-direction:column; } .header-actions { display:grid; grid-template-columns:1fr 1fr; } .scan-focus { flex-wrap:wrap; } .scan-focus .input-group-text { display:none; } .scan-focus .form-control { width:100%; border-radius:4px !important; } .scan-focus .btn { width:100%; margin-top:.5rem; border-radius:4px !important; } .submit-bar { align-items:stretch; flex-direction:column; } .btn-send { width:100%; } .result-facts { grid-template-columns:1fr; } }
</style>
@endpush

@push('js')
<script>
    (function () {
        const input = document.getElementById('barcode_input');
        const addButton = document.getElementById('addBarcodeBtn');
        const rows = document.getElementById('caseRows');
        const fields = document.getElementById('caseIdFields');
        const errorBox = document.getElementById('barcodeInputError');
        const form = document.getElementById('sendFilesForm');
        const submitButton = document.getElementById('sendSubmit');
        const fileCount = document.getElementById('fileCount');
        const validateUrl = @json(route('admin.tracking.handover.validate', $recipient));
        const files = [];

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function render() {
            fields.innerHTML = files.map(file => `<input type="hidden" name="case_ids[]" value="${file.id}">`).join('');
            fileCount.textContent = `${files.length} ${files.length === 1 ? 'file' : 'files'}`;

            if (files.length === 0) {
                rows.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No files added</td></tr>';
                return;
            }

            rows.innerHTML = files.map((file, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${escapeHtml(file.caseNumber)}</strong><div class="queue-barcode">${escapeHtml(file.barcode)}</div></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${index}" title="Remove"><i class="bi bi-trash"></i></button></td>
                </tr>
            `).join('');
        }

        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove('d-none');
            input.select();
        }

        async function addFile() {
            const identifier = input.value.trim().replace(/\s+/g, ' ');
            if (!identifier) return;
            if (files.length >= 200) {
                showError('A batch can contain at most 200 files.');
                return;
            }

            errorBox.classList.add('d-none');
            addButton.disabled = true;

            try {
                const response = await fetch(`${validateUrl}?identifier=${encodeURIComponent(identifier)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (!response.ok || !result.valid) {
                    showError(result.message || 'File cannot be sent.');
                    return;
                }

                if (!files.some(file => file.id === Number(result.case_id))) {
                    files.push({
                        id: Number(result.case_id),
                        barcode: result.permanent_barcode,
                        caseNumber: result.case_number
                    });
                    render();
                }

                input.value = '';
                input.focus();
            } catch (error) {
                showError('Unable to check this file. Please try again.');
            } finally {
                addButton.disabled = false;
            }
        }

        addButton.addEventListener('click', addFile);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addFile();
            }
        });
        rows.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-file');
            if (!button) return;
            files.splice(Number(button.dataset.index), 1);
            render();
            input.focus();
        });
        form.addEventListener('submit', function (event) {
            if (files.length === 0) {
                event.preventDefault();
                showError('Please scan at least one file.');
                return;
            }
            submitButton.disabled = true;
        });
    })();
</script>
@endpush
