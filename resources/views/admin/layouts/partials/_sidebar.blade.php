<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ url('/admin') }}">
                VSurvey
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item d-flex me-3">
                <a href="#" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom" onclick="localStorage.setItem('data-bs-theme', 'dark'); window.location.reload();">
                    <i class="ti ti-moon fs-2"></i>
                </a>
                <a href="#" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom" onclick="localStorage.setItem('data-bs-theme', 'light'); window.location.reload();">
                    <i class="ti ti-sun fs-2"></i>
                </a>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url('https://ui-avatars.com/api/?name=Admin')"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.profile.index') }}" class="dropdown-item">Profile</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                
                {{-- ===================== --}}
                {{-- CORE --}}
                {{-- ===================== --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/admin') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-home"></i>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.voice-to-text') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-microphone"></i>
                        </span>
                        <span class="nav-link-title">Voice to Text</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.ai-logs') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-chart-bar"></i>
                        </span>
                        <span class="nav-link-title">AI Logs & Stats</span>
                    </a>
                </li>
                
                {{-- ===================== --}}
                {{-- USERS --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Users</div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users"></i>
                        </span>
                        <span class="nav-link-title">All Users</span>
                    </a>
                </li>
                

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.blocked') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-user-off"></i>
                        </span>
                        <span class="nav-link-title">Blocked Users</span>
                    </a>
                </li>

                {{-- ===================== --}}
                {{-- TRASH --}}
                {{-- ===================== --}}
                <li class="nav-item mt-3 mb-1">
                    <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Trash</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.deleted') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-trash"></i>
                        </span>
                        <span class="nav-link-title">Deleted User</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>