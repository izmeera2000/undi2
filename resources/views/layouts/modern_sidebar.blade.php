<aside class="sidebar">
  <!-- Sidebar Header -->
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
      <img src="{{ asset('assets/img/logo.png') }}" alt="UndiScope">
      <span>UndiScope</span>
    </a>
    <button class="sidebar-close">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <!-- Sidebar Navigation -->
  <nav class="sidebar-nav">
    <ul class="nav-menu">
      <!-- Main Section -->
      <x-sidebar.nav-item route="dashboard" icon="ph-light ph-squares-four" label="Dashboard" />

      @can('pengundi.view')
        <x-sidebar.nav-group pattern="pengundi.*" icon="ph-light ph-gauge" label="Pengundi">

          <x-sidebar.nav-item route="pengundi.list" label="List" />

          <x-sidebar.nav-item route="pengundi.analytics" label="Analytics" />

          @can('pengundi.add')
            <x-sidebar.nav-item route="pengundi.bulkimport" label="Bulk Import" />

            <x-sidebar.nav-item route="pengundi.bulkimport2" label="Bulk Import 2" />
          @endcan

        </x-sidebar.nav-group>
      @endcan


      <x-sidebar.nav-group pattern="staff.*" icon="ph-light ph-users" label="Staff">

        <x-sidebar.nav-item route="staff.list" label="List" />

      </x-sidebar.nav-group>


      <x-sidebar.nav-group pattern="members.*" label="Members">

        <x-slot name="iconSlot">
          <i class="umno-logo">
            @include('layouts.logo')
          </i>
        </x-slot>

        <x-sidebar.nav-item route="members.list" label="List" />

        <x-sidebar.nav-item route="members.list" label="Groups" />


        <x-sidebar.nav-item route="members.bulkimport" label="Bulk Import" />


      </x-sidebar.nav-group>

      {{-- Events --}}
      <x-sidebar.nav-item route="event" label="Events" icon="ph-light ph-calendar-blank" />

      <x-sidebar.nav-item route="task.index" label="Task" icon="ph-light ph-check-square" />

      {{-- Settings Heading --}}
      <x-sidebar.nav-heading label="Settings" icon="ph-light ph-gear" />

      <x-sidebar.nav-item route="parlimen.index" label="Parlimen" icon="ph-light ph-buildings" />

      <x-sidebar.nav-item route="dun.index" label="Dun" icon="ph-light ph-map-trifold" />

      <x-sidebar.nav-item route="dm.index" label="DM" icon="ph-light ph-map-pin" />

      <x-sidebar.nav-item route="lokaliti.index" label="Lokaliti" icon="ph-light ph-house-line" />

      {{-- Admin-only section --}}
      @role('admin')
      {{-- Apps Heading --}}
      <x-sidebar.nav-heading label="Test" />

      <x-sidebar.nav-item route="testimport" label="Test" icon="ph-light ph-file" />

      <x-sidebar.nav-item route="clear-all" label="Clear All" icon="ph-light ph-file" />

      {{-- Maps Submenu with Custom Icon Slot --}}
      <x-sidebar.nav-group pattern="map.*" label="Maps">
        <x-slot name="iconSlot">
          <i class="umno-logo">@include('layouts.logo')</i>
        </x-slot>

        <x-sidebar.nav-item route="map.page2" label="Fetch" />
        <x-sidebar.nav-item route="map.page" label="Map" />
      </x-sidebar.nav-group>
      @endrole


    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <div class="sidebar-footer-user">
      <a href="{{ route('staff.show', ['staff' => auth()->user()]) }}" class="sidebar-footer-profile">
        <img src="{{auth()->user()->profile->getProfilePictureUrlAttribute()}}" alt="User" class="sidebar-footer-avatar"
          data-avatar>
        <div class="sidebar-footer-info">
          <div class="sidebar-footer-name">{{ auth()->user()->name }}</div>
          <div class="sidebar-footer-role">{{auth()->user()->role}}</div>
        </div>
      </a>
      <div class="sidebar-footer-actions">


        {{-- Logout Link --}}
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); submitLogout('logout-form');"
          class="sidebar-footer-action sidebar-footer-logout" title="Logout">
          <i class="ph-light ph-sign-out"></i>
        </a>


        {{-- Hidden Logout Form --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
          @csrf
        </form>




      </div>
    </div>
  </div>
</aside>