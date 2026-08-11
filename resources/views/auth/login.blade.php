@extends('auth.app')

@section('content')
<!-- GOOGLE FONTS -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

    /* BASE PAGE RESET & OVERRIDES */
    body.login-img {
        background: #080914 !important;
        background-image: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Outfit', 'Inter', sans-serif !important;
        color: #fff !important;
        overflow: hidden !important;
        position: relative !important;
    }

    /* Hide standard dashboard switcher elements on login page */
    .demo-icon,
    .switcher-wrapper,
    #switcher-back,
    .sidebar-right {
        display: none !important;
    }

    #global-loader {
        background: #080914 !important;
    }

    .page {
        height: 100vh !important;
        min-height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        position: relative !important;
        z-index: 1 !important;
        overflow: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .page > div {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100vh !important;
        min-height: 100vh !important;
        position: relative !important;
        overflow: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* BACKGROUND GLOW ORBS */
    .glow-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
        pointer-events: none;
    }

    .glow-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.35;
        mix-blend-mode: screen;
        transition: all 1s ease;
    }

    .orb-1 {
        top: 15%;
        left: 5%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(147, 51, 234, 0.6) 0%, rgba(79, 70, 229, 0.05) 75%);
        animation: float1 15s infinite ease-in-out alternate;
    }

    .orb-2 {
        bottom: 10%;
        right: 5%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.6) 0%, rgba(219, 39, 119, 0.05) 75%);
        animation: float2 18s infinite ease-in-out alternate;
    }

    /* BACKGROUND ORBIT LINES */
    .orbit-line-1 {
        position: absolute;
        width: 600px;
        height: 600px;
        border: 1.5px solid rgba(168, 85, 247, 0.15);
        border-radius: 50%;
        top: 15%;
        left: -180px;
        pointer-events: none;
        box-shadow: 0 0 30px rgba(168, 85, 247, 0.08), inset 0 0 30px rgba(168, 85, 247, 0.03);
        transform: rotate(-15deg);
        z-index: 0;
    }

    .orbit-line-2 {
        position: absolute;
        width: 750px;
        height: 750px;
        border: 1.5px solid rgba(99, 102, 241, 0.2);
        border-radius: 50%;
        bottom: -150px;
        right: -250px;
        pointer-events: none;
        box-shadow: 0 0 40px rgba(99, 102, 241, 0.12);
        z-index: 0;
    }

    /* TOP BAR ELEMENTS */
    .top-logo-badge {
        position: absolute;
        top: 24px;
        left: 24px;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
        z-index: 10;
        user-select: none;
    }

    .top-theme-toggle {
        position: absolute;
        top: 24px;
        right: 24px;
        background: rgba(19, 22, 38, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #cbd5e1;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        user-select: none;
    }

    .top-theme-toggle:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    .top-theme-toggle svg {
        transition: transform 0.5s ease;
    }

    .top-theme-toggle:hover svg {
        transform: rotate(30deg);
    }

    .top-theme-toggle .chevron-arrow {
        font-size: 11px;
        opacity: 0.6;
    }

    /* GLASSMORPHISM CARD */
    .admin-login-card {
        background: rgba(19, 22, 38, 0.6);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 28px;
        width: 90%;
        max-width: 430px;
        padding: 40px 32px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        z-index: 5;
        text-align: center;
        transition: all 0.3s ease;
        position: relative !important;
        margin: 0 !important;
    }

    .card-icon-badge {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #a855f7 0%, #3b82f6 100%);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px auto;
        box-shadow: 0 8px 25px rgba(168, 85, 247, 0.3);
        color: #fff;
    }

    .card-icon-badge svg {
        stroke: #fff;
    }

    .welcome-title {
        font-size: 26px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .welcome-subtitle {
        font-size: 14px;
        color: #94a3b8;
        margin-bottom: 32px;
    }

    /* INPUTS & FORM CONTROLS */
    .custom-input-group {
        position: relative;
        margin-bottom: 16px;
        width: 100%;
        box-sizing: border-box !important;
        text-align: left;
    }

    .custom-input {
        width: 100%;
        background: rgba(15, 17, 28, 0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-radius: 14px !important;
        padding: 0 48px !important;
        color: #fff !important;
        font-size: 14.5px !important;
        line-height: 52px !important;
        height: 52px !important;
        transition: all 0.3s ease !important;
        outline: none !important;
        box-sizing: border-box !important;
        text-align: left !important;
    }

    .custom-input::placeholder {
        color: #64748b;
    }

    .custom-input:focus {
        border-color: rgba(168, 85, 247, 0.6) !important;
        background: rgba(15, 17, 28, 0.75) !important;
        box-shadow: 0 0 14px rgba(168, 85, 247, 0.2) !important;
    }

    .input-icon-left {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 3;
    }

    .input-icon-right {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
        z-index: 3;
    }

    .input-icon-right:hover {
        color: #cbd5e1;
    }

    .error-message {
        color: #f87171;
        font-size: 12.5px;
        text-align: left;
        margin-top: -10px;
        margin-bottom: 16px;
        padding-left: 4px;
        display: block;
    }

    .recaptcha-container {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* SUBMIT BUTTON */
    .submit-btn {
        width: 100%;
        height: 52px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #a855f7 0%, #3b82f6 100%);
        border: none;
        border-radius: 14px;
        color: #fff;
        font-size: 15.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        margin-top: 8px;
        box-sizing: border-box !important;
    }

    .submit-btn:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 15px rgba(168, 85, 247, 0.25);
    }

    .submit-btn:active {
        transform: translateY(1px);
    }

    /* CARD FOOTER */
    .card-footer-text {
        margin-top: 36px;
        font-size: 12.5px;
        color: #64748b;
    }

    .contact-admin-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .contact-admin-link:hover {
        color: #60a5fa;
        text-decoration: underline;
    }

    @keyframes float1 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(30px, 20px) scale(1.08); }
    }

    @keyframes float2 {
        0% { transform: translate(0, 0) scale(1.05); }
        100% { transform: translate(-20px, -30px) scale(0.95); }
    }

    /* LIGHT THEME OVERRIDES */
    body.login-img.light-theme-override {
        background: #f8fafc !important;
    }

    body.login-img.light-theme-override .glow-orb {
        opacity: 0.15;
    }

    body.login-img.light-theme-override .orbit-line-1 {
        border-color: rgba(168, 85, 247, 0.1);
        box-shadow: 0 0 30px rgba(168, 85, 247, 0.03);
    }

    body.login-img.light-theme-override .orbit-line-2 {
        border-color: rgba(99, 102, 241, 0.12);
        box-shadow: 0 0 40px rgba(99, 102, 241, 0.05);
    }

    body.login-img.light-theme-override .admin-login-card {
        background: rgba(255, 255, 255, 0.75);
        border-color: rgba(99, 102, 241, 0.12);
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.05), 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    body.login-img.light-theme-override .welcome-title {
        color: #0f172a;
    }

    body.login-img.light-theme-override .welcome-subtitle {
        color: #475569;
    }

    body.login-img.light-theme-override .custom-input {
        background: rgba(255, 255, 255, 0.8) !important;
        border-color: rgba(99, 102, 241, 0.15) !important;
        color: #0f172a !important;
    }

    body.login-img.light-theme-override .custom-input::placeholder {
        color: #94a3b8;
    }

    body.login-img.light-theme-override .custom-input:focus {
        border-color: #a855f7 !important;
        background: #fff !important;
        box-shadow: 0 0 14px rgba(168, 85, 247, 0.15) !important;
    }

    body.login-img.light-theme-override .input-icon-left {
        color: #94a3b8;
    }

    body.login-img.light-theme-override .input-icon-right {
        color: #94a3b8;
    }

    body.login-img.light-theme-override .input-icon-right:hover {
        color: #475569;
    }

    body.login-img.light-theme-override .card-footer-text {
        color: #475569;
    }

    body.login-img.light-theme-override .top-theme-toggle {
        background: rgba(255, 255, 255, 0.8);
        border-color: rgba(99, 102, 241, 0.12);
        color: #475569;
    }

    body.login-img.light-theme-override .top-theme-toggle:hover {
        background: #fff;
        color: #0f172a;
    }

    /* Override external CSS limiting form width */
    .login100-form {
        width: 100% !important;
        margin: 0 auto !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-sizing: border-box !important;
    }
</style>

<!-- BACKGROUND EFFECTS -->
<div class="glow-bg">
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
</div>
<div class="orbit-line-1"></div>
<div class="orbit-line-2"></div>

<!-- TOP ACTIONS -->
<div class="top-logo-badge">
    <span>5</span>
</div>

<div class="top-theme-toggle" id="themeToggleBtn">
    <!-- Moon SVG Icon -->
    <svg id="themeToggleIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
    <span class="chevron-arrow">&gt;</span>
</div>

<!-- CONTAINER OPEN -->
<div class="admin-login-card">
    <div class="card-icon-badge">
        <!-- Shield Lock SVG Icon -->
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>

    <h2 class="welcome-title">Welcome Back <span class="wave">👋</span></h2>
    <p class="welcome-subtitle">Sign in to your admin account</p>

    <form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Input -->
        <div class="custom-input-group">
            <div class="input-icon-left">
                <!-- Mail SVG -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <input class="custom-input" type="email" name="email" value="{{ old('email') }}" placeholder="admin@yourdomain.com" required autocomplete="email" autofocus>
        </div>
        @error('email')
            <span class="error-message">{{ $message }}</span>
        @enderror

        <!-- Password Input -->
        <div class="custom-input-group" style="margin-bottom: 24px;">
            <div class="input-icon-left">
                <!-- Lock SVG -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <input class="custom-input" type="password" name="password" id="passwordInput" placeholder="••••••••••" required autocomplete="current-password">
            <button type="button" class="input-icon-right" id="togglePasswordBtn" title="Toggle password visibility">
                <!-- Eye SVG -->
                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
        @error('password')
            <span class="error-message">{{ $message }}</span>
        @enderror

        <!-- Recaptcha -->
        @if(config('settings.recaptcha') === 'yes')
        <div class="recaptcha-container">
            {!! htmlFormSnippet() !!}
            @if ($errors->has('g-recaptcha-response'))
                <span class="error-message" style="margin-top: 4px;">
                    {{ $errors->first('g-recaptcha-response') }}
                </span>
            @endif
        </div>
        @endif

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">
            Sign In
        </button>
    </form>

    
</div>
<!-- CONTAINER CLOSED -->

<!-- JAVASCRIPT FOR INTERACTIONS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password Visibility Toggle
        const passwordInput = document.getElementById('passwordInput');
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (togglePasswordBtn && passwordInput && eyeIcon) {
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    // Eye slash icon
                    eyeIcon.innerHTML = `
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    `;
                } else {
                    // Regular eye icon
                    eyeIcon.innerHTML = `
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    `;
                }
            });
        }

        // Theme Toggle Action (Mockup toggling style)
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        
        if (themeToggleBtn && themeToggleIcon) {
            themeToggleBtn.addEventListener('click', function() {
                const isLight = document.body.classList.toggle('light-theme-override');
                
                if (isLight) {
                    // Switch to Sun Icon
                    themeToggleIcon.innerHTML = `
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    `;
                } else {
                    // Switch to Moon Icon
                    themeToggleIcon.innerHTML = `
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    `;
                }
            });
        }
    });
</script>
@endsection