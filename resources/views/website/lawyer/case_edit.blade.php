@extends('website.layouts.lawyerlayout')

@section('title', __('lawyer.case.edit_title'))

@section('content')
<style>
    main {
        padding-top: 40px !important;
    }

    .lawyer-sidebar {
        background: #00284d;
        border-radius: 10px;
        padding: 20px 0;
        color: #fff;
        width: 250px;
    }

    .lawyer-sidebar a {
        display: block;
        padding: 12px 20px;
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }

    .lawyer-sidebar a:hover,
    .lawyer-sidebar a.active {
        background: #d4a017;
        color: #fff;
    }

    .profile-section-title {
        font-size: 20px;
        font-weight: 700;
        color: #003366;
        margin-bottom: 10px;
    }

    .lawyer-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .case-form-label {
        color: #2f3a45;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .case-form-control {
        border: 1px solid #cfd8e3;
        border-radius: 8px;
        min-height: 46px;
    }

    .case-form-control:focus {
        border-color: #0b5ed7;
        box-shadow: 0 0 0 0.16rem rgba(13, 110, 253, 0.15);
    }

    @media (max-width: 767px) {
        .lawyer-sidebar {
            display: none !important;
        }
    }
</style>

<div class="container py-4" style="min-height:75vh;">
    <div class="row">
        {{-- Sidebar --}}
        @include('website.lawyer.sidebar')

        {{-- Main Content --}}
        <div class="col-md-9">

            <h3 class="mb-4">{{ __('lawyer.case.edit_title') }}</h3>

            <form action="{{ route('lawyer.case.update', $case->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- CASE INFO -->
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.case.basic_info') }}</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="case-form-label">{{ __('lawyer.case.case_type') }}</label>
                                <select name="case_type" class="form-select case-form-control" required>
                                    <option value="">Select One</option>
                                    @foreach($caseTypes as $caseType)
                                        <option value="{{ $caseType }}" @selected(old('case_type', $case->case_type) === $caseType)>
                                            {{ $caseType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="case-form-label">{{ __('lawyer.case.description') }}</label>
                                <textarea name="description" class="form-control case-form-control" rows="4">{{ old('description', $case->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PETITIONERS -->
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.case.petitioners') }}</h5>
                        <table class="table table-bordered" id="petitioners_table">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('lawyer.case.name_or_organization') }}</th>
                                    <th>{{ __('lawyer.case.represented_by') }}</th>
                                    <th>{{ __('lawyer.case.designation') }}</th>
                                    <th>{{ __('lawyer.case.address') }}</th>
                                    <th>
                                        <button type="button" class="btn btn-success btn-sm" id="addPetitioner">+</button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($case->petitioners as $i => $p)
                                <tr>
                                    <td><input type="text" name="petitioners[{{ $i }}][name_or_organization]" class="form-control" value="{{ old("petitioners.$i.name_or_organization", $p->name_or_organization) }}" required></td>
                                    <td><input type="text" name="petitioners[{{ $i }}][represented_by]" class="form-control" value="{{ old("petitioners.$i.represented_by", $p->represented_by) }}"></td>
                                    <td><input type="text" name="petitioners[{{ $i }}][designation]" class="form-control" value="{{ old("petitioners.$i.designation", $p->designation) }}"></td>
                                    <td><textarea name="petitioners[{{ $i }}][address]" class="form-control" rows="1">{{ old("petitioners.$i.address", $p->address) }}</textarea></td>
                                    <td></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RESPONDENTS -->
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.case.respondents') }}</h5>
                        <table class="table table-bordered" id="respondents_table">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('lawyer.case.name_or_organization') }}</th>
                                    <th>{{ __('lawyer.case.represented_by') }}</th>
                                    <th>{{ __('lawyer.case.designation') }}</th>
                                    <th>{{ __('lawyer.case.address') }}</th>
                                    <th>
                                        <button type="button" class="btn btn-success btn-sm" id="addRespondent">+</button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($case->respondents as $i => $r)
                                <tr>
                                    <td><input type="text" name="respondents[{{ $i }}][name_or_organization]" class="form-control" value="{{ old("respondents.$i.name_or_organization", $r->name_or_organization) }}" required></td>
                                    <td><input type="text" name="respondents[{{ $i }}][represented_by]" class="form-control" value="{{ old("respondents.$i.represented_by", $r->represented_by) }}"></td>
                                    <td><input type="text" name="respondents[{{ $i }}][designation]" class="form-control" value="{{ old("respondents.$i.designation", $r->designation) }}"></td>
                                    <td><textarea name="respondents[{{ $i }}][address]" class="form-control" rows="1">{{ old("respondents.$i.address", $r->address) }}</textarea></td>
                                    <td></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FILE UPLOAD -->
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.case.upload_files') }}</h5>
                        <input type="file" name="files[]" class="form-control" multiple>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('lawyer.case.update') }}</button>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
let petitionerIndex = {{ $case->petitioners->count() }};
let respondentIndex = {{ $case->respondents->count() }};

// Add Petitioner Row
document.getElementById('addPetitioner').addEventListener('click', function(){
    const table = document.querySelector('#petitioners_table tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="petitioners[${petitionerIndex}][name_or_organization]" class="form-control" required></td>
        <td><input type="text" name="petitioners[${petitionerIndex}][represented_by]" class="form-control"></td>
        <td><input type="text" name="petitioners[${petitionerIndex}][designation]" class="form-control"></td>
        <td><textarea name="petitioners[${petitionerIndex}][address]" class="form-control" rows="1"></textarea></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
    `;
    table.appendChild(row);
    petitionerIndex++;
});

// Add Respondent Row
document.getElementById('addRespondent').addEventListener('click', function(){
    const table = document.querySelector('#respondents_table tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="respondents[${respondentIndex}][name_or_organization]" class="form-control" required></td>
        <td><input type="text" name="respondents[${respondentIndex}][represented_by]" class="form-control"></td>
        <td><input type="text" name="respondents[${respondentIndex}][designation]" class="form-control"></td>
        <td><textarea name="respondents[${respondentIndex}][address]" class="form-control" rows="1"></textarea></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
    `;
    table.appendChild(row);
    respondentIndex++;
});

// Remove any row
document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('removeRow')){
        e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection
