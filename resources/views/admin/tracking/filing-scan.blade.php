@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 filing-scan-page">
    <div class="filing-header mb-3">
        <div>
            <div class="system-mark">RTFTS Filing</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: {{ __('tracking.filing.scan_title') }}</h4>
        </div>
        <a href="{{ route('admin.tracking.filing.direct-create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-file-earmark-plus me-1" aria-hidden="true"></i>{{ __('tracking.filing.direct_button') }}
        </a>
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

    <form method="GET" action="{{ route('admin.tracking.filing.scan-temp') }}" class="scan-panel mb-3">
        <label for="temporary_barcode" class="visually-hidden">{{ __('tracking.filing.temp_barcode') }}</label>
        <div class="input-group filing-scan-focus">
            <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
            <input type="text" id="temporary_barcode" name="temporary_barcode" value="{{ $tempBarcode ?? '' }}" class="form-control form-control-lg" placeholder="{{ __('tracking.filing.scan_placeholder') }}" autofocus required>
            <button type="submit" class="btn btn-brand px-4" title="{{ __('tracking.filing.lookup_button') }}">
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <span class="visually-hidden">{{ __('tracking.filing.lookup_button') }}</span>
            </button>
        </div>
    </form>

    @if (($tempBarcode ?? '') !== '' && !$case)
        <div class="alert alert-warning">{{ __('tracking.filing.case_not_found') }}</div>
    @endif

    @if ($case && ($isBlocked ?? false))
        <div class="alert alert-danger mb-3">{{ __('tracking.filing.already_taken') }}</div>
    @endif

    @if ($case && !($isBlocked ?? false))
        <form method="POST" action="{{ route('admin.tracking.filing.receive-temp') }}" class="admin-panel filing-data-workspace">
            @csrf
            <input type="hidden" name="temporary_barcode" value="{{ $case->temporary_barcode }}">

            <div class="lawyer-strip d-flex flex-wrap gap-4 mb-4">
                <span><strong>{{ $case->lawyer?->full_name ?? __('tracking.lookup.na') }}</strong></span>
                <span>{{ $case->lawyer?->bar_council_id ?? __('tracking.lookup.na') }}</span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('tracking.filing.case_type') }}</label>
                    <select name="case_type" class="form-select" required>
                        <option value="">Select One</option>
                        @foreach($caseTypes as $caseType)
                            <option value="{{ $caseType }}" @selected(old('case_type', $case->case_type) === $caseType)>
                                {{ $caseType }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">{{ __('tracking.filing.description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $case->description) }}</textarea>
                </div>
            </div>

            @php
                $petitioners = old('petitioners', $case->petitioners->map(function($p){
                    return [
                        'name_or_organization' => $p->name_or_organization,
                        'represented_by' => $p->represented_by,
                        'designation' => $p->designation,
                        'address' => $p->address,
                    ];
                })->toArray());
                if (empty($petitioners)) {
                    $petitioners = [['name_or_organization' => '', 'represented_by' => '', 'designation' => '', 'address' => '']];
                }
            @endphp
            <div class="party-panel mb-3">
                <div class="panel-heading compact"><h5>{{ __('tracking.filing.petitioners') }}</h5></div>
                <table class="table filing-table mb-0" id="petitioners_table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('tracking.filing.name_or_organization') }}</th>
                            <th>{{ __('tracking.filing.represented_by') }}</th>
                            <th>{{ __('tracking.filing.designation') }}</th>
                            <th>{{ __('tracking.filing.address') }}</th>
                            <th><button type="button" class="btn btn-add btn-sm" id="addPetitioner"><i class="bi bi-plus"></i></button></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($petitioners as $i => $p)
                            <tr>
                                <td><input type="text" name="petitioners[{{ $i }}][name_or_organization]" class="form-control" value="{{ $p['name_or_organization'] ?? '' }}" required></td>
                                <td><input type="text" name="petitioners[{{ $i }}][represented_by]" class="form-control" value="{{ $p['represented_by'] ?? '' }}"></td>
                                <td><input type="text" name="petitioners[{{ $i }}][designation]" class="form-control" value="{{ $p['designation'] ?? '' }}"></td>
                                <td><textarea name="petitioners[{{ $i }}][address]" class="form-control" rows="1">{{ $p['address'] ?? '' }}</textarea></td>
                                <td>
                                    @if($i > 0)
                                        <button type="button" class="btn btn-remove btn-sm removeRow"><i class="bi bi-trash"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                $respondents = old('respondents', $case->respondents->map(function($r){
                    return [
                        'name_or_organization' => $r->name_or_organization,
                        'represented_by' => $r->represented_by,
                        'designation' => $r->designation,
                        'address' => $r->address,
                    ];
                })->toArray());
                if (empty($respondents)) {
                    $respondents = [['name_or_organization' => '', 'represented_by' => '', 'designation' => '', 'address' => '']];
                }
            @endphp
            <div class="party-panel mb-3">
                <div class="panel-heading compact"><h5>{{ __('tracking.filing.respondents') }}</h5></div>
                <table class="table filing-table mb-0" id="respondents_table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('tracking.filing.name_or_organization') }}</th>
                            <th>{{ __('tracking.filing.represented_by') }}</th>
                            <th>{{ __('tracking.filing.designation') }}</th>
                            <th>{{ __('tracking.filing.address') }}</th>
                            <th><button type="button" class="btn btn-add btn-sm" id="addRespondent"><i class="bi bi-plus"></i></button></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($respondents as $i => $r)
                            <tr>
                                <td><input type="text" name="respondents[{{ $i }}][name_or_organization]" class="form-control" value="{{ $r['name_or_organization'] ?? '' }}" required></td>
                                <td><input type="text" name="respondents[{{ $i }}][represented_by]" class="form-control" value="{{ $r['represented_by'] ?? '' }}"></td>
                                <td><input type="text" name="respondents[{{ $i }}][designation]" class="form-control" value="{{ $r['designation'] ?? '' }}"></td>
                                <td><textarea name="respondents[{{ $i }}][address]" class="form-control" rows="1">{{ $r['address'] ?? '' }}</textarea></td>
                                <td>
                                    @if($i > 0)
                                        <button type="button" class="btn btn-remove btn-sm removeRow"><i class="bi bi-trash"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="submit-bar">
                <button type="submit" class="btn btn-brand">{{ __('tracking.filing.receive_convert') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.tracking.filing.return-to-lawyer') }}" class="admin-panel return-panel mt-3">
            @csrf
            <input type="hidden" name="temporary_barcode" value="{{ $case->temporary_barcode }}">
            <div class="panel-heading danger"><h5>{{ __('tracking.filing.return_title') }}</h5></div>
            <div class="panel-body">
                <label class="form-label">{{ __('tracking.filing.return_reason') }}</label>
                <textarea name="return_reason" class="form-control" rows="3" required></textarea>
                <button type="submit" class="btn btn-danger mt-3">{{ __('tracking.filing.return_button') }}</button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('css')
<style>
    .filing-scan-page { max-width: 1120px; }
    .filing-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-top: 3px solid #00284d; border-bottom-color: #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .08); }
    .filing-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .scan-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; padding: 1rem; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); }
    .filing-scan-focus { border: 2px solid #0f766e; border-radius: 4px; box-shadow: 0 0 0 4px rgba(15, 118, 110, .1); }
    .filing-scan-focus .input-group-text,
    .filing-scan-focus .form-control,
    .filing-scan-focus .btn { min-height: 58px; border: 0; }
    .filing-scan-focus:focus-within { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .filing-data-workspace { padding: 1rem; }
    .lawyer-strip { padding: .75rem 1rem; background: #f7fbff; border-left: 4px solid #2563eb; border-radius: 4px; }
    .panel-heading { padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading.compact { border-top: 0; }
    .panel-heading.danger { border-top-color: #a93b2d; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-body { padding: 1rem; }
    .form-label { color: #374151; font-size: .84rem; font-weight: 800; }
    .form-control, .form-select { border-radius: 4px; }
    .form-control:focus, .form-select:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .party-panel { border: 1px solid #e3e8ef; border-radius: 4px; overflow-x: auto; }
    .filing-table { min-width: 900px; }
    .filing-table thead th { background: #eef5fb; color: #00284d; font-weight: 800; border-bottom: 0; white-space: nowrap; }
    .filing-table td { vertical-align: top; }
    .btn-add, .btn-remove { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; }
    .btn-add { background: #eefbf3; border-color: #bce8ca; color: #186a36; }
    .btn-remove { background: #fff5f5; border-color: #f1b7b7; color: #a93b2d; }
    .submit-bar { display: flex; justify-content: flex-end; padding-top: .5rem; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #111827; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    @media (max-width: 575.98px) {
        .filing-scan-page { padding-top: 1rem !important; }
        .filing-header { align-items: stretch; flex-direction: column; }
        .filing-header .btn, .submit-bar .btn { width: 100%; }
        .filing-scan-focus .input-group-text { display: none; }
    }
</style>
@endpush

@push('js')
<script>
let petitionerIndex = document.querySelectorAll('#petitioners_table tbody tr').length;
let respondentIndex = document.querySelectorAll('#respondents_table tbody tr').length;

const petitionerBtn = document.getElementById('addPetitioner');
if (petitionerBtn) {
    petitionerBtn.addEventListener('click', function () {
        const table = document.querySelector('#petitioners_table tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="petitioners[${petitionerIndex}][name_or_organization]" class="form-control" required></td>
            <td><input type="text" name="petitioners[${petitionerIndex}][represented_by]" class="form-control"></td>
            <td><input type="text" name="petitioners[${petitionerIndex}][designation]" class="form-control"></td>
            <td><textarea name="petitioners[${petitionerIndex}][address]" class="form-control" rows="1"></textarea></td>
            <td><button type="button" class="btn btn-remove btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
        `;
        table.appendChild(row);
        petitionerIndex++;
    });
}

const respondentBtn = document.getElementById('addRespondent');
if (respondentBtn) {
    respondentBtn.addEventListener('click', function () {
        const table = document.querySelector('#respondents_table tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="respondents[${respondentIndex}][name_or_organization]" class="form-control" required></td>
            <td><input type="text" name="respondents[${respondentIndex}][represented_by]" class="form-control"></td>
            <td><input type="text" name="respondents[${respondentIndex}][designation]" class="form-control"></td>
            <td><textarea name="respondents[${respondentIndex}][address]" class="form-control" rows="1"></textarea></td>
            <td><button type="button" class="btn btn-remove btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
        `;
        table.appendChild(row);
        respondentIndex++;
    });
}

document.addEventListener('click', function (event) {
    const removeButton = event.target.closest('.removeRow');
    if (removeButton) {
        removeButton.closest('tr').remove();
    }
});
</script>
@endpush
