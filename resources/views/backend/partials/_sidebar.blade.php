@php
use Illuminate\Support\Facades\Route;
@endphp

<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; height: 100%; text-decoration: none;">
                <h1 style="color: #CFA267; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 2px;">VUQIA</h1>
            </a>
        </div>
        <div class="main-sidemenu">
            <input class="form-control form-control-dark w-100 border-0" id="menuSearching" type="text" placeholder="Search" aria-label="Search">
            <ul id="customMenulist" class="side-menu"></ul>
        </div>
        <div class="main-sidemenu">
            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-house side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <li>
                    <h3>Basic</h3>
                </li>
                
             
                                @role('admin')
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.session-packages.*') ? 'has-link active' : '' }}" href="{{ route('admin.session-packages.index') }}">
                        <i class="fa-solid fa-list side-menu__icon"></i>
                        <span class="side-menu__label">Session Packages</span>
                    </a>
                </li>
                @endrole

                @role('admin')
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.category.*') ? 'has-link active' : '' }}" href="{{ route('admin.category.index') }}">
                        <i class="fa-solid fa-list side-menu__icon"></i>
                        <span class="side-menu__label">Category</span>
                    </a>
                </li>
                @endrole

                @role('admin')
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.users.*') ? 'has-link active' : '' }}" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32">
                            <rect width="416" height="416" rx="48" ry="48" />
                            <path d="m192 256 128 0" />
                        </svg>
                        <span class="side-menu__label">User Access</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.users.index') }}" class="slide-item">User</a></li>
                    </ul>
                </li>
                @endrole

                @role('admin')
                <li>
                    <h3>CMS</h3>
                </li>
            
               
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.legal.*') ? 'has-link active' : '' }}" href="{{ route('admin.legal.index') }}">
                        <i class="fa-solid fa-file-pdf side-menu__icon"></i>
                        <span class="side-menu__label">Legal Documents</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.social-profile.*') ? 'has-link active' : '' }}" href="{{ route('admin.social-profile.index') }}">
                        <i class="fa-solid fa-share-nodes side-menu__icon"></i>
                        <span class="side-menu__label">Social Profiles</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.top-creator.*') ? 'has-link active' : '' }}" href="{{ route('admin.top-creator.index') }}">
                        <i class="fa-solid fa-star side-menu__icon"></i>
                        <span class="side-menu__label">Top Creators</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.creators.*') ? 'has-link active' : '' }}" href="{{ route('admin.creators.index') }}">
                        <i class="fa-solid fa-users side-menu__icon"></i>
                        <span class="side-menu__label">Creators</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="side-menu__icon" viewBox="0 0 16 16">
                            <path d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">CMS</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Home Page</span><i class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a href="{{ route('admin.cms.home.safe_space.index') }}" class="sub-slide-item">Safe Space Section</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
    
                @endrole

                <li class="slide">
                    <hr />
                </li>
                <li class="slide">
                    <a class="side-menu__item" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket side-menu__icon"></i>
                        <span class="side-menu__label">Log out</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
                <li class="slide">
                    <hr />
                </li>
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->

<script>
    const menuSearchInput = document.getElementById('menuSearching');
    const customMenuList = document.getElementById('customMenulist');
    const menus = @json(App\Models\Menu::where('status', 1)->orderBy('id', 'DESC')->get());

    function sideMenu() {
        menus.forEach(menu => {
            if (menu.name.toLowerCase().includes(menuSearchInput.value.toLowerCase())) {
                customMenuList.innerHTML += `
                    <li class="slide">
                        <a class="side-menu__item" href="#">
                            <i class="fa-solid fa-bars side-menu__icon"></i>
                            <span class="side-menu__label">${menu.name}</span>
                        </a>
                    </li>
                `;
            }
        });
    }

    menuSearchInput.addEventListener('input', function() {
        customMenuList.innerHTML = '';
        if (menuSearchInput.value.trim() === '') {
            customMenuList.innerHTML = '';
        } else {
            sideMenu();
        }
    });
</script>
