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
  @include('layouts.modern_header_mobile')



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
    <audio id="notification-sound" src="{{ asset('assets/sounds/notification.mp3') }}" preload="auto"></audio>
    @include('layouts.modern_footer')

  </main>

  <!-- Back to Top -->
  <a href="#" class="back-to-top">
    <i class="bi bi-arrow-up"></i>
  </a>

   <!-- Add somewhere in your HTML -->
  <div id="pdfSpinner" class="overlay-spinner d-none">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  @include('layouts.modern_script')


 


  @stack('scripts')

</body>

</html>