<header class="header">
  <!-- Header Left -->
  <div class="header-left">
    <a href="{{ route('logout') }}" class="header-logo">
      <img src="{{ asset('assets/img/logo2.png') }}" alt="UndiScope">
      <span>UndiScope</span>
    </a>
    <button class="sidebar-toggle" title="Toggle Sidebar">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <!-- Header Search (Desktop) -->
  <div class="header-search">
    <form class="search-form" action="search-results.html" method="GET">
      <i class="bi bi-search search-icon"></i>
      <input type="search" name="q" placeholder="Search..." autocomplete="off">
      <kbd class="search-shortcut">Ctrl+K</kbd>
    </form>
  </div>

  <!-- Header Right -->
  <div class="header-right">
    <!-- Desktop Actions (hidden on mobile, shown in mobile menu) -->
    <div class="header-actions-desktop">
      <!-- Apps Dropdown -->






      <!-- Notifications -->
      <div class="header-action dropdown notification-dropdown">
        <button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="badge">3</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
          <div class="notification-header">
            <h6>Notifications</h6>
            <span class="notification-count">3 new</span>
          </div>
          <div class="notification-list">
            <a href="#" class="notification-item unread">
              <div class="notification-avatar success">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title">Order Completed</div>
                <div class="notification-text">Your order #12345 has been delivered successfully</div>
                <div class="notification-time">
                  <i class="bi bi-clock"></i>
                  5 min ago
                </div>
              </div>
            </a>
            <a href="#" class="notification-item unread">
              <div class="notification-avatar warning">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title">Low Storage Warning</div>
                <div class="notification-text">Server storage is running low (85% used)</div>
                <div class="notification-time">
                  <i class="bi bi-clock"></i>
                  1 hour ago
                </div>
              </div>
            </a>
            <a href="#" class="notification-item unread">
              <div class="notification-avatar info">
                <i class="bi bi-person-plus"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title">New Team Member</div>
                <div class="notification-text">Sarah Johnson joined the design team</div>
                <div class="notification-time">
                  <i class="bi bi-clock"></i>
                  2 hours ago
                </div>
              </div>
            </a>
            <a href="#" class="notification-item">
              <div class="notification-avatar primary">
                <i class="bi bi-chat-left-text"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title">New Comment</div>
                <div class="notification-text">Mike commented on your post</div>
                <div class="notification-time">
                  <i class="bi bi-clock"></i>
                  Yesterday
                </div>
              </div>
            </a>
          </div>
          <div class="notification-footer">
            <a href="notifications.html">
              View all notifications
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- User Dropdown -->
      <div class="header-action dropdown user-dropdown">
        <button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="{{auth()->user()->profile->getProfilePictureUrlAttribute()}}" alt="User" class="avatar"  data-avatar>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
          <div class="user-dropdown-header">
            <img src="{{auth()->user()->profile->getProfilePictureUrlAttribute()}}" alt="User" class="user-dropdown-avatar"  data-avatar>
            <div class="user-dropdown-info">
              <div class="user-dropdown-name">{{ucfirst(auth()->user()->name)}}</div>
              <div class="user-dropdown-role">{{ucfirst(auth()->user()->role)}}</div>
            </div>
          </div>
          <div class="user-dropdown-body">
            <a class="user-dropdown-item" href="profile.html">
              <i class="bi bi-person"></i>
              <span>My Profile</span>
            </a>
            <a class="user-dropdown-item" href="settings.html">
              <i class="bi bi-gear"></i>
              <span>Account Settings</span>
            </a>
            <a class="user-dropdown-item" href="activity.html">
              <i class="bi bi-clock-history"></i>
              <span>Activity Log</span>
            </a>
          </div>
          <div class="user-dropdown-footer">
            <a href="#" class="user-dropdown-logout"
              onclick="event.preventDefault(); document.getElementById('dropdown-logout-form').submit();">
              <i class="bi bi-box-arrow-right"></i>
              <span>Sign Out</span>
            </a>

            <form id="dropdown-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
              @csrf
            </form>
          </div>

        </div>
      </div>
    </div>

    <!-- Mobile Actions (visible only on mobile) -->
    <div class="header-actions-mobile">
      <!-- Search Toggle (Mobile) -->
      <button class="header-action search-toggle" title="Search">
        <i class="bi bi-search"></i>
      </button>

      <!-- Mobile Menu Toggle -->
      <button class="header-action mobile-menu-toggle" title="More">
        <i class="bi bi-three-dots-vertical"></i>
      </button>
    </div>
  </div>
</header>