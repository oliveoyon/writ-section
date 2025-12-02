@extends('website.layouts.lawyerlayout')

@section('title', __('lawyer.title'))

@section('content')
<div class="container py-4">

    <h3 class="mb-4">{{ __('lawyer.case.create_title') }}</h3>

    <form action="{{ route('lawyer.case.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- CASE INFO -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                {{ __('lawyer.case.basic_info') }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>{{ __('lawyer.case.case_type') }}</label>
                        <input type="text" name="case_type" class="form-control" value="{{ old('case_type') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>{{ __('lawyer.case.subject') }}</label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label>{{ __('lawyer.case.description') }}</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- PETITIONERS -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                {{ __('lawyer.case.petitioners') }}
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="petitioners_table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('lawyer.case.name') }}</th>
                            <th>{{ __('lawyer.case.address') }}</th>
                            <th>{{ __('lawyer.case.phone') }}</th>
                            <th>{{ __('lawyer.case.email') }}</th>
                            <th>{{ __('lawyer.case.nid') }}</th>
                            <th><button type="button" class="btn btn-success btn-sm" id="addPetitioner">+</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="petitioners[0][name]" class="form-control" required></td>
                            <td><input type="text" name="petitioners[0][address]" class="form-control"></td>
                            <td><input type="text" name="petitioners[0][phone]" class="form-control"></td>
                            <td><input type="email" name="petitioners[0][email]" class="form-control"></td>
                            <td><input type="text" name="petitioners[0][nid]" class="form-control"></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESPONDENTS -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                {{ __('lawyer.case.respondents') }}
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="respondents_table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('lawyer.case.name') }}</th>
                            <th>{{ __('lawyer.case.designation') }}</th>
                            <th>{{ __('lawyer.case.organization') }}</th>
                            <th>{{ __('lawyer.case.address') }}</th>
                            <th><button type="button" class="btn btn-success btn-sm" id="addRespondent">+</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="respondents[0][name]" class="form-control" required></td>
                            <td><input type="text" name="respondents[0][designation]" class="form-control"></td>
                            <td><input type="text" name="respondents[0][organization]" class="form-control"></td>
                            <td><input type="text" name="respondents[0][address]" class="form-control"></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FILE UPLOAD -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                {{ __('lawyer.case.upload_files') }}
            </div>
            <div class="card-body">
                <input type="file" name="files[]" class="form-control" multiple>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('lawyer.case.submit') }}</button>
    </form>
</div>

<!-- DYNAMIC ROW JS -->
@push('scripts')
<script>
let petitionerIndex = 1;
let respondentIndex = 1;

// Add Petitioner Row
document.getElementById('addPetitioner').addEventListener('click', function(){
    const table = document.querySelector('#petitioners_table tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="petitioners[${petitionerIndex}][name]" class="form-control" required></td>
        <td><input type="text" name="petitioners[${petitionerIndex}][address]" class="form-control"></td>
        <td><input type="text" name="petitioners[${petitionerIndex}][phone]" class="form-control"></td>
        <td><input type="email" name="petitioners[${petitionerIndex}][email]" class="form-control"></td>
        <td><input type="text" name="petitioners[${petitionerIndex}][nid]" class="form-control"></td>
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
        <td><input type="text" name="respondents[${respondentIndex}][name]" class="form-control" required></td>
        <td><input type="text" name="respondents[${respondentIndex}][designation]" class="form-control"></td>
        <td><input type="text" name="respondents[${respondentIndex}][organization]" class="form-control"></td>
        <td><input type="text" name="respondents[${respondentIndex}][address]" class="form-control"></td>
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
