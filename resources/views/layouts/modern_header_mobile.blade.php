<div class="mobile-header-menu">
  <div class="mobile-header-menu-content">
    <!-- Theme Toggle -->


    <!-- Notifications -->
    <a href="notifications.html" class="mobile-menu-item">
      <i class="bi bi-bell"></i>
      <span class="badge">3</span>
      <span class="mobile-menu-label">Notifications</span>
    </a>



    <!-- Settings -->
    <a href="{{ route('staff.show', ['staff' => auth()->user()]) }}" class="mobile-menu-item">
      <i class="bi bi-gear"></i>
      <span class="mobile-menu-label">Settings</span>
    </a>

    <!-- Sign Out -->
    <a href="#" class="mobile-menu-item mobile-menu-item-danger"
      onclick="event.preventDefault(); submitLogout('mobile-logout-form');">
      <i class="bi bi-box-arrow-right"></i>
      <span class="mobile-menu-label">Sign Out</span>
    </a>
    <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
      @csrf
    </form>



  </div>
</div>