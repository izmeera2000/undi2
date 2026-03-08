<aside class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
      <img src="{{ asset('assets/img/logo.png') }}" alt="UndiScope">
      <span>UndiScope</span>
    </a>
    <button class="sidebar-close">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <nav class="sidebar-nav">
    <ul class="nav-menu">

      <x-sidebar.nav-item route="dashboard" icon="ph-light ph-squares-four" label="Dashboard" />

      <x-sidebar.nav-heading label="Main Database" />

      @can('pengundi.view')
        <x-sidebar.nav-group pattern="pengundi.*" icon="ph-light ph-identification-card" label="Pengundi">
          <x-sidebar.nav-link route="pengundi.list" label="All List" />
          {{-- <x-sidebar.nav-item route="pengundi.analytics" label="Analytics" /> --}}
          @can('pengundi.add')
            <x-sidebar.nav-link route="culaan.index" label="Expectations" />
            <x-sidebar.nav-link route="pengundi.bulkimport2" label="Bulk Import" />
          @endcan
        </x-sidebar.nav-group>
      @endcan

      <x-sidebar.nav-group pattern="members.*" label="Members">
        <x-slot name="iconSlot">
          <i class="umno-logo">@include('layouts.logo')</i>
        </x-slot>
        <x-sidebar.nav-link route="members.list" label="Member List" />
        <x-sidebar.nav-link route="members.groups.index" label="Groups" />
        <x-sidebar.nav-link route="members.bulkimport" label="Bulk Import" />
      </x-sidebar.nav-group>

      <x-sidebar.nav-group pattern="staff.*" icon="ph-light ph-users" label="Staff">
        <x-sidebar.nav-link route="staff.list" label="Staff Directory" />
      </x-sidebar.nav-group>

      <x-sidebar.nav-heading label="Operations" />
      <x-sidebar.nav-item route="event" label="Events" icon="ph-light ph-calendar-blank" />
      <x-sidebar.nav-item route="task.index" label="Tasks" icon="ph-light ph-check-square" />



      <x-sidebar.nav-heading label="Configuration" />
      <x-sidebar.nav-item route="elections.index" label="Elections" icon="ph-light ph-seal-check" />

      <x-sidebar.nav-group pattern="geo.*" icon="ph-light ph-map-trifold" label="Jurisdiction">
        <x-sidebar.nav-link route="parlimen.index" label="Parlimen" />
        <x-sidebar.nav-link route="dun.index" label="DUN" />
        <x-sidebar.nav-link route="dm.index" label="DM" />
        <x-sidebar.nav-link route="lokaliti.index" label="Lokaliti" />
      </x-sidebar.nav-group>

      @role('admin')
      <x-sidebar.nav-heading label="Developer Tools" />
      <x-sidebar.nav-item route="testimport" label="Test Import" icon="ph-light ph-flask" />
      <x-sidebar.nav-item route="clear-all" label="System Reset" icon="ph-light ph-warning-octagon" />

      <x-sidebar.nav-group pattern="map.*" label="Maps" icon="ph-light ph-map-pin">

        <x-sidebar.nav-link route="map.page2" label="Live Map" />
        <x-sidebar.nav-link route="map.page" label="Data Fetch" />
      </x-sidebar.nav-group>
      @endrole

    </ul>
  </nav>

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