<!DOCTYPE html>
<html lang="en">

@include('layouts.modern_head')

<body>
  <!-- Header -->
  <!-- Header -->
  @include('layouts.modern_header')

  <!-- Mobile Search -->
  <div class="mobile-search">
    <form class="search-form" action="search-results.html" method="GET">
      <input type="search" name="q" placeholder="Search..." autocomplete="off">
      <button type="submit"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <!-- Mobile Header Menu -->
  <div class="mobile-header-menu">
    <div class="mobile-header-menu-content">
      <!-- Theme Toggle -->
      <button class="mobile-menu-item theme-toggle" title="Toggle Theme">
        <i class="ph ph-moon theme-icon-dark"></i>
        <i class="ph ph-sun theme-icon-light"></i>
        <span class="mobile-menu-label">Theme</span>
      </button>

      <!-- Notifications -->
      <a href="notifications.html" class="mobile-menu-item">
        <i class="bi bi-bell"></i>
        <span class="badge">3</span>
        <span class="mobile-menu-label">Notifications</span>
      </a>

      <!-- Profile -->
      <a href="profile.html" class="mobile-menu-item">
        <i class="bi bi-person"></i>
        <span class="mobile-menu-label">Profile</span>
      </a>

      <!-- Settings -->
      <a href="settings.html" class="mobile-menu-item">
        <i class="bi bi-gear"></i>
        <span class="mobile-menu-label">Settings</span>
      </a>

      <!-- Sign Out -->
      <a href="auth-login.html" class="mobile-menu-item mobile-menu-item-danger">
        <i class="bi bi-box-arrow-right"></i>
        <span class="mobile-menu-label">Sign Out</span>
      </a>
    </div>
  </div>

  <!-- Sidebar -->
  <!-- Sidebar -->
  @include('layouts.modern_sidebar')


  <!-- Sidebar Overlay (Mobile) -->
  <div class="sidebar-overlay"></div>

  <!-- Main Content -->
  <main class="main">
    <div class="main-content">



    @include('layouts.modern_breadcrumb')
    

@yield('content')






    </div>
    <!-- Footer -->
    <!-- Footer -->
    @include('layouts.modern_footer')

  </main>

  <!-- Back to Top -->
  <a href="#" class="back-to-top">
    <i class="bi bi-arrow-up"></i>
  </a>

  @include('layouts.modern_script')

 
  @stack('scripts')

</body>

</html>