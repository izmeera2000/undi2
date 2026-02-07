<!DOCTYPE html>
<html lang="en">

@include('layouts.modern_head')

<body>
    <!-- Header -->
    <!-- Header -->





    @yield('content')


    <!-- Back to Top -->
    <a href="#" class="back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    @include('layouts.modern_script')


    @stack('scripts')

</body>

</html>