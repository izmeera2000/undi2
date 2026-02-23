@extends('layouts.app')

@section('title', 'Edit Profile')



@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];
        $crumbs[] = ['label' => 'Profile', 'url' => route('members.show', $member)];
        $crumbs[] = ['label' => 'Edit', 'url' => route('members.edit', $member)];
     @endphp
@endsection

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

                                </a>
                                @if (auth()->user()->isAdmin() )
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
                                            <img src="{{$member->getProfilePictureUrlAttribute()}}" alt="John Doe"
                                                id="avatarPreview">

                                        </div>

                                        <label class="avatar-upload-btn" for="avatarInput">
                                            <i class="bi bi-camera"></i>
                                        </label>


                                        <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*">
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">{{ $member->nama }}</h5>
                                    <p class="text-muted mb-2">{{ $member->no_ahli }}</p>
                                    <div class="d-flex flex-wrap gap-2">
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
                                    <input type="text" name="name" class="form-control" value="{{ $member->nama }}" required>
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
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <input type="email" name="email" class="form-control" value="{{ $member->email }}">

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" value="{{ $member->phone }}">
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Address Line 1</label>
                                        <input type="text" name="alamat_1" class="form-control"
                                            value="{{ $member->alamat_1 ?? '' }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Address Line 2</label>
                                        <input type="text" name="alamat_2" class="form-control"
                                            value="{{ $member->alamat_2 ?? '' }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Address Line 3</label>
                                        <input type="text" name="alamat_3" class="form-control"
                                            value="{{ $member->alamat_3 ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Postcode</label>
                                        <input type="text" name="poskod" class="form-control"
                                            value="{{ $member->poskod ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="bandar" class="form-control"
                                            value="{{ $member->bandar ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">State</label>
                                        <input type="text" name="negeri" class="form-control"
                                            value="{{ $member->negeri ?? '' }}">
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
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Access & Security Section -->

       

                    @if (auth()->user()->isAdmin() )
                        <!-- Danger Zone Section -->
                        <div class="card border-danger" id="dangerZone" data-user-id="{{ $member->id }}">
                            <div class="card-header bg-danger-light">
                                <h5 class="card-title mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger
                                    Zone</h5>
                            </div>
                            <div class="card-body">
                                <div class="danger-actions">
 


                       

                     
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


    

    @include('members.partials.edit.deleteuser')

    @include('members.partials.edit.saveprofile')


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

            const reader = new FileReader();
            reader.onload = function (e) {
                $('#avatarPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);

            uploadAvatar(file);
        });

        function uploadAvatar(file) {
            let formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '/members/{{ $member->id }}/avatar',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log(res);
                    toast.success('Avatar updated');
                    $('img[data-avatar]').each(function () {
                        $(this).attr('src', res.avatar_url);
                    });
                },
                error: function (err) {
                    toast.error('Failed to upload avatar');
                    console.error(err);
                }
            });
        }
    </script>



@endpush