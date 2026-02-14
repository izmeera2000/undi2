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
      <li class="nav-item">
        <a class="nav-link active" href="{{ route('dashboard') }}" data-sidebar-tooltip="Dashboard">
          <i class="ph-light ph-squares-four"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- Dashboards Submenu -->
      <li class="nav-item has-submenu ">
        <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Dashboards">
          <i class="ph-light ph-gauge"></i>
          <span>Pengundi</span>
          <i class="ph-light ph-caret-down nav-arrow"></i>
        </a>
        <ul class="nav-submenu ">
          <li><a class="nav-link " href="{{ route('pengundi.analysis') }}">Analytics</a></li>
          <li><a class="nav-link " href="{{ route('pengundi.bulkimport') }}">Bulk Import</a></li>

        </ul>
      </li>

      <!-- Users -->
      <li class="nav-item has-submenu ">
        <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Users">
          <i class="ph-light ph-users"></i>
          <span>Staff</span>
          <i class="ph-light ph-caret-down nav-arrow"></i>
        </a>
        <ul class="nav-submenu ">
          <li><a class="nav-link " href="{{ route('staff.list') }}">List</a></li>


        </ul>
      </li>


      <li class="nav-item has-submenu ">
        <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Users">
          <i class="umno-logo">
            @include('layouts.logo')

          </i>

          <span>Members</span>
          <i class="ph-light ph-caret-down nav-arrow"></i>
        </a>
        <ul class="nav-submenu ">
          <li><a class="nav-link " href="{{ route('members.list') }}">List</a></li>


          <li><a class="nav-link " href="{{ route('members.list') }}">Groups</a></li>
        </ul>
      </li>



      <li class="nav-item">
        <a class="nav-link " href="{{ route('event') }}" data-sidebar-tooltip="Event">
          <i class="ph-light ph-calendar-blank"></i>
          <span>Events</span>
        </a>
      </li>

      <li class="nav-heading"><span>Settings</span></li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('parlimen.index') }}" data-sidebar-tooltip="Parlimen">
          <i class="ph-light ph-file"></i>
          <span>Parlimen</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('dun.index') }}" data-sidebar-tooltip="Dun">
          <i class="ph-light ph-file"></i>
          <span>Dun</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('dm.index') }}" data-sidebar-tooltip="DM">
          <i class="ph-light ph-file"></i>
          <span>DM</span>
        </a>
      </li>



      <!-- Apps Section -->
      <li class="nav-heading"><span>Test</span></li>



      <li class="nav-item">
        <a class="nav-link " href="{{ route('testimport') }}" data-sidebar-tooltip="Test">
          <i class="ph-light ph-file"></i>
          <span>Test</span>
        </a>
      </li>

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

 
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
          @csrf
          <button type="submit" class="sidebar-footer-action sidebar-footer-logout" title="Logout"
            style="background: none; border: none;">
            <i class="ph-light ph-sign-out"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
</aside>