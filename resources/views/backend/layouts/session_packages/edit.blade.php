@extends('backend.app', ['title' => 'Update Session Package'])

@section('content')

<style>
    .feature-card {
        border: 1px solid #2a2a2a !important;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: none;
        background-color: #1a1a1a !important;
    }
    .feature-card-header {
        background: #2a2a2a !important;
        color: #CFA267 !important;
        padding: 14px 20px;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #3a3a3a !important;
    }
    .feature-card-header i {
        font-size: 16px;
        color: #CFA267 !important;
    }
    .feature-card-body {
        background: transparent !important;
        padding: 20px 24px;
    }
    .feature-switch-col {
        border-right: 1px dashed #3a3a3a;
        padding-right: 24px;
    }
    .feature-switch-col .switch-label {
        font-weight: 600;
        font-size: 14px;
        color: #ffffff !important;
        margin-bottom: 4px;
        display: block;
    }
    .feature-switch-col small {
        font-size: 12px;
        color: #a0a0a0 !important;
        line-height: 1.6;
    }
    .feature-text-col {
        padding-left: 28px;
    }
    .feature-text-col label {
        font-weight: 600;
        font-size: 13px;
        color: #ffffff !important;
        margin-bottom: 6px;
    }
    .feature-text-col textarea {
        border-radius: 8px;
        border: 1px solid #3a3a3a !important;
        background-color: #2a2a2a !important;
        color: #e0e0e0 !important;
        font-size: 13px;
        resize: vertical;
        transition: border-color .2s, box-shadow .2s;
    }
    .feature-text-col textarea:focus {
        border-color: #CFA267 !important;
        box-shadow: 0 0 0 3px rgba(207, 162, 103, 0.15) !important;
    }
    .custom-switch-container {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .custom-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        flex-shrink: 0;
    }
    .custom-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        margin: 0;
        padding: 0;
    }
    .custom-switch label {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #2a2a2a !important;
        transition: .3s;
        border-radius: 24px;
        border: 1px solid #3a3a3a !important;
        margin-bottom: 0 !important;
    }
    .custom-switch label:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: #a0a0a0 !important;
        transition: .3s;
        border-radius: 50%;
    }
    .custom-switch input:checked + label {
        background-color: #CFA267 !important;
        border-color: #CFA267 !important;
    }
    .custom-switch input:checked + label:before {
        transform: translateX(22px);
        background-color: #121212 !important;
    }
    .custom-switch-text {
        font-weight: 600;
        font-size: 14px;
        color: #ffffff !important;
        user-select: none;
        margin: 0;
        padding: 0;
        line-height: 1;
    }
</style>

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ isset($crud) ? ucwords(str_replace('_', ' ', $crud)) : 'Session Package' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i
                                    class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.session-packages.index') }}">Session
                                Packages</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Edit Session Package</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST"
                                action="{{ route('admin.session-packages.update', $package->id) }}">
                                @csrf

                                <div class="row mb-4">

                                    {{-- Multilingual Tabs --}}
                                    <div class="col-md-12 mb-4">
                                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="pills-en-tab" data-bs-toggle="pill" data-bs-target="#pills-en" type="button" role="tab" aria-controls="pills-en" aria-selected="true">English</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-fr-tab" data-bs-toggle="pill" data-bs-target="#pills-fr" type="button" role="tab" aria-controls="pills-fr" aria-selected="false">Français</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-es-tab" data-bs-toggle="pill" data-bs-target="#pills-es" type="button" role="tab" aria-controls="pills-es" aria-selected="false">Español</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="pills-tabContent">
                                            {{-- English Tab --}}
                                            <div class="tab-pane fade show active" id="pills-en" role="tabpanel" aria-labelledby="pills-en-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="name_en" class="form-label">Package Name (EN) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" placeholder="e.g. Premium Therapy Session" id="name_en" value="{{ old('name_en', $package->name_en) }}">
                                                            @error('name_en') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="duration_en" class="form-label">Duration (EN) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('duration_en') is-invalid @enderror" name="duration_en" placeholder="e.g. 30 minutes / 1 hour" id="duration_en" value="{{ old('duration_en', $package->duration_en) }}">
                                                            @error('duration_en') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-3">
                                                            <label for="description_en" class="form-label">Description (EN)</label>
                                                            <textarea class="summernote form-control @error('description_en') is-invalid @enderror" name="description_en" id="description_en" placeholder="Enter package description" rows="4">{{ old('description_en', $package->description_en) }}</textarea>
                                                            @error('description_en') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- French Tab --}}
                                            <div class="tab-pane fade" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="name_fr" class="form-label">Package Name (FR) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('name_fr') is-invalid @enderror" name="name_fr" placeholder="e.g. Séance de thérapie premium" id="name_fr" value="{{ old('name_fr', $package->name_fr) }}">
                                                            @error('name_fr') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="duration_fr" class="form-label">Duration (FR) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('duration_fr') is-invalid @enderror" name="duration_fr" placeholder="e.g. 30 minutes / 1 heure" id="duration_fr" value="{{ old('duration_fr', $package->duration_fr) }}">
                                                            @error('duration_fr') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-3">
                                                            <label for="description_fr" class="form-label">Description (FR)</label>
                                                            <textarea class="summernote form-control @error('description_fr') is-invalid @enderror" name="description_fr" id="description_fr" placeholder="Enter package description in French" rows="4">{{ old('description_fr', $package->description_fr) }}</textarea>
                                                            @error('description_fr') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Spanish Tab --}}
                                            <div class="tab-pane fade" id="pills-es" role="tabpanel" aria-labelledby="pills-es-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="name_es" class="form-label">Package Name (ES) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('name_es') is-invalid @enderror" name="name_es" placeholder="e.g. Sesión de terapia premium" id="name_es" value="{{ old('name_es', $package->name_es) }}">
                                                            @error('name_es') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="duration_es" class="form-label">Duration (ES) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control @error('duration_es') is-invalid @enderror" name="duration_es" placeholder="e.g. 30 minutos / 1 hora" id="duration_es" value="{{ old('duration_es', $package->duration_es) }}">
                                                            @error('duration_es') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-3">
                                                            <label for="description_es" class="form-label">Description (ES)</label>
                                                            <textarea class="summernote form-control @error('description_es') is-invalid @enderror" name="description_es" id="description_es" placeholder="Enter package description in Spanish" rows="4">{{ old('description_es', $package->description_es) }}</textarea>
                                                            @error('description_es') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <hr style="border-color: #3a3a3a;">
                                        <h5 class="mb-3 text-white">General Information</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="type" class="form-label">Package Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('type') is-invalid @enderror" name="type" id="type">
                                                <option value="">-- Select Type --</option>
                                                <option value="vip_access" {{ old('type', $package->type) == 'vip_access' ? 'selected' : '' }}>VIP Access</option>
                                                <option value="quick_advice" {{ old('type', $package->type) == 'quick_advice' ? 'selected' : '' }}>Quick Advice</option>
                                                <option value="personal_advice" {{ old('type', $package->type) == 'personal_advice' ? 'selected' : '' }}>Personal Advice</option>
                                            </select>
                                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   step="0.01"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   name="price"
                                                   placeholder="e.g. 99.00"
                                                   id="price"
                                                   value="{{ old('price', $package->price) }}">
                                            @error('price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Feature Settings Card --}}
                                    <div class="col-md-12 mt-2">
                                        <div class="feature-card">
                                            <div class="feature-card-header">
                                                <i class="fe fe-star"></i>
                                                Feature Settings
                                            </div>
                                            <div class="feature-card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4 feature-switch-col">
                                                        <span class="switch-label">Mark as Featured</span>
                                                        <div class="custom-switch-container">
                                                            <div class="custom-switch">
                                                                <input type="checkbox"
                                                                       name="is_feature"
                                                                       id="is_feature"
                                                                       value="1"
                                                                       {{ old('is_feature', $package->is_feature) ? 'checked' : '' }}>
                                                                <label for="is_feature"></label>
                                                            </div>
                                                            <span class="custom-switch-text" id="is_feature_status">Disabled</span>
                                                        </div>
                                                        <small>Enable this to highlight the package as a featured offering on the front-end.</small>
                                                    </div>
                                                    <div class="col-md-8 feature-text-col" id="feature_text_group"
                                                         style="display: {{ old('is_feature', $package->is_feature) ? 'block' : 'none' }};">
                                                        <label for="feature_text">Featured Highlight Text</label>
                                                        <textarea class="form-control @error('feature_text') is-invalid @enderror"
                                                                  name="feature_text"
                                                                  id="feature_text"
                                                                  rows="3"
                                                                  placeholder="e.g. Most popular choice for lasting results">{{ old('feature_text', $package->feature_text) }}</textarea>
                                                        @error('feature_text')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-8 feature-text-col text-center text-muted" id="feature_placeholder"
                                                         style="display: {{ old('is_feature', $package->is_feature) ? 'none' : 'flex' }}; align-items: center; justify-content: center; min-height: 80px; gap: 8px;">
                                                        <i class="fe fe-lock" style="font-size:20px;"></i>
                                                        <span style="font-size:13px;">Toggle the switch to add a featured highlight text.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="col-md-12">
                                        <div class="form-group mt-2">
                                            <button class="submit btn btn-primary px-4 shadow-sm" type="submit">
                                                <i class="fa-solid fa-floppy-disk me-2"></i> Update Package
                                            </button>
                                            <a href="{{ route('admin.session-packages.index') }}" class="btn btn-outline-secondary ms-2 px-4 shadow-sm"><i class="fa-solid fa-xmark me-1"></i> Cancel</a>
                                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var checkbox    = document.getElementById('is_feature');
        var textGroup   = document.getElementById('feature_text_group');
        var placeholder = document.getElementById('feature_placeholder');
        var statusText  = document.getElementById('is_feature_status');

        function toggle() {
            if (checkbox.checked) {
                textGroup.style.display   = 'block';
                placeholder.style.display = 'none';
                statusText.textContent = 'Enabled';
            } else {
                textGroup.style.display   = 'none';
                placeholder.style.display = 'flex';
                statusText.textContent = 'Disabled';
            }
        }

        checkbox.addEventListener('change', toggle);
        toggle();
    });
</script>

@endsection
