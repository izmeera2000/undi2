@extends('layouts.app')

@section('title', 'Profile')




@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];
        $crumbs[] = ['label' => 'Profile', 'url' => route('members.show', $member)];
     @endphp
@endsection


@section('content')
  <!-- Welcome & Stats Row -->



  <section class="section">
    <!-- Profile Cover & Card -->

    <div class="row mb-4">
      <div class="d-flex justify-content-end gap-2">



        <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">
          <i class="bi bi-pencil me-1"></i> Edit Profile
        </a>



      </div>
    </div>



    <div class="profile-cover-card">
      <div class="profile-cover">
        <div class="profile-cover-overlay"></div>

      </div>
      <div class="profile-cover-content">
        <div class="profile-cover-avatar">
          <img src="{{$member->getProfilePictureUrlAttribute()}}" alt="John Doe">
        </div>
        <div class="profile-cover-info">
          <h2>{{$member->nama}}</h2>


          <p class="profile-header-title">{{$member->no_ahli}}</p>
          <div class="profile-cover-meta">
            <span><i class="bi bi-calendar3"></i>Joined {{ $member->created_at->format('d M Y') }}</span>
          </div>
        </div>

      </div>
    </div>

    <div class="row">
      <!-- Left Sidebar -->
      <div class="col-md-6">
        <!-- About Card -->
        <div class="card h-100">
          <div class="card-header">
            <h6 class="card-title mb-0">Contact Information</h6>
          </div>
          <div class="card-body">




            <div class="profile-about-list">
              @if($member->email)

                <div class="info-list-item">
                  <div class="info-list-icon"><i class="bi bi-envelope"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Email</div>
                    <div class="info-list-value">{{ $member->email }}</div>
                  </div>
                </div>
              @endif

              @if($member->phone)

                <div class="info-list-item">
                  <div class="info-list-icon"><i class="bi bi-phone"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Phone</div>
                    <div class="info-list-value">{{ $member->phone }}</div>
                  </div>
                </div>

              @endif


              @if($member->alamat_1 || $member->alamat_2 || $member->alamat_3 )

                <div class="info-list-item">
                  <div class="info-list-icon"><i class="bi bi-geo-alt"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Address</div>
                    <div class="info-list-value">
                      <div class="ms-2">
                        @foreach([$member->alamat_1, $member->alamat_2, $member->alamat_3 ] as $alamat)
                          @if($alamat)
                            <div>{{ $alamat }}</div>
                          @endif
                        @endforeach
                      </div>


                    </div>
                  </div>
                </div>
              @endif





            </div>
          </div>
        </div>

        <!-- Skills Card -->


      </div>

      <div class="col-md-6">
        <div class="card mb-4 h-100">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-person-lines-fill me-2"></i>Maklumat Ahli</h5>
          </div>
          <div class="card-body">
            <div class="info-list">

              <!-- Member Number -->
              <div class="info-list-item mb-3">
                <div class="info-list-icon text-primary"><i class="bi bi-card-text fs-5"></i></div>
                <div class="info-list-content">
                  <div class="info-list-label">No Ahli</div>
                  <div class="info-list-value">{{ $member->no_ahli }}</div>
                </div>
              </div>

              <!-- Branch -->
              <div class="info-list-item mb-3">
                <div class="info-list-icon text-success"><i class="bi bi-building fs-5"></i></div>
                <div class="info-list-content">
                  <div class="info-list-label">Cawangan</div>
                  <div class="info-list-value">
                    {{ $member->nama_cwgn }}<br>
                    <small class="text-muted">{{ $member->kod_cwgn }}</small>
                  </div>
                </div>
              </div>

              <!-- Start Date -->
              <div class="info-list-item mb-3">
                <div class="info-list-icon text-warning"><i class="bi bi-calendar-check fs-5"></i></div>
                <div class="info-list-content">
                  <div class="info-list-label">Start Date</div>
                  <div class="info-list-value">{{ $member->created_at->format('d M Y') }}</div>
                </div>
              </div>

              <!-- Alamat JPN -->
              @if($member->alamat_jpn_1 || $member->alamat_jpn_2 || $member->alamat_jpn_3)
                <div class="info-list-item mb-3">
                  <div class="info-list-icon text-danger"><i class="bi bi-geo-alt fs-5"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Alamat JPN</div>
                    <div class="info-list-value ms-2">
                      @foreach([$member->alamat_jpn_1, $member->alamat_jpn_2, $member->alamat_jpn_3] as $alamatjpn)
                        @if($alamatjpn)
                          <div>{{ $alamatjpn }}</div>
                        @endif
                      @endforeach
                    </div>
                  </div>
                </div>
              @endif

            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i>Personal Info</h5>
          </div>
          <div class="card-body">
            <div class="info-list">

              <!-- Gender -->
              @if ($member->jantina)
                <div class="info-list-item mb-3">
                  <div class="info-list-icon text-info"><i class="bi bi-gender-ambiguous fs-5"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Jantina</div>
                    <div class="info-list-value">
                      @if($member->jantina === 'P')
                        Perempuan
                      @elseif($member->jantina === 'L')
                        Lelaki
                      @else
                        Tidak Diketahui
                      @endif
                    </div>
                  </div>
                </div>
              @endif

              <!-- Age -->
              @if ($member->tahun_lahir)
                <div class="info-list-item mb-3">
                  <div class="info-list-icon text-secondary"><i class="bi bi-hourglass-split fs-5"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">Umur</div>
                    <div class="info-list-value">{{ now()->year - $member->tahun_lahir }}</div>
                  </div>
                </div>
              @endif

              <!-- No KP -->
              @if ($member->nokp_baru)
                <div class="info-list-item mb-3">
                  <div class="info-list-icon text-dark"><i class="bi bi-card-heading fs-5"></i></div>
                  <div class="info-list-content">
                    <div class="info-list-label">No KP</div>
                    <div class="info-list-value">{{ $member->nokp_baru }}</div>
                  </div>
                </div>
              @endif

            </div>
          </div>
        </div>
      </div>





      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i>Pengundi</h5>
          </div>
          <div class="card-body">


<div class="activity-timeline">

  @foreach($member->pengundiGroupedByElection() as $electionKey => $pengundiCollection)
    @php
        // Split the key into type and series for display
        [$type, $series] = explode('_', $electionKey);
    @endphp

    @forelse($pengundiCollection as $pengundi)
      <div class="activity-timeline-item">
        <div class="activity-timeline-marker success"></div>
        <div class="activity-timeline-content">
          <div class="activity-timeline-header">
            <span class="activity-timeline-title">Registered Voter </span>
            <span class="activity-timeline-time">({{ $type }} #{{ $series }})</span>
          </div>
          <p class="activity-timeline-desc">
            Age at election: {{ ($pengundi->tarikh_undian ?? now()->year) - $member->tahun_lahir }}
            <br>
            @if($pengundi->status_umno)
              Undi UMNO
            @else
              Undi Lain
            @endif
            <br>
            {{ optional($pengundi->lokaliti)->nama_lokaliti ?? 'Unknown Lokaliti' }}
          </p>
        </div>
      </div>
    @empty
      <div class="activity-timeline-item">
        <div class="activity-timeline-marker secondary"></div>
        <div class="activity-timeline-content">
          <div class="activity-timeline-header">
            <span class="activity-timeline-title">Not Registered</span>
            <span class="activity-timeline-time">{{ $type }} #{{ $series }}</span>
          </div>
        </div>
      </div>
    @endforelse
  @endforeach

</div>


          </div>
        </div>
      </div>


    </div>


    </div>
  </section>


@endsection

@push('scripts')

  {{--
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
  </script> --}}

@endpush