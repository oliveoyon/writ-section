@extends('dashboard.layouts.admin')
@section('title', 'Dashboard')
@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Home Page</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Home Bangladesh</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="app-content">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-12">
                    <div class="callout callout-info">
                        For detailed documentation of Form visit
                        <a href="https://getbootstrap.com/docs/5.3/forms/overview/" target="_blank"
                            rel="noopener noreferrer" class="callout-link">
                            Bootstrap Form
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    @push('scripts')
    @endpush
@endsection
