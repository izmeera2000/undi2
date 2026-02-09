@extends('layouts.app')

@section('title', 'Profile')


@section('content')
    <!-- Welcome & Stats Row -->



    <section class="section">
        <!-- Profile Cover & Card -->

        <form onsubmit="return false;">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-lg-3">
                    <!-- Edit Navigation -->
                    <div class="card edit-nav-card">
                        <div class="card-body p-0">
                            <nav class="edit-nav">
                                <a href="#section-avatar" class="edit-nav-item active">
                                    <i class="bi bi-person-circle"></i>
                                    <span>Avatar &amp; Status</span>
                                </a>
                                <a href="#section-personal" class="edit-nav-item">
                                    <i class="bi bi-person"></i>
                                    <span>Personal Info</span>
                                </a>
                                <a href="#section-contact" class="edit-nav-item">
                                    <i class="bi bi-envelope"></i>
                                    <span>Contact Details</span>
                                </a>

                                @if (auth()->id() == $staff->id)

                                    <a href="#section-access" class="edit-nav-item">
                                        <i class="bi bi-shield-lock"></i>
                                        <span>Access &amp; Security</span>

                                @endif
                                </a>
                                @if (auth()->user()->isAdmin() && auth()->id() !== $staff->id)
                                    <a href="#section-danger" class="edit-nav-item text-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <span>Danger Zone</span>
                                    </a>
                                @endif

                            </nav>
                        </div>
                    </div>

                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Avatar & Status Section -->
                    <div class="card" id="section-avatar">
                        <div class="card-header">

                            <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i>Avatar &amp; Account
                                Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar-upload-wrapper">
                                        <div class="avatar-upload-preview">
                                            <img src="{{$staff->profile->getProfilePictureUrlAttribute()}}" alt="John Doe"
                                                id="avatarPreview">

                                        </div>
                                        @if (auth()->id() == $staff->id)

                                            <label class="avatar-upload-btn" for="avatarInput">
                                                <i class="bi bi-camera"></i>
                                            </label>


                                            <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*">
                                        @endif
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">{{ $staff->name }}</h5>
                                    <p class="text-muted mb-2">{{ $staff->email }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        {!! $staff->status_badge !!}
                                        {!! $staff->role_badge !!}
                                    </div>
                                </div>

                            </div>
 
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="card" id="section-personal">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $staff->name }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Bio</label>
                                    <textarea name="bio" class="form-control" rows="3"
                                        placeholder="A brief description about the user...">{{ $staff->profile->bio }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Section -->
                    <div class="card" id="section-contact">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-envelope me-2"></i>Contact Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="email" name="email" class="form-control" value="{{ $staff->email }}"
                                            required>
                                        <span class="input-group-text text-success" title="Verified"><i
                                                class="bi bi-check-circle-fill"></i></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control"
                                        value="{{ $staff->profile->phone }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ $staff->profile->address }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-bar  mb-4">
                        <div class="form-actions-info">
                            {{-- <span class="text-muted">Last updated: May 10, 2024 at 3:45 PM</span> --}}
                        </div>
                        <div class="form-actions-buttons">
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Access & Security Section -->

                    @if (auth()->id() == $staff->id)

                        <div class="card" id="section-access">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Access &amp; Security</h5>
                            </div>
                            <div class="card-body">
                                <!-- Role Assignment -->
                                <div class="row g-3 mb-4">


                                </div>



                                <h6 class="mb-3">Change Password</h6>
                                <div class="alert alert-info d-flex gap-2 mb-3">
                                    <i class="bi bi-info-circle flex-shrink-0"></i>
                                    <div>Leave password fields empty to keep the current password.</div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control password-field" id="newPassword"
                                                placeholder="Enter new password" autocomplete="new-password">
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <div class="input-group">

                                            <input type="password" class="form-control password-field"
                                                placeholder="Confirm new password" autocomplete="new-password"
                                                id="confirmPassword">
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="form-actions-bar  mb-4">
                            <div class="form-actions-info">
                                {{-- <span class="text-muted">Last updated: May 10, 2024 at 3:45 PM</span> --}}
                            </div>
                            <div class="form-actions-buttons">
                                <button type="submit" class="btn btn-primary" id="savePasswordBtn">
                                    <i class="bi bi-check-lg me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    @endif


                    @if (auth()->user()->isAdmin() && auth()->id() !== $staff->id)
                        <!-- Danger Zone Section -->
                        <div class="card border-danger" id="dangerZone" data-user-id="{{ $staff->id }}">
                            <div class="card-header bg-danger-light">
                                <h5 class="card-title mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger
                                    Zone</h5>
                            </div>
                            <div class="card-body">
                                <div class="danger-actions">
                                    <div class="danger-action">
                                        <div class="danger-action-info">
                                            <div class="danger-action-title">Role</div>
                                            <div class="danger-action-desc">Change Role.</div>
                                        </div>

                                        <!-- Dropdown for Role actions -->
                                        <div class="dropdown">
                                            <button class="btn btn-outline-warning btn-sm dropdown-toggle" type="button"
                                                id="roleDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Change Role
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item change-role" data-role="admin">Assign Admin</a>
                                                </li>
                                                <li> <a class="dropdown-item change-role" data-role="moderator">Assign
                                                        Moderator</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item change-role" data-role="user">Assign User</a>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>


                                    <div class="danger-action">
                                        <div class="danger-action-info">
                                            <div class="danger-action-title">Account Status</div>
                                            <div class="danger-action-desc">Temporarily disable or reactivate user access.</div>
                                        </div>
                                        <button type="button" id="statusBtn"
                                            class="btn btn-sm {{ $staff->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            data-user-id="{{ $staff->id }}"
                                            data-status="{{ $staff->status }}">{{ $staff->status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </div>

                                    <div class="danger-action">
                                        <div class="danger-action-info">
                                            <div class="danger-action-title">Revoke All Sessions</div>
                                            <div class="danger-action-desc">Log user out of all devices immediately.</div>
                                        </div>
                                        <button type="button" id="revokeSessionsBtn" class="btn btn-outline-danger btn-sm">
                                            Revoke
                                        </button>
                                    </div>
                                    <div class="danger-action">
                                        <div class="danger-action-info">
                                            <div class="danger-action-title">Delete Account</div>
                                            <div class="danger-action-desc">Permanently delete this user. This action cannot
                                                be undone.</div>
                                        </div>
                                        <button class="btn btn-danger btn-sm" id="deleteUserBtn">
                                            Delete
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </form>
    </section>


@endsection

@push('scripts')
    <script>
        $(document).on('click', '.change-role', function () {
            let role = $(this).data('role');
            let userId = $('#dangerZone').data('user-id');

            if (!confirm('Change role to ' + role + '?')) return;

            $.post({
                url: `/staff/${userId}/role`,
                data: {
                    role: role,
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    alert('Role updated successfully');
                    location.reload();
                },
                error: function () {
                    alert('Failed to change role');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#statusBtn').on('click', function () {
                let userId = $(this).data('user-id');
                let currentStatus = $(this).data('status'); // active, suspended, etc.
                let action = currentStatus === 'active' ? 'suspend' : 'activate';
                let confirmMsg = currentStatus === 'active' ?
                    'Suspend this account?' : 'Activate this account?';

                if (!confirm(confirmMsg)) return;

                $.post(`/staff/${userId}/${action}`, { _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.success) {
                        // Update button text & status dynamically
                        let newStatus = action === 'suspend' ? 'suspended' : 'active';
                        $('#statusBtn')
                            .text(newStatus === 'active' ? 'Suspend' : 'Activate')
                            .data('status', newStatus)
                            .removeClass('btn-outline-warning btn-outline-success')
                            .addClass(newStatus === 'active' ? 'btn-outline-warning' : 'btn-outline-success');

                        // Optionally update status badge elsewhere on page
                        $('#profileStatusBadge')
                            .removeClass()
                            .addClass('badge ' + getStatusBadgeClass(newStatus))
                            .text(capitalizeFirstLetter(newStatus));

                        alert(`User is now ${newStatus}`);
                    }
                });

            });

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'active': return 'bg-success';
                    case 'suspended': return 'bg-danger';
                    case 'not_yet_login': return 'bg-warning';
                    default: return 'bg-secondary';
                }
            }

            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }
        });

    </script>

    <script>
        $('#revokeSessionsBtn').on('click', function () {
            let userId = $('#dangerZone').data('user-id');

            if (!confirm('Revoke all sessions?')) return;

            $.post({
                url: `/staff/${userId}/suspend`,
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    alert('All sessions revoked');
                }
            });
        });
    </script>

    <script>
        $('#deleteUserBtn').on('click', function () {
            let userId = $('#dangerZone').data('user-id');

            if (!confirm('This will permanently remove the account. Continue?')) return;

            $.ajax({
                url: `/staff/${userId}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    alert('User deleted');
                    window.location.href = "{{ route('staff.list') }}";
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#saveProfileBtn').on('click', function (e) {
                e.preventDefault();

                // Collect all inputs from personal + contact sections
                let formData = {
                    name: $('input[name="name"]').val(),
                    bio: $('textarea[name="bio"]').val(),
                    email: $('input[name="email"]').val(),
                    phone: $('input[name="phone"]').val(),
                    address: $('input[name="address"]').val(),
                    _token: '{{ csrf_token() }}' // CSRF token for security
                };

                $.ajax({
                    url: '/staff/{{ $staff->id ?? auth()->id() }}/profile', // route to your controller
                    method: 'POST',
                    data: formData,
                    success: function (res) {
                        console.log('Response:', res); // Debugging: Check response structure

                        // Check if the data is there and update the form fields dynamically
                        if (res && res.user) {
                            console.log('Updating fields with new values...');

                            // Ensure values are being updated correctly
                            $('input[name="name"]').val(res.user.name);  // Update Name
                            $('textarea[name="bio"]').val(res.user.profile?.bio ?? '');  // Update Bio
                            $('input[name="phone"]').val(res.user.profile?.phone ?? '');  // Update Phone
                            $('input[name="address"]').val(res.user.profile?.address ?? '');  // Update Address

                            // Check if the fields are being updated in the DOM
                            console.log(formData);



                            alert('Profile updated successfully!');
                        } else {
                            alert('Failed to update profile. Invalid response data.');
                        }
                    },
                    error: function (err) {
                        console.log('Error:', err); // Debugging: Log the error if the AJAX call fails
                        alert('Failed to update profile. Check your input.');
                    }
                });
            });
        });



    </script>


    <script>
        $('#savePasswordBtn').on('click', function () {
            let userId = $('#dangerZone').data('user-id');

            let password = $('#newPassword').val();
            let confirm = $('#confirmPassword').val();

            if (!password || !confirm) {
                alert('Please fill both password fields');
                return;
            }

            $.ajax({
                url: `/staff/${userId}/change-password`,
                method: 'POST',
                data: {
                    password: password,
                    password_confirmation: confirm,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    alert(res.message);

                    // Clear inputs
                    $('#newPassword').val('');
                    $('#confirmPassword').val('');
                },
                error: function (err) {
                    console.log(err);

                    if (err.responseJSON?.errors) {
                        alert(Object.values(err.responseJSON.errors)[0][0]);
                    } else {
                        alert('Failed to change password');
                    }
                }
            });
        });

    </script>




    <script>
        $(document).on('click', '.toggle-password', function () {
            const input = $(this).siblings('.password-field');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

    </script>

    <script>
        $('#avatarInput').on('change', function () {
            const file = this.files[0];
            if (!file) return;

            // Preview
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#avatarPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);

            // Upload automatically
            uploadAvatar(file);
        });
    </script>

    <script>
        function uploadAvatar(file) {
            let formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '/staff/{{ $staff->id ?? auth()->id() }}/avatar',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log('Avatar updated');
                    $('img[data-avatar]').each(function () {
                        $(this).attr('src', res.avatar_url); // Set the new avatar URL
                    });

                },
                error: function (err) {
                    alert('Failed to upload avatar');
                    console.error(err);
                }
            });
        }
    </script>


@endpush