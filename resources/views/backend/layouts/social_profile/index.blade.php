@extends('backend.app')

@section('title', 'Social Profiles')

@push('styles')
<style>
    .social-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 25px;
        height: 100%;
        transition: all 0.3s ease;
    }
    .social-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        border-color: rgba(255, 255, 255, 0.2);
    }
    .social-icon {
        font-size: 40px;
        margin-bottom: 15px;
        color: #CFA267;
    }
    .social-title {
        color: #fff;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 18px;
    }
    .social-desc {
        color: #aaa;
        font-size: 13px;
        margin-bottom: 20px;
    }
    .save-btn {
        background: #CFA267;
        color: #1a1a1a;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        margin-top: 20px;
    }
    .save-btn:hover {
        background: #dabc90;
        transform: translateY(-2px);
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
                    <h1 class="page-title text-white">Social Profiles</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Social Profiles</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card bg-transparent border-0 shadow-none">
                        <div class="card-body">
                            <form action="{{ route('admin.social-profile.update') }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="row">
                                    <!-- Facebook -->
                                    <div class="col-md-3 mb-4">
                                        <div class="social-card text-center">
                                            <i class="fa-brands fa-facebook social-icon"></i>
                                            <h3 class="social-title">Facebook</h3>
                                            <p class="social-desc">Link to your Facebook page.</p>
                                            
                                            <div class="form-group text-start">
                                                <input class="form-control" type="url" name="facebook_url" placeholder="https://facebook.com/yourpage" value="{{ $setting->facebook_url ?? '' }}">
                                                @error('facebook_url')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instagram -->
                                    <div class="col-md-3 mb-4">
                                        <div class="social-card text-center">
                                            <i class="fa-brands fa-instagram social-icon"></i>
                                            <h3 class="social-title">Instagram</h3>
                                            <p class="social-desc">Link to your Instagram profile.</p>
                                            
                                            <div class="form-group text-start">
                                                <input class="form-control" type="url" name="instagram_url" placeholder="https://instagram.com/yourprofile" value="{{ $setting->instagram_url ?? '' }}">
                                                @error('instagram_url')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Twitter -->
                                    <div class="col-md-3 mb-4">
                                        <div class="social-card text-center">
                                            <i class="fa-brands fa-twitter social-icon"></i>
                                            <h3 class="social-title">Twitter / X</h3>
                                            <p class="social-desc">Link to your Twitter feed.</p>
                                            
                                            <div class="form-group text-start">
                                                <input class="form-control" type="url" name="twitter_url" placeholder="https://twitter.com/yourhandle" value="{{ $setting->twitter_url ?? '' }}">
                                                @error('twitter_url')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pinterest -->
                                    <div class="col-md-3 mb-4">
                                        <div class="social-card text-center">
                                            <i class="fa-brands fa-pinterest social-icon"></i>
                                            <h3 class="social-title">Pinterest</h3>
                                            <p class="social-desc">Link to your Pinterest boards.</p>
                                            
                                            <div class="form-group text-start">
                                                <input class="form-control" type="url" name="pinterest_url" placeholder="https://pinterest.com/yourboards" value="{{ $setting->pinterest_url ?? '' }}">
                                                @error('pinterest_url')
                                                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12 text-center">
                                        <button class="save-btn" type="submit">
                                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Save Social Links
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
