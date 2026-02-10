<aside class="sidebar">
  <!-- Sidebar Header -->
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
      <img src="{{ asset('assets/img/logo.webp') }}" alt="Undi">
      <span>Undi</span>
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
          <li><a class="nav-link " href=" ">List</a></li>


          <li><a class="nav-link " href="roles.html">Groups</a></li>
        </ul>
      </li>



      <li class="nav-item">
        <a class="nav-link " href="{{ route('event') }}" data-sidebar-tooltip="Event">
          <i class="ph-light ph-calendar-blank"></i>
          <span>Event</span>
        </a>
      </li>



      <!-- Apps Section -->
      <li class="nav-heading"><span>Apps</span></li>



      <li class="nav-item">
        <a class="nav-link " href="apps-kanban.html" data-sidebar-tooltip="Kanban Board">
          <i class="ph-light ph-kanban"></i>
          <span>Kanban Board</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="apps-chat.html" data-sidebar-tooltip="Chat">
          <i class="ph-light ph-chat-circle-dots"></i>
          <span>Chat</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="apps-contacts.html" data-sidebar-tooltip="Contacts">
          <i class="ph-light ph-address-book"></i>
          <span>Contacts</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="apps-file-manager.html" data-sidebar-tooltip="File Manager">
          <i class="ph-light ph-folder-open"></i>
          <span>File Manager</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="apps-email.html" data-sidebar-tooltip="Email">
          <i class="ph-light ph-envelope"></i>
          <span>Email</span>
        </a>
      </li>



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
      <a href="profile.html" class="sidebar-footer-profile">
        <img src="{{auth()->user()->profile->getProfilePictureUrlAttribute()}}" alt="User" class="sidebar-footer-avatar"
          data-avatar>
        <div class="sidebar-footer-info">
          <div class="sidebar-footer-name">{{ auth()->user()->name }}</div>
          <div class="sidebar-footer-role">{{auth()->user()->role}}</div>
        </div>
      </a>
      <div class="sidebar-footer-actions">
        <a href="settings.html" class="sidebar-footer-action" title="Settings">
          <i class="ph-light ph-gear"></i>
        </a>
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