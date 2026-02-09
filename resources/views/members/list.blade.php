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
   

        <!-- Users Table -->
        <div class="card g-4 mb-4">
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
 