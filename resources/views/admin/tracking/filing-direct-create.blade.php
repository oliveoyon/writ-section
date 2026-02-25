@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">{{ __('tracking.filing.direct_title') }}</h3>
    <p class="text-muted">{{ __('tracking.filing.direct_subtitle') }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.tracking.filing.store-direct') }}" class="card p-4">
        @csrf
        <div class="card p-3 mb-3 border">
            <h5 class="mb-3">{{ __('tracking.filing.lawyer_block_title') }}</h5>
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
        </div>

        <div class="mb-3">
            <label for="case_type" class="form-label">{{ __('tracking.filing.case_type') }}</label>
            <input type="text" id="case_type" name="case_type" class="form-control" value="{{ old('case_type') }}" required>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">{{ __('tracking.filing.subject') }}</label>
            <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">{{ __('tracking.filing.description') }}</label>
            <textarea id="description" name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
        </div>

        @php
            $petitioners = old('petitioners', [['name_or_organization' => '', 'represented_by' => '', 'phone' => '']]);
            $respondents = old('respondents', [['name' => '', 'designation' => '', 'organization' => '', 'address' => '']]);
        @endphp
        <div class="mb-3">
            <h6>{{ __('tracking.filing.petitioners') }}</h6>
            <table class="table table-bordered" id="petitioners_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('tracking.filing.name_or_organization') }}</th>
                        <th>{{ __('tracking.filing.represented_by') }}</th>
                        <th>{{ __('tracking.filing.phone') }}</th>
                        <th><button type="button" class="btn btn-success btn-sm" id="addPetitioner">{{ __('tracking.filing.add_row') }}</button></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($petitioners as $i => $p)
                        <tr>
                            <td><input type="text" name="petitioners[{{ $i }}][name_or_organization]" class="form-control" value="{{ $p['name_or_organization'] ?? '' }}" required></td>
                            <td><input type="text" name="petitioners[{{ $i }}][represented_by]" class="form-control" value="{{ $p['represented_by'] ?? '' }}"></td>
                            <td><input type="text" name="petitioners[{{ $i }}][phone]" class="form-control" value="{{ $p['phone'] ?? '' }}"></td>
                            <td>
                                @if($i > 0)
                                    <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <h6>{{ __('tracking.filing.respondents') }}</h6>
            <table class="table table-bordered" id="respondents_table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('tracking.filing.name') }}</th>
                        <th>{{ __('tracking.filing.designation') }}</th>
                        <th>{{ __('tracking.filing.organization') }}</th>
                        <th>{{ __('tracking.filing.address') }}</th>
                        <th><button type="button" class="btn btn-success btn-sm" id="addRespondent">{{ __('tracking.filing.add_row') }}</button></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($respondents as $i => $r)
                        <tr>
                            <td><input type="text" name="respondents[{{ $i }}][name]" class="form-control" value="{{ $r['name'] ?? '' }}" required></td>
                            <td><input type="text" name="respondents[{{ $i }}][designation]" class="form-control" value="{{ $r['designation'] ?? '' }}"></td>
                            <td><input type="text" name="respondents[{{ $i }}][organization]" class="form-control" value="{{ $r['organization'] ?? '' }}"></td>
                            <td><input type="text" name="respondents[{{ $i }}][address]" class="form-control" value="{{ $r['address'] ?? '' }}"></td>
                            <td>
                                @if($i > 0)
                                    <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('tracking.filing.remove_row') }}</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-brand">{{ __('tracking.filing.create_permanent') }}</button>
    </form>
</div>
@endsection

@push('css')
<style>
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
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
            <td><input type="text" name="petitioners[${petitionerIndex}][phone]" class="form-control"></td>
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
            <td><input type="text" name="respondents[${respondentIndex}][name]" class="form-control" required></td>
            <td><input type="text" name="respondents[${respondentIndex}][designation]" class="form-control"></td>
            <td><input type="text" name="respondents[${respondentIndex}][organization]" class="form-control"></td>
            <td><input type="text" name="respondents[${respondentIndex}][address]" class="form-control"></td>
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
