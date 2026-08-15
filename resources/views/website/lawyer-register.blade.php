@extends('website.layouts.weblayout')

@section('title', 'Lawyer Registration')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7" data-aos="fade-up">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Lawyer Registration</h3>

                    <!-- Step Indicator -->
                    <ul class="nav nav-pills mb-4 justify-content-center" id="registrationSteps">
                        <li class="nav-item">
                            <span class="nav-link active" id="step1-tab">Step 1: Validate Bar ID</span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link disabled" id="step2-tab">Step 2: Complete Profile</span>
                        </li>
                    </ul>

                    <!-- Step 1 -->
                    <div id="step1">
                        <div class="mb-3">
                            <label for="bar_council_id" class="form-label">SCB Membership No.</label>
                            <input type="text" id="bar_council_id" class="form-control" placeholder="Enter your SCB Membership No.">
                            <div id="bar-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                        <button id="validateBarBtn" class="btn btn-gold w-100">Validate</button>
                    </div>

                    <!-- Step 2 -->
                    <div id="step2" style="display:none;">
                        <form id="lawyer-form" method="POST" action="{{ route('lawyer.register') }}">
                            @csrf
                            <input type="hidden" name="bar_council_id" id="hidden_bar_id">

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" readonly required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control" readonly required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-gold w-100">Register</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const step1Tab = document.getElementById('step1-tab');
    const step2Tab = document.getElementById('step2-tab');
    const step1Div = document.getElementById('step1');
    const step2Div = document.getElementById('step2');
    const validateBarBtn = document.getElementById('validateBarBtn');
    const barInput = document.getElementById('bar_council_id');
    const fullName = document.getElementById('full_name');
    const phone = document.getElementById('phone');
    const hiddenBarId = document.getElementById('hidden_bar_id');
    const barError = document.getElementById('bar-error');

    validateBarBtn.addEventListener('click', function () {
        const barId = barInput.value.trim();
        if(!barId) {
            barError.innerText = 'Please enter your SCB Membership No.';
            barError.style.display = 'block';
            return;
        }

        fetch("{{ route('validate.bar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ bar_council_id: barId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Prefill
                fullName.value = data.full_name;
                phone.value = data.phone;
                hiddenBarId.value = barId;

                // Show step 2
                step1Div.style.display = 'none';
                step2Div.style.display = 'block';

                // Update step indicator
                step1Tab.classList.remove('active');
                step1Tab.classList.add('disabled');
                step2Tab.classList.remove('disabled');
                step2Tab.classList.add('active');

                barError.style.display = 'none';
            } else {
                barError.innerText = data.message;
                barError.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            barError.innerText = 'Server error, try again later.';
            barError.style.display = 'block';
        });
    });
});
</script>
@endsection
