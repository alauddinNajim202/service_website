@extends('backend.app')

@section('title', 'Legal Documents (PDFs)')

@push('styles')
<style>
    .legal-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
        margin-bottom: 24px;
    }
    .legal-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(207, 162, 103, 0.5); /* Match the VUQIA gold color */
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }
    .legal-icon {
        font-size: 2.5rem;
        color: #CFA267;
        margin-bottom: 16px;
    }
    .legal-title {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .legal-desc {
        color: #adb5bd;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }
    .file-input-wrapper {
        position: relative;
    }
    .file-input-wrapper .form-control {
        background-color: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .file-input-wrapper .form-control:focus {
        border-color: #CFA267;
        box-shadow: 0 0 0 0.25rem rgba(207, 162, 103, 0.25);
    }
    .view-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #CFA267;
        text-decoration: none;
        font-weight: 500;
        margin-top: 12px;
        transition: color 0.2s;
    }
    .view-link:hover {
        color: #fff;
    }
    .save-btn {
        background: linear-gradient(135deg, #CFA267 0%, #b38851 100%);
        border: none;
        color: #1a1a1a;
        font-weight: 600;
        padding: 12px 32px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .save-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(207, 162, 103, 0.4);
        color: #000;
    }
</style>
@endpush

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title text-white">Legal Documents</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Legal Documents</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card bg-transparent border-0 shadow-none">
                        <div class="card-body">
                            <form action="{{ route('admin.legal.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')

                                <div class="row">
                                    <!-- Privacy Policy -->
                                    <div class="col-md-4">
                                        <div class="legal-card text-center">
                                            <i class="fa-solid fa-user-shield legal-icon"></i>
                                            <h3 class="legal-title">Privacy Policy</h3>
                                            <p class="legal-desc">Upload the official Privacy Policy PDF for your users.</p>
                                            
                                            <div class="file-input-wrapper text-start">
                                                <input class="form-control" type="file" id="privacy_pdf" name="privacy_pdf" accept="application/pdf">
                                                @if($setting && $setting->privacy_pdf)
                                                    <a href="{{ asset($setting->privacy_pdf) }}" target="_blank" class="view-link">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Current PDF
                                                    </a>
                                                @endif
                                                @error('privacy_pdf')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Terms of Use -->
                                    <div class="col-md-4">
                                        <div class="legal-card text-center">
                                            <i class="fa-solid fa-file-contract legal-icon"></i>
                                            <h3 class="legal-title">Terms of Use</h3>
                                            <p class="legal-desc">Upload the Terms of Use agreement document.</p>
                                            
                                            <div class="file-input-wrapper text-start">
                                                <input class="form-control" type="file" id="terms_pdf" name="terms_pdf" accept="application/pdf">
                                                @if($setting && $setting->terms_pdf)
                                                    <a href="{{ asset($setting->terms_pdf) }}" target="_blank" class="view-link">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Current PDF
                                                    </a>
                                                @endif
                                                @error('terms_pdf')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cookie Settings -->
                                    <div class="col-md-4">
                                        <div class="legal-card text-center">
                                            <i class="fa-solid fa-cookie-bite legal-icon"></i>
                                            <h3 class="legal-title">Cookie Settings</h3>
                                            <p class="legal-desc">Upload the Cookie Settings and tracking policy PDF.</p>
                                            
                                            <div class="file-input-wrapper text-start">
                                                <input class="form-control" type="file" id="cookie_pdf" name="cookie_pdf" accept="application/pdf">
                                                @if($setting && $setting->cookie_pdf)
                                                    <a href="{{ asset($setting->cookie_pdf) }}" target="_blank" class="view-link">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Current PDF
                                                    </a>
                                                @endif
                                                @error('cookie_pdf')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12 text-center">
                                        <button class="save-btn" type="submit">
                                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Save Legal Documents
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
