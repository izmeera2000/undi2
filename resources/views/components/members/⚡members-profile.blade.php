<?php

use App\Models\Member;
use Livewire\Component;
use SweetAlert2\Laravel\Traits\WithSweetAlert;


new class extends Component {
    public Member $member;
    use WithSweetAlert;


    public function mount(Member $member)
    {
        $this->member = $member;
    }

    public function deleteMember()
    {
        $this->member->delete();
        return $this->redirect(route('members.list'), navigate: true);
    }
}; ?>

<div>


    <div class="row mb-4">
        <div class="d-flex justify-content-end gap-2">
            @if(auth()->user()->can('members.edit') || auth()->user()->member_id === $this->member->id)
                <a href="{{ route('members.edit', $this->member->id) }}" class="btn btn-primary" wire:navigate>
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
            @endif

            @can('members.delete')

                <button wire:click="deleteMember" wire:confirm="Confirm delete member?" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>

            @endcan


        </div>
    </div>

    <div class="profile-cover-card">
        <div class="profile-cover">
            <div class="profile-cover-overlay"></div>
        </div>
        <div class="profile-cover-content">
            <div class="profile-cover-avatar">
                <img src="{{ $member->getProfilePictureUrlAttribute() }}" alt="Avatar">
            </div>
            <div class="profile-cover-info">
                <h2>{{ $member->nama }}</h2>
                <p class="profile-header-title">{{ $member->no_ahli }}</p>
                <div class="profile-cover-meta">
                    <span><i class="bi bi-calendar3"></i>Joined {{ $member->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
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

                        @if($member->alamat_1 || $member->alamat_2 || $member->alamat_3)
                            <div class="info-list-item">
                                <div class="info-list-icon"><i class="bi bi-geo-alt"></i></div>
                                <div class="info-list-content">
                                    <div class="info-list-label">Address</div>
                                    <div class="info-list-value">
                                        @foreach([$member->alamat_1, $member->alamat_2, $member->alamat_3] as $alamat)
                                            @if($alamat)
                                                <div>{{ $alamat }}</div>
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
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Maklumat Ahli</h5>
                </div>
                <div class="card-body">
                    @if ($member->no_ahli)

                        <div class="info-list-item mb-3">
                            <div class="info-list-icon text-primary"><i class="bi bi-card-text fs-5"></i></div>
                            <div class="info-list-content">
                                <div class="info-list-label">No Ahli</div>
                                <div class="info-list-value">{{ $member->no_ahli }}</div>
                            </div>
                        </div>

                    @endif

                    @if ($member->nama_cwgn)

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
                    @endif
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
                                <div class="info-list-icon text-secondary">
                                    <i class="bi bi-hourglass-split fs-5"></i>
                                </div>
                                <div class="info-list-content">
                                    <div class="info-list-label">Umur</div>
                                    <div class="info-list-value">
                                        {{ now()->year - $member->tahun_lahir }}
                                    </div>
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
                        @forelse($member->pengundiGroupedByElection() as $electionKey => $pengundiCollection)

                            @php
                                [$type, $series] = explode('_', $electionKey);
                            @endphp

                            @forelse($pengundiCollection as $pengundi)
                                <div class="activity-timeline-item">
                                    <div class="activity-timeline-marker success"></div>
                                    <div class="activity-timeline-content">
                                        <div class="activity-timeline-header">
                                            <span class="activity-timeline-title">Registered Voter</span>
                                            <span class="activity-timeline-time">
                                                ({{ $type }} #{{ $series }})
                                            </span>
                                        </div>
                                        <p class="activity-timeline-desc">
                                            Age at election:
                                            {{ ($pengundi->tarikh_undian ?? now()->year) - $member->tahun_lahir }}
                                            <br>
                                            {{ $pengundi->status_umno ? 'Undi UMNO' : 'Undi Lain' }}
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
                                            <span class="activity-timeline-time">
                                                {{ $type }} #{{ $series }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforelse

                        @empty
                            <div class="activity-timeline-item">
                                <div class="activity-timeline-marker secondary"></div>
                                <div class="activity-timeline-content">
                                    <div class="activity-timeline-header">
                                        <span class="activity-timeline-title text-muted">
                                            No election records found
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>


                </div>
            </div>
        </div>

        @if ($member->groups)

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Groups</h5>
                    </div>
                    <div class="card-body">
                        @forelse($member->groups as $group)
                            <span class="badge bg-primary me-2 mb-2">{{ $group->name }}</span>
                        @empty
                            <span class="text-muted">No groups assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif




    </div>
</div>