@extends('dashboard.layouts.admin-layout')

@section('title', 'User Profile')

@section('content')
<section>
    {{-- Flash Messages --}}
    @if(auth()->user()->status == 2)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Notice:</strong> You must change your password to continue using the system.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success:</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Oops!</strong> Please fix the following issues:
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="container-fluid">
        <div class="row mb-4">
            <!-- User Info Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>User Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Name:</strong> {{ auth()->user()->name }}
                            </li>
                            <li class="list-group-item">
                                <strong>Email:</strong> {{ auth()->user()->email }}
                            </li>
                            <li class="list-group-item">
                                <strong>District:</strong> {{ auth()->user()->district->name ?? 'N/A' }}
                            </li>
                            <li class="list-group-item">
                                <strong>PNGO:</strong> {{ auth()->user()->pngo->name ?? 'N/A' }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Password Change Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.change-my-password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection