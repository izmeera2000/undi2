@extends('layouts.app')

@section('title', 'List')

@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Staff', 'url' => route('user.list')],
      ['label' => 'List', 'url' => route('user.list')],
    ];

  @endphp

@endsection

@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">
    
 
      <section class="section">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="card user-stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                  <div class="user-stat-icon bg-primary-light">
                    <i class="bi bi-people text-primary"></i>
                  </div>
                  <div>
                    <div class="user-stat-value">248</div>
                    <div class="user-stat-label">Total Users</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card user-stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                  <div class="user-stat-icon bg-success-light">
                    <i class="bi bi-person-check text-success"></i>
                  </div>
                  <div>
                    <div class="user-stat-value">186</div>
                    <div class="user-stat-label">Active Users</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card user-stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                  <div class="user-stat-icon bg-warning-light">
                    <i class="bi bi-person-exclamation text-warning"></i>
                  </div>
                  <div>
                    <div class="user-stat-value">42</div>
                    <div class="user-stat-label">Pending</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card user-stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                  <div class="user-stat-icon bg-info-light">
                    <i class="bi bi-person-plus text-info"></i>
                  </div>
                  <div>
                    <div class="user-stat-value">+12</div>
                    <div class="user-stat-label">New This Month</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="card">
          <div class="card-header">
            <div class="row g-3 align-items-center">
              <div class="col-md-4">
                <div class="input-group">
                  <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-0" placeholder="Search users...">
                </div>
              </div>
              <div class="col-md-8">
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                  <!-- Status Filter -->
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-circle me-1"></i> Status
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item active" href="#"><i class="bi bi-check2 me-2"></i> All</a></li>
                      <li><a class="dropdown-item" href="#">Active</a></li>
                      <li><a class="dropdown-item" href="#">Inactive</a></li>
                      <li><a class="dropdown-item" href="#">Pending</a></li>
                    </ul>
                  </div>
                  <!-- Role Filter -->
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-shield me-1"></i> Role
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item active" href="#"><i class="bi bi-check2 me-2"></i> All Roles</a></li>
                      <li><a class="dropdown-item" href="#">Admin</a></li>
                      <li><a class="dropdown-item" href="#">Manager</a></li>
                      <li><a class="dropdown-item" href="#">User</a></li>
                    </ul>
                  </div>
                  <!-- Bulk Actions -->
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" disabled>
                      Bulk Actions
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="#"><i class="bi bi-envelope me-2"></i> Send Email</a></li>
                      <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i> Export</a></li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i> Delete Selected</a></li>
                    </ul>
                  </div>
                  <!-- Add User -->
                  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-lg me-1"></i> Add User
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th class="ps-4" style="width: 40px;">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                      </div>
                    </th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Joined</th>
                    <th class="text-end pe-4" style="width: 80px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-1.webp" alt="Sarah Johnson">
                          <span class="user-status-badge online"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Sarah Johnson</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="4c3f2d3e2d2462262324223f23220c29342d213c2029622f2321">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-danger-light text-danger">Admin</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">Just now</td>
                    <td class="text-muted">Jan 15, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-2.webp" alt="Michael Chen">
                          <span class="user-status-badge online"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Michael Chen</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="bcd192dfd4d9d2fcd9c4ddd1ccd0d992dfd3d1">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-warning-light text-warning">Manager</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">5 min ago</td>
                    <td class="text-muted">Feb 3, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-3.webp" alt="Emily Rodriguez">
                          <span class="user-status-badge away"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Emily Rodriguez</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="2540484c495c0b5765405d44485549400b464a48">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-info-light text-info">User</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">2 hours ago</td>
                    <td class="text-muted">Mar 12, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-4.webp" alt="David Kim">
                          <span class="user-status-badge offline"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">David Kim</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="05612b6e6c6845607d64687569602b666a68">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-info-light text-info">User</span></td>
                    <td><span class="badge bg-secondary-light text-secondary"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Inactive</span></td>
                    <td class="text-muted">3 days ago</td>
                    <td class="text-muted">Jan 28, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-5.webp" alt="Jessica Taylor">
                          <span class="user-status-badge online"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Jessica Taylor</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="42286c36233b2e2d3002273a232f322e276c212d2f">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-warning-light text-warning">Manager</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">1 hour ago</td>
                    <td class="text-muted">Dec 5, 2023</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-6.webp" alt="Robert Martinez">
                          <span class="user-status-badge away"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Robert Martinez</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="34461a595546405d5a514e74514c55594458511a575b59">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-info-light text-info">User</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">30 min ago</td>
                    <td class="text-muted">Apr 18, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-7.webp" alt="Amanda Wilson">
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Amanda Wilson</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="076629706e6b74686947627f666a776b622964686a">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-info-light text-info">User</span></td>
                    <td><span class="badge bg-warning-light text-warning"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Pending</span></td>
                    <td class="text-muted">Never</td>
                    <td class="text-muted">May 2, 2024</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                          <img src="assets/img/avatars/avatar-8.webp" alt="Chris Thompson">
                          <span class="user-status-badge online"></span>
                        </div>
                        <div>
                          <a href="users-view.html" class="fw-semibold text-dark">Chris Thompson</a>
                          <div class="text-muted small"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="3a59144e5255574a4955547a5f425b574a565f14595557">[email&#160;protected]</a></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge bg-danger-light text-danger">Admin</span></td>
                    <td><span class="badge bg-success-light text-success"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active</span></td>
                    <td class="text-muted">15 min ago</td>
                    <td class="text-muted">Nov 20, 2023</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <a href="users-view.html" class="btn btn-sm btn-light" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="users-edit.html" class="btn btn-sm btn-light" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 border-top">
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Rows per page:</span>
                <select class="form-select form-select-sm" style="width: auto;">
                  <option value="8" selected>8</option>
                  <option value="16">16</option>
                  <option value="24">24</option>
                  <option value="50">50</option>
                </select>
                <span class="text-muted small ms-2">1-8 of 248</span>
              </div>
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item disabled">
                    <a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>
                  </li>
                  <li class="page-item active"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                  <li class="page-item"><a class="page-link" href="#">31</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </section>

      <!-- Add User Modal -->
      <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header border-0 pb-0">
              <div>
                <h5 class="modal-title">Add New User</h5>
                <p class="text-muted small mb-0">Create a new user account</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form>
                <div class="text-center mb-4">
                  <div class="avatar-upload">
                    <div class="avatar-preview">
                      <img src="assets/img/avatars/avatar-placeholder.webp" alt="Avatar" id="avatarPreview">
                    </div>
                    <label class="avatar-edit" for="avatarUpload">
                      <i class="bi bi-camera"></i>
                      <input type="file" id="avatarUpload" accept="image/*" class="d-none">
                    </label>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" placeholder="Enter first name">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" placeholder="Enter last name">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" placeholder="Enter email address">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Role</label>
                    <select class="form-select">
                      <option value="">Select role...</option>
                      <option value="admin">Admin</option>
                      <option value="manager">Manager</option>
                      <option value="user">User</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" placeholder="Enter password">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" placeholder="Confirm password">
                  </div>
                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="sendInvite" checked>
                      <label class="form-check-label" for="sendInvite">
                        Send welcome email with login details
                      </label>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add User
              </button>
            </div>
          </div>
        </div>
      </div>
 

@endsection
 