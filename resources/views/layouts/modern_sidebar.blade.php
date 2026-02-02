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
            <li><a class="nav-link " href="{{ route('dashboard') }}">Insert</a></li>
 
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
            <li><a class="nav-link " href="users.html">List</a></li>
             {{-- <li><a class="nav-link " href="users-edit.html">User Edit</a></li>
             <li><a class="nav-link " href="users-view.html">User View</a></li> --}}
            {{-- <li><a class="nav-link " href="profile.html">Profile</a></li> --}}
  
            <li><a class="nav-link " href="roles.html">Roles & Permissions</a></li>
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
          <a class="nav-link " href="apps-todo.html" data-sidebar-tooltip="Todo List">
            <i class="ph-light ph-check-square"></i>
            <span>Todo List</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="apps-support.html" data-sidebar-tooltip="Support Center">
            <i class="ph-light ph-headset"></i>
            <span>Support Center</span>
          </a>
        </li>

        <!-- UI Elements Section -->
        <li class="nav-heading"><span>UI Elements</span></li>

        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Components">
            <i class="ph-light ph-puzzle-piece"></i>
            <span>Components</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="components-alerts.html">Alerts</a></li>
            <li><a class="nav-link " href="components-accordion.html">Accordion</a></li>
            <li><a class="nav-link " href="components-badges.html">Badges</a></li>
            <li><a class="nav-link " href="components-breadcrumbs.html">Breadcrumbs</a></li>
            <li><a class="nav-link " href="components-buttons.html">Buttons</a></li>
            <li><a class="nav-link " href="components-cards.html">Cards</a></li>
            <li><a class="nav-link " href="components-carousel.html">Carousel</a></li>
            <li><a class="nav-link " href="components-dropdowns.html">Dropdowns</a></li>
            <li><a class="nav-link " href="components-list-group.html">List Group</a></li>
            <li><a class="nav-link " href="components-modal.html">Modal</a></li>
            <li><a class="nav-link " href="components-nav-tabs.html">Navs & Tabs</a></li>
            <li><a class="nav-link " href="components-offcanvas.html">Offcanvas</a></li>
            <li><a class="nav-link " href="components-pagination.html">Pagination</a></li>
            <li><a class="nav-link " href="components-popovers.html">Popovers</a></li>
            <li><a class="nav-link " href="components-progress.html">Progress</a></li>
            <li><a class="nav-link " href="components-spinners.html">Spinners</a></li>
            <li><a class="nav-link " href="components-toasts.html">Toasts</a></li>
            <li><a class="nav-link " href="components-tooltips.html">Tooltips</a></li>
          </ul>
        </li>

        <!-- Widgets -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Widgets">
            <i class="ph-light ph-grid-four"></i>
            <span>Widgets</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="widgets-cards.html">Cards</a></li>
            <li><a class="nav-link " href="widgets-banners.html">Banners</a></li>
            <li><a class="nav-link " href="widgets-charts.html">Charts</a></li>
            <li><a class="nav-link " href="widgets-apps.html">Apps</a></li>
            <li><a class="nav-link " href="widgets-data.html">Data</a></li>
          </ul>
        </li>

        <!-- Forms Section -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Forms">
            <i class="ph-light ph-textbox"></i>
            <span>Forms</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="forms-elements.html">Form Elements</a></li>
            <li><a class="nav-link " href="forms-layouts.html">Form Layouts</a></li>
            <li><a class="nav-link " href="forms-validation.html">Validation</a></li>
            <li><a class="nav-link " href="forms-wizard.html">Wizard</a></li>
            <li><a class="nav-link " href="forms-editors.html">Rich Editors</a></li>
            <li><a class="nav-link " href="forms-pickers.html">Date/Time Pickers</a></li>
            <li><a class="nav-link " href="forms-select.html">Advanced Select</a></li>
            <li><a class="nav-link " href="forms-upload.html">File Upload</a></li>
          </ul>
        </li>

        <!-- Tables Section -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Tables">
            <i class="ph-light ph-table"></i>
            <span>Tables</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="tables-basic.html">Basic Tables</a></li>
            <li><a class="nav-link " href="tables-datatables.html">DataTables</a></li>
            <li><a class="nav-link " href="tables-responsive.html">Responsive Tables</a></li>
          </ul>
        </li>

        <!-- Charts Section -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Charts">
            <i class="ph-light ph-chart-bar"></i>
            <span>Charts</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="charts-apexcharts.html">ApexCharts</a></li>
            <li><a class="nav-link " href="charts-chartjs.html">Chart.js</a></li>
            <li><a class="nav-link " href="charts-echarts.html">ECharts</a></li>
          </ul>
        </li>

        <!-- Icons Section -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Icons">
            <i class="ph-light ph-diamond"></i>
            <span>Icons</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="icons-bootstrap.html">Bootstrap Icons</a></li>
            <li><a class="nav-link " href="icons-remixicon.html">Remix Icons</a></li>
            <li><a class="nav-link " href="icons-fontawesome.html">Font Awesome</a></li>
            <li><a class="nav-link " href="icons-phosphor.html">Phosphor Icons</a></li>
            <li><a class="nav-link " href="icons-lucide.html">Lucide Icons</a></li>
          </ul>
        </li>

        <!-- Pages Section -->
        <li class="nav-heading"><span>Pages</span></li>

        <li class="nav-item">
          <a class="nav-link " href="contact.html" data-sidebar-tooltip="Contact">
            <i class="ph-light ph-envelope-simple"></i>
            <span>Contact</span>
          </a>
        </li>

        <!-- Invoices -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Invoices">
            <i class="ph-light ph-receipt"></i>
            <span>Invoices</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="invoice-list.html">Invoice List</a></li>
            <li><a class="nav-link " href="invoice.html">Invoice View</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="pricing.html" data-sidebar-tooltip="Pricing">
            <i class="ph-light ph-tag"></i>
            <span>Pricing</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="faq.html" data-sidebar-tooltip="FAQ">
            <i class="ph-light ph-question"></i>
            <span>FAQ</span>
          </a>
        </li>

        <!-- Error Pages -->
        <li class="nav-item has-submenu ">
          <a class="nav-link" href="#" aria-expanded="false" data-sidebar-tooltip="Error Pages">
            <i class="ph-light ph-warning"></i>
            <span>Error Pages</span>
            <i class="ph-light ph-caret-down nav-arrow"></i>
          </a>
          <ul class="nav-submenu ">
            <li><a class="nav-link " href="error-404.html">404 Not Found</a></li>
            <li><a class="nav-link " href="error-403.html">403 Forbidden</a></li>
            <li><a class="nav-link " href="error-500.html">500 Server Error</a></li>
            <li><a class="nav-link " href="error-maintenance.html">Maintenance</a></li>
            <li><a class="nav-link " href="error-coming-soon.html">Coming Soon</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="timeline.html" data-sidebar-tooltip="Timeline">
            <i class="ph-light ph-clock-counter-clockwise"></i>
            <span>Timeline</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="search-results.html" data-sidebar-tooltip="Search Results">
            <i class="ph-light ph-magnifying-glass"></i>
            <span>Search Results</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link " href="{{ route('testimport') }}" data-sidebar-tooltip="Blank Page">
            <i class="ph-light ph-file"></i>
            <span>Blank Page</span>
          </a>
        </li>

      </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="sidebar-footer-user">
        <a href="profile.html" class="sidebar-footer-profile">
          <img src="{{ asset('assets/img/profile-img.webp') }}" alt="User" class="sidebar-footer-avatar">
          <div class="sidebar-footer-info">
            <div class="sidebar-footer-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-footer-role">Administrator</div>
          </div>
        </a>
        <div class="sidebar-footer-actions">
          <a href="settings.html" class="sidebar-footer-action" title="Settings">
            <i class="ph-light ph-gear"></i>
          </a>
          <a href="auth-login.html" class="sidebar-footer-action sidebar-footer-logout" title="Logout">
            <i class="ph-light ph-sign-out"></i>
          </a>
        </div>
      </div>
    </div>
  </aside>