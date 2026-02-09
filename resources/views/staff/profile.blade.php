@extends('layouts.app')

@section('title', 'Profile')


@section('content')
  <!-- Welcome & Stats Row -->



  <section class="section">
    <!-- Profile Cover & Card -->

    <div class="row mb-4">
      <div class="d-flex justify-content-end gap-2">

        
       
            <a href="{{ route('staff.edit', $staff) }}" class="btn btn-primary">
              <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
        

      
      </div>
    </div>



    <div class="profile-cover-card">
      <div class="profile-cover">
        <div class="profile-cover-overlay"></div>
        <div class="profile-cover-actions">
          <button class="btn btn-sm btn-light">
            <i class="bi bi-camera me-1"></i> Change Cover
          </button>
        </div>
      </div>
      <div class="profile-cover-content">
        <div class="profile-cover-avatar">
          <img src="assets/img/profile-img.webp" alt="John Doe">
          <span class="profile-online-badge"></span>
        </div>
        <div class="profile-cover-info">
          <h2>{{$staff->name}}</h2>
          <p class="text-muted mb-2">{{$staff->role}}</p>
          <div class="profile-cover-meta">
            <span><i class="bi bi-calendar3"></i>{{$staff->created_at}}</span>
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
            <p class="mb-3">{{$staff->bio}}</p>
            <div class="profile-about-list">

              @if ($staff->address)
                <div class="profile-about-item">
                  <i class="bi bi-house-door"></i>
                  <div>
                    <span class="label">Lives in</span>
                    <span class="value">New York, NY</span>
                  </div>
                </div>
              @endif


            </div>
          </div>
        </div>

        <!-- Skills Card -->
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">Group Affliation</h6>
          </div>
          <div class="card-body">
            <div class="profile-skills">
              <span class="profile-skill-tag">JavaScript</span>
              <span class="profile-skill-tag">TypeScript</span>
              <span class="profile-skill-tag">React</span>
              <span class="profile-skill-tag">Node.js</span>
              <span class="profile-skill-tag">Python</span>
              <span class="profile-skill-tag">PostgreSQL</span>
              <span class="profile-skill-tag">AWS</span>
              <span class="profile-skill-tag">Docker</span>
              <span class="profile-skill-tag">GraphQL</span>
              <span class="profile-skill-tag">REST APIs</span>
            </div>
          </div>
        </div>





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
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          success: function (res) {
            // Optionally remove the deleted staff from the DOM
            alert('Staff deleted successfully!');
            location.reload(); // or reload your table via AJAX
          },
          error: function (err) {
            console.error(err);
            alert('Error deleting staff.');
          }
        });
      });

    });
  </script>

@endpush