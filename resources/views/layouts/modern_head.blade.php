<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Dashboard')</title>

  <meta name="robots" content="noindex, nofollow">
  <meta name="description" content="ModernAdmin - Bootstrap Admin Template">
  <meta name="keywords" content="admin, dashboard, bootstrap">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/phosphor-icons/phosphor-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/lucide-icons/lucide.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/simple-datatables/style.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/choices.js/choices.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">

  <!-- Main CSS -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
</head>