@extends('backend.app', ['title' => 'View Session Package'])

@section('content')

<style>
    .detail-card {
        border: 1px solid #2a2a2a !important;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: none;
        background-color: #1a1a1a !important;
    }
    .detail-card-header {
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
    .detail-card-header i {
        font-size: 16px;
        color: #CFA267 !important;
    }
    .detail-card-body {
        background: transparent !important;
        padding: 24px;
    }
    .detail-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #2a2a2a;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #a0a0a0 !important;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 200px;
        flex-shrink: 0;
    }
    .detail-value {
        color: #e0e0e0 !important;
        font-size: 14px;
        line-height: 1.6;
    }
    .type-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.active {
        background-color: rgba(0, 184, 148, 0.15);
        color: #00B894;
    }
    .status-badge.inactive {
        background-color: rgba(255, 107, 107, 0.15);
        color: #FF6B6B;
    }
    .feature-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background-color: rgba(207, 162, 103, 0.15);
        color: #CFA267;
    }
    .language-tabs {
        margin-top: 16px;
    }
    .language-tabs .nav-pills .nav-link {
        color: #a0a0a0;
        border: 1px solid #3a3a3a;
        margin-right: 8px;
        border-radius: 8px;
        padding: 6px 16px;
        font-size: 13px;
    }
    .language-tabs .nav-pills .nav-link.active {
        background-color: #CFA267;
        color: #121212;
        border-color: #CFA267;
    }
    .lang-content-card {
        background: #2a2a2a;
        border-radius: 10px;
        padding: 20px;
        margin-top: 12px;
    }
    .lang-content-card h6 {
        color: #CFA267;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    .lang-content-card p {
        color: #e0e0e0;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 0;
    }
</style>

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'Session Package' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.session-packages.index') }}">Session Packages</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">
                                <i class="fa-solid fa-box me-2" style="color: #CFA267;"></i>
                                Package Details - #{{ $package->id }}
                            </h3>
                            <div class="card-options">
                                <a href="{{ route('admin.session-packages.edit', $package->id) }}" class="btn btn-sm btn-primary shadow-sm me-2">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                                <a href="{{ route('admin.session-packages.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body border-0">

                            {{-- Main Details --}}
                            <div class="detail-card">
                                <div class="detail-card-header">
                                    <i class="fe fe-info"></i>
                                    General Information
                                </div>
                                <div class="detail-card-body">
                                    <div class="detail-row">
                                        <span class="detail-label">Package Name</span>
                                        <span class="detail-value">{{ $package->name }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Type</span>
                                        <span class="detail-value">
                                            @php
                                                $colors = [
                                                    'vip_access' => ['bg' => '#CFA267', 'text' => '#121212'],
                                                    'quick_advice' => ['bg' => '#6C5CE7', 'text' => '#FFFFFF'],
                                                    'personal_advice' => ['bg' => '#00B894', 'text' => '#FFFFFF'],
                                                ];
                                                $color = $colors[$package->type] ?? ['bg' => '#636e72', 'text' => '#FFFFFF'];
                                                $label = $typeLabels[$package->type] ?? ucwords(str_replace('_', ' ', $package->type ?? 'N/A'));
                                            @endphp
                                            <span class="type-badge" style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                                {{ $label }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Price</span>
                                        <span class="detail-value">${{ number_format($package->price, 2) }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Duration</span>
                                        <span class="detail-value">{{ $package->duration }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Badge</span>
                                        <span class="detail-value">{{ $package->badge ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Status</span>
                                        <span class="detail-value">
                                            <span class="status-badge {{ $package->status }}">
                                                {{ ucfirst($package->status) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Featured</span>
                                        <span class="detail-value">
                                            @if($package->is_feature)
                                                <span class="feature-badge">
                                                    <i class="fe fe-star me-1" style="font-size: 11px;"></i>
                                                    {{ $package->feature_text ?? 'Featured' }}
                                                </span>
                                            @else
                                                <span style="color: #666;">No</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Created At</span>
                                        <span class="detail-value">{{ $package->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Updated At</span>
                                        <span class="detail-value">{{ $package->updated_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Multilingual Content --}}
                            <div class="detail-card">
                                <div class="detail-card-header">
                                    <i class="fe fe-globe"></i>
                                    Multilingual Content
                                </div>
                                <div class="detail-card-body">
                                    <div class="language-tabs">
                                        <ul class="nav nav-pills mb-0" id="lang-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="lang-en-tab" data-bs-toggle="pill" data-bs-target="#lang-en" type="button" role="tab">English</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="lang-fr-tab" data-bs-toggle="pill" data-bs-target="#lang-fr" type="button" role="tab">Français</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="lang-es-tab" data-bs-toggle="pill" data-bs-target="#lang-es" type="button" role="tab">Español</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="lang-tabContent">
                                            <div class="tab-pane fade show active" id="lang-en" role="tabpanel">
                                                <div class="lang-content-card">
                                                    <h6>Name (EN)</h6>
                                                    <p>{{ $package->name_en }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Duration (EN)</h6>
                                                    <p>{{ $package->duration_en }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Description (EN)</h6>
                                                    <p>{{ $package->description_en ?? 'No description provided.' }}</p>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="lang-fr" role="tabpanel">
                                                <div class="lang-content-card">
                                                    <h6>Name (FR)</h6>
                                                    <p>{{ $package->name_fr }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Duration (FR)</h6>
                                                    <p>{{ $package->duration_fr }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Description (FR)</h6>
                                                    <p>{{ $package->description_fr ?? 'No description provided.' }}</p>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="lang-es" role="tabpanel">
                                                <div class="lang-content-card">
                                                    <h6>Name (ES)</h6>
                                                    <p>{{ $package->name_es }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Duration (ES)</h6>
                                                    <p>{{ $package->duration_es }}</p>
                                                    <hr style="border-color: #3a3a3a; margin: 16px 0;">
                                                    <h6>Description (ES)</h6>
                                                    <p>{{ $package->description_es ?? 'No description provided.' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->

@endsection
