<header class="header">
    <!-- Header Left -->
    <div class="header-left">
        <a href="{{ route('dashboard') }}" class="header-logo">
            <img src="{{ asset('assets/img/logo.webp') }}" alt="ModernAdmin">
            <span>ModernAdmin</span>
        </a>

        <button class="sidebar-toggle" title="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- Header Search -->
    <div class="header-search">
        <form class="search-form" action="#" method="GET">
            <i class="bi bi-search search-icon"></i>
            <input type="search" name="q" placeholder="Search..." autocomplete="off">
            <kbd class="search-shortcut">Ctrl+K</kbd>
        </form>
    </div>

    <!-- Header Right -->
    <div class="header-right">

        <!-- Apps Dropdown -->
        <div class="header-action dropdown apps-dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end apps-menu">
                <div class="apps-menu-header">
                    <h6>Quick Access</h6>
                </div>

                <div class="apps-grid">
                    <a href="{{ route('dashboard') }}" class="apps-item">
                        <div class="apps-item-icon primary">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <span>Dashboard</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon success">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <span>Projects</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon info">
                            <i class="bi bi-chat-square-text"></i>
                        </div>
                        <span>Messages</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon warning">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <span>Contacts</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon danger">
                            <i class="bi bi-check2-square"></i>
                        </div>
                        <span>Tasks</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon secondary">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <span>Calendar</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon primary">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <span>Files</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon info">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <span>Email</span>
                    </a>

                    <a href="#" class="apps-item">
                        <div class="apps-item-icon muted">
                            <i class="bi bi-gear"></i>
                        </div>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button class="header-action theme-toggle">
            <i class="ph ph-moon theme-icon-dark"></i>
            <i class="ph ph-sun theme-icon-light"></i>
        </button>

        <!-- Notifications -->
        <div class="header-action dropdown notification-dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="badge">3</span>
            </button>

            <div class="dropdown-menu dropdown-menu-end">
                <div class="notification-header">
                    <h6>Notifications</h6>
                </div>

                <div class="notification-list">
                    <a href="#" class="notification-item">
                        <div class="notification-avatar success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Order Completed</div>
                            <div class="notification-time">5 min ago</div>
                        </div>
                    </a>
                </div>

                <div class="notification-footer">
                    <a href="#">View all</a>
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="header-action dropdown user-dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <img src="{{ asset('assets/img/profile-img.webp') }}" class="avatar">
            </button>

            <div class="dropdown-menu dropdown-menu-end">
                <div class="user-dropdown-header">
                    <img src="{{ asset('assets/img/profile-img.webp') }}" class="user-dropdown-avatar">
                    <div class="user-dropdown-info">
                        <div class="user-dropdown-name">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="user-dropdown-role">
                            {{ auth()->user()->email }}
                        </div>
                    </div>
                </div>

                <div class="user-dropdown-body">
                    <a class="user-dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person"></i>
                        My Profile
                    </a>

                    <a class="user-dropdown-item" href="#">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </div>

                <div class="user-dropdown-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-dropdown-logout">
                            <i class="bi bi-box-arrow-right"></i>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
