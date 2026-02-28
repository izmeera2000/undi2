@extends('layouts.app')

@section('title', 'Profile')



@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Staff', 'url' => route('staff.list')];
    $crumbs[] = ['label' => 'Profile', 'url' => route('staff.show', $staff)];
   @endphp
@endsection



@section('content')
  <!-- Welcome & Stats Row -->



  <section class="section">
    <!-- Profile Cover & Card -->

    <div class="row mb-4">
      <div class="d-flex justify-content-end gap-2">


        @if (auth()->user()->id === $staff->id || auth()->user()->role !== 'user')
          <a href="{{ route('staff.edit', $staff) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Profile
          </a>
        @endif




      </div>
    </div>



    <div class="profile-cover-card">
      <div class="profile-cover">
        <div class="profile-cover-overlay"></div>

      </div>
      <div class="profile-cover-content">
        <div class="profile-cover-avatar">
          <img src="{{$staff->profile->getProfilePictureUrlAttribute()}}" alt="John Doe">
          <span class="profile-online-badge"></span>
        </div>
        <div class="profile-cover-info">
          <h2>{{$staff->name}}</h2>
          <p class="text-muted mb-2">{{ucfirst($staff->role)}}</p>
          <div class="profile-cover-meta">
            <span><i class="bi bi-calendar3"></i>{{ $staff->created_at->format('d M Y') }}</span>
          </div>
        </div>

      </div>
    </div>

    <div class="row">
      <!-- Left Sidebar -->
      <div class="col ">
        <!-- About Card -->
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">About</h6>
          </div>
          <div class="card-body">
            <p class="mb-3">{{$staff->profile->bio}}</p>
            <div class="profile-about-list">

              @if ($staff->profile->address)
                <div class="profile-about-item">
                  <i class="bi bi-house-door"></i>
                  <div>
                    <span class="label">Lives in</span>
                    <span class="value">{{$staff->profile->address}}</span>
                  </div>
                </div>
              @endif


            </div>
          </div>
        </div>

        <!-- Skills Card -->

        @if ($staff->members_id)

          <div class="card">
            <div class="card-header">
              <h6 class="card-title mb-0">Group Affiliation</h6>
            </div>
            <div class="card-body">
              @if ($staff->groups->count())
                <div class="profile-skills">
                  @foreach ($staff->groups as $group)
                    <span class="profile-skill-tag">
                      {{ $group->name }}
                    </span>
                  @endforeach
                </div>
              @else
                <p class="text-muted mb-0">No group assigned</p>
              @endif
            </div>
          </div>

        @endif




      </div>


    </div>
  </section>


@endsection

@push('scripts')

  <script>
    $(document).ready(function () {

      $('.delete-staff-btn').on('click', function () {
        let staffId = $(this).data('id');

        if (!confirm('Are you sure you want to delete this staff?')) return;

        $.ajax({
          url: `/staff/${staffId}`, // matches Route::resource('staff')
          type: 'DELETE',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          success: function (res) {
            // Optionally remove the deleted staff from the DOM
            toast.success('Staff deleted successfully!');

            location.reload(); // or reload your table via AJAX
          },
          error: function (err) {
            console.error(err);
            toast.error('Error deleting staff.');

          }
        });
      });

    });
  </script>

@endpush