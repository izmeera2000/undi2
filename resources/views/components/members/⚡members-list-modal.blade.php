<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Member;
use App\Models\Dun;
use App\Models\Dm;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';

    // Form Properties mapping exactly to your HTML input names
    public $profile_picture, $nama, $jantina, $nokp_baru, $nokp_lama;
    public $alamat_1, $alamat_2, $alamat_3, $poskod, $bandar, $negeri;
    public $bangsa, $alamat_jpn_1, $alamat_jpn_2, $alamat_jpn_3;
    public $kod_dun, $kod_dm, $kod_cwgn, $nama_cwgn, $no_ahli;

    // Dropdown Data
    public $dms = [];

    protected $paginationTheme = 'bootstrap';

    /**
     * Dependent Dropdown: Triggers when kod_dun changes
     */
    public function updatedKodDun($value)
    {
        $this->dms = Dm::where('kod_dun', $value)->get();
        $this->kod_dm = null;
    }

    public function saveMember()
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'nokp_baru' => 'required|unique:members,nokp_baru',
            'profile_picture' => 'nullable|image|max:1024',
            'kod_dun' => 'required',
            'kod_dm' => 'required',
        ]);

        $path = $this->profile_picture ? $this->profile_picture->store('avatars', 'public') : null;

        Member::create([
            'nama' => $this->nama,
            'jantina' => $this->jantina,
            'nokp_baru' => $this->nokp_baru,
            'nokp_lama' => $this->nokp_lama,
            'alamat_1' => $this->alamat_1,
            'alamat_2' => $this->alamat_2,
            'alamat_3' => $this->alamat_3,
            'poskod' => $this->poskod,
            'bandar' => $this->bandar,
            'negeri' => $this->negeri,
            'bangsa' => $this->bangsa,
            'alamat_jpn_1' => $this->alamat_jpn_1,
            'alamat_jpn_2' => $this->alamat_jpn_2,
            'alamat_jpn_3' => $this->alamat_jpn_3,
            'kod_dun' => $this->kod_dun,
            'kod_dm' => $this->kod_dm,
            'kod_cwgn' => $this->kod_cwgn,
            'nama_cwgn' => $this->nama_cwgn,
            'no_ahli' => $this->no_ahli,
            'profile_picture' => $path,
        ]);

        $this->reset();
        $this->dispatch('close-modal');
        session()->flash('success', 'Member added successfully!');
    }

    public function with()
    {
        return [
            'members' => Member::where('nama', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
            'duns' => Dun::all(),
        ];
    }
}; ?>

<div>
    <!-- Table Header with Search -->
    <div class="row g-3 mb-3 align-items-center">
        <div class="col-md-4">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search members...">
        </div>
        <div class="col-md-8 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-plus-lg me-1"></i> Add Member
            </button>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div wire:ignore.self class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Add New Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit="saveMember">
                    <div class="modal-body">
                        <!-- Avatar Section -->
                        <div class="text-center mb-4">
                            <div class="avatar-upload mx-auto">
                                <div class="avatar-preview mb-2">
                                    <img src="{{ $profile_picture ? $profile_picture->temporaryUrl() : asset('assets/img/avatars/avatar-placeholder.webp') }}" 
                                         class="rounded-circle border" width="100" height="100" style="object-fit: cover;">
                                </div>
                                <label class="btn btn-sm btn-light border shadow-sm">
                                    <i class="bi bi-camera me-1"></i> Upload
                                    <input type="file" wire:model="profile_picture" accept="image/*" class="d-none">
                                </label>
                                <div wire:loading wire:target="profile_picture" class="text-primary small mt-1">Uploading...</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" wire:model="nama" class="form-control" placeholder="Enter name">
                                @error('nama') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Gender</label>
                                <select wire:model="jantina" class="form-select">
                                    <option value="">Select...</option>
                                    <option value="L">Male</option>
                                    <option value="P">Female</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">NO KP Baru</label>
                                <input type="text" wire:model="nokp_baru" class="form-control" placeholder="000000000000">
                                @error('nokp_baru') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">NO KP Lama</label>
                                <input type="text" wire:model="nokp_lama" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small text-uppercase">Current Address</label>
                                <input type="text" wire:model="alamat_1" class="form-control mb-2" placeholder="Line 1">
                                <input type="text" wire:model="alamat_2" class="form-control mb-2" placeholder="Line 2">
                                <input type="text" wire:model="alamat_3" class="form-control" placeholder="Line 3">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Poskod</label>
                                <input type="text" wire:model="poskod" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bandar</label>
                                <input type="text" wire:model="bandar" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Negeri</label>
                                <input type="text" wire:model="negeri" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bangsa</label>
                                <input type="text" wire:model="bangsa" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Location Data</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <select wire:model.live="kod_dun" class="form-select">
                                            <option value="">-- DUN --</option>
                                            @foreach($duns as $dun)
                                                <option value="{{ $dun->kod_dun }}">{{ $dun->kod_dun }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select wire:model="kod_dm" class="form-select" @disabled(!$kod_dun)>
                                            <option value="">-- DM --</option>
                                            @foreach($dms as $dm)
                                                <option value="{{ $dm->kod_dm }}">{{ $dm->kod_dm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kod CWGN</label>
                                <input type="text" wire:model="kod_cwgn" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nama CWGN</label>
                                <input type="text" wire:model="nama_cwgn" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">No Ahli</label>
                                <input type="text" wire:model="no_ahli" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveMember">Add Member</span>
                            <span wire:loading wire:target="saveMember" class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('close-modal', () => {
            bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
        });
    </script>
    @endscript
</div>
