@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 direct-filing-page">
    <div class="direct-filing-header mb-4">
        <div>
            <h3 class="mb-1">{{ __('tracking.filing.direct_title') }}</h3>
            <p class="text-muted mb-0">{{ __('tracking.filing.direct_subtitle') }}</p>
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

    <form method="POST" action="{{ route('admin.tracking.filing.store-direct') }}" class="direct-filing-form">
        @csrf
        <section class="form-panel mb-3">
            <div class="form-panel-title">
                <span class="step-badge">1</span>
                <h5 class="mb-0">{{ __('tracking.filing.lawyer_block_title') }}</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="lawyer_member_id" class="form-label">{{ __('tracking.filing.lawyer_member_id') }}</label>
                    <input type="text" id="lawyer_member_id" name="lawyer_member_id" class="form-control" value="{{ old('lawyer_member_id') }}" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-brand w-100" id="lookupLawyerBtn">{{ __('tracking.filing.lookup_button') }}</button>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <span id="lawyer_lookup_status" class="small text-muted"></span>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label for="lawyer_full_name" class="form-label">{{ __('tracking.filing.lawyer_name') }}</label>
                    <input type="text" id="lawyer_full_name" name="lawyer_full_name" class="form-control" value="{{ old('lawyer_full_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="lawyer_phone" class="form-label">{{ __('tracking.filing.phone') }}</label>
                    <input type="text" id="lawyer_phone" name="lawyer_phone" class="form-control" value="{{ old('lawyer_phone') }}">
                </div>
                <div class="col-md-4">
                    <label for="lawyer_email" class="form-label">{{ __('tracking.filing.lawyer_email') }}</label>
                    <input type="email" id="lawyer_email" name="lawyer_email" class="form-control" value="{{ old('lawyer_email') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="lawyer_password" class="form-label">{{ __('tracking.filing.lawyer_password') }}</label>
                    <input type="text" id="lawyer_password" name="lawyer_password" class="form-control" value="{{ old('lawyer_password') }}" placeholder="{{ __('tracking.filing.lawyer_password_hint') }}">
                </div>
            </div>
        </section>

        <section class="form-panel mb-3">
            <div class="form-panel-title">
                <span class="step-badge">2</span>
                <h5 class="mb-0">{{ __('tracking.filing.fill_data_title') }}</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="case_type" class="form-label">{{ __('tracking.filing.case_type') }}</label>
                    <select id="case_type" name="case_type" class="form-select" required>
                        <option value="">Select One</option>
                        @foreach($caseTypes as $caseType)
                            <option value="{{ $caseType }}" @selected(old('case_type') === $caseType)>
                                {{ $caseType }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label for="description" class="form-label">{{ __('tracking.filing.description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        @php
            $petitioners = old('petitioners', [['name_or_organization' => '', 'represented_by' => '', 'designation' => '', 'address' => '']]);
            $respondents = old('respondents', [['name_or_organization' => '', 'represented_by' => '', 'designation' => '', 'address' => '']]);
        @endphp
        <section class="form-panel party-panel mb-3">
            <div class="form-panel-title">
                <span class="step-badge">3</span>
                <h5 class="mb-0">{{ __('tracking.filing.petitioners') }}</h5>
            </div>
            <table class="table table-bordered" id="petitioners_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('tracking.filing.name_or_organization') }}</th>
                        <th>{{ __('tracking.filing.represented_by') }}</th>
                        <th>{{ __('tracking.filing.designation') }}</th>
                        <th>{{ __('tracking.filing.address') }}</th>
                        <th><button type="button" class="btn btn-success btn-sm" id="addPetitioner">{{ __('tracking.filing.add_row') }}</button></th>
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
                                    <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="form-panel party-panel mb-3">
            <div class="form-panel-title">
                <span class="step-badge">4</span>
                <h5 class="mb-0">{{ __('tracking.filing.respondents') }}</h5>
            </div>
            <table class="table table-bordered" id="respondents_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('tracking.filing.name_or_organization') }}</th>
                        <th>{{ __('tracking.filing.represented_by') }}</th>
                        <th>{{ __('tracking.filing.designation') }}</th>
                        <th>{{ __('tracking.filing.address') }}</th>
                        <th><button type="button" class="btn btn-success btn-sm" id="addRespondent">{{ __('tracking.filing.add_row') }}</button></th>
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
                                    <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <div class="submit-bar">
            <button type="submit" class="btn btn-brand btn-lg px-4">{{ __('tracking.filing.create_permanent') }}</button>
        </div>
    </form>
</div>
@endsection

@push('css')
<style>
    .direct-filing-page { max-width: 1180px; }
    .direct-filing-header { border-left: 4px solid #0f766e; padding: .25rem 0 .25rem 1rem; }
    .direct-filing-header h3 { color: #1f2937; font-size: 1.4rem; font-weight: 700; }
    .direct-filing-form { border-top: 1px solid #e5e7eb; padding-top: 1rem; }
    .form-panel { background: #fff; border: 1px solid #e5e7eb; border-left: 4px solid #d4a017; padding: 1rem; }
    .form-panel-title { display: flex; align-items: center; gap: .65rem; margin-bottom: 1rem; color: #1f2937; }
    .form-panel-title h5 { font-size: 1rem; font-weight: 700; }
    .step-badge { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #00284d; color: #fff; font-weight: 700; font-size: .85rem; }
    .direct-filing-page .form-label { color: #374151; font-size: .84rem; font-weight: 700; margin-bottom: .35rem; }
    .direct-filing-page .form-control,
    .direct-filing-page .form-select { min-height: 44px; border-color: #cfd8e3; border-radius: .45rem; }
    .direct-filing-page textarea.form-control { min-height: 44px; }
    .direct-filing-page .form-control:focus,
    .direct-filing-page .form-select:focus { border-color: #0f766e; box-shadow: 0 0 0 .16rem rgba(15, 118, 110, .14); }
    .party-panel { overflow-x: auto; }
    .party-panel table { min-width: 900px; margin-bottom: 0; }
    .party-panel thead th { background: #f7f8fa; color: #4b5563; font-size: .78rem; text-transform: uppercase; border-bottom-width: 1px; vertical-align: middle; }
    .party-panel td { vertical-align: top; background: #fff; }
    .party-panel th:last-child,
    .party-panel td:last-child { width: 92px; text-align: center; }
    .submit-bar { display: flex; justify-content: flex-end; padding: 1rem 0 0; border-top: 1px solid #e5e7eb; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    @media (max-width: 767.98px) {
        .form-panel { padding: .85rem; }
        .submit-bar .btn { width: 100%; }
    }
</style>
@endpush

@push('js')
<script>
const lookupBtn = document.getElementById('lookupLawyerBtn');
if (lookupBtn) {
    lookupBtn.addEventListener('click', function () {
        const memberId = document.getElementById('lawyer_member_id').value.trim();
        const status = document.getElementById('lawyer_lookup_status');
        if (!memberId) {
            status.textContent = @json(__('tracking.filing.lookup_member_required'));
            status.className = 'small text-danger';
            return;
        }

        status.textContent = @json(__('tracking.filing.lookup_loading'));
        status.className = 'small text-muted';

        fetch(@json(route('admin.tracking.filing.lawyer-lookup')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ member_id: memberId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                document.getElementById('lawyer_full_name').value = data.member.memberName || '';
                document.getElementById('lawyer_phone').value = data.member.mobile || '';
                if (data.member.email) {
                    document.getElementById('lawyer_email').value = data.member.email;
                }
                status.textContent = data.existing
                    ? @json(__('tracking.filing.lookup_existing_lawyer'))
                    : @json(__('tracking.filing.lookup_found_api'));
                status.className = 'small text-success';
            } else {
                status.textContent = data.message || @json(__('tracking.filing.lookup_manual_entry'));
                status.className = 'small text-warning';
            }
        })
        .catch(() => {
            status.textContent = @json(__('tracking.filing.lookup_manual_entry'));
            status.className = 'small text-warning';
        });
    });
}

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
            <td><button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button></td>
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
            <td><button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button></td>
        `;
        table.appendChild(row);
        respondentIndex++;
    });
}

document.addEventListener('click', function (event) {
    if (event.target && event.target.classList.contains('removeRow')) {
        event.target.closest('tr').remove();
    }
});
</script>
@endpush
