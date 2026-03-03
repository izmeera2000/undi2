<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Member;
use SweetAlert2\Laravel\Traits\WithSweetAlert;

new class extends Component {
    use WithFileUploads;
    use WithSweetAlert;


    public Member $member;
    public $avatar;

    // Form Properties (Mapped to your AJAX formData)
    public $nama;
    public $email;
    public $phone;
    public $alamat_1;
    public $alamat_2;
    public $alamat_3;
    public $poskod;
    public $bandar;
    public $negeri;

    public function mount(Member $member)
    {
        $this->member = $member;
        $this->nama = $member->nama;
        $this->email = $member->email;
        $this->phone = $member->phone;
        $this->alamat_1 = $member->alamat_1;
        $this->alamat_2 = $member->alamat_2;
        $this->alamat_3 = $member->alamat_3;
        $this->poskod = $member->poskod;
        $this->bandar = $member->bandar;
        $this->negeri = $member->negeri;
    }

    // Replaces your uploadAvatar jQuery function
    public function updatedAvatar()
    {
        $this->validate(['avatar' => 'image|max:1024']);

        $path = $this->avatar->store('avatars', 'public');
        $this->member->update(['profile_picture' => $path]);

        $this->js("toastr.success('Avatar updated successfully!')");
    }

    // Replaces your #saveProfileBtn jQuery AJAX call
    public function save()
    {
        try {
            $validated = $this->validate([
                'nama' => 'required|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable',
                'alamat_1' => 'nullable',
                'alamat_2' => 'nullable',
                'alamat_3' => 'nullable',
                'poskod' => 'nullable',
                'bandar' => 'nullable',
                'negeri' => 'nullable',
            ]);

            $this->member->update($validated);

            // This replaces your toastr.info/success in the AJAX callback
            $this->js("toastr.info('Profile updated successfully!')");
        } catch (\Exception $e) {
            $this->js("toastr.error('Failed to update profile. Check your input.')");
        }
    }

    public function confirmDelete()
    {
        $this->swalFire([
            'title' => 'Are you sure?',
            'text' => "Deleting {$this->nama} cannot be undone.",
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => 'Yes, delete it!',
            // This is the key: it tells the JS to trigger this Livewire method on confirm
            'onConfirmed' => 'deleteAccount',
        ]);
    }

    public function deleteAccount()
    {
        if (auth()->user()->isAdmin()) {
            $this->member->delete();
            return redirect()->route('members.list');
        }
    }
}; ?>

<div>
    {{--
    <pre>@json($this->all())</pre> --}}
    <section class="section">
        <div class="row">
            <div class="col-lg-3">
                <div class="card edit-nav-card">
                    <div class="card-body p-0">
                        <nav class="edit-nav">
                            <a href="#section-avatar" class="edit-nav-item active"><i class="bi bi-person-circle"></i>
                                <span>Avatar & Status</span></a>
                            <a href="#section-personal" class="edit-nav-item"><i class="bi bi-person"></i>
                                <span>Personal Info</span></a>
                            <a href="#section-contact" class="edit-nav-item"><i class="bi bi-envelope"></i>
                                <span>Contact Details</span></a>
                            @can ('members.delete')
                                <a href="#section-danger" class="edit-nav-item text-danger"><i
                                        class="bi bi-exclamation-triangle"></i> <span>Danger Zone</span></a>
                            @endcan
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <form wire:submit="save">
                    <div class="card" id="section-avatar">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i>Avatar & Account Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar-upload-wrapper">
                                        <div class="avatar-upload-preview">
                                            @if ($avatar)
                                                <img src="{{ $avatar->temporaryUrl() }}">
                                            @else
                                                <img src="{{ $member->getProfilePictureUrlAttribute() }}"
                                                    id="avatarPreview">
                                            @endif
                                        </div>
                                        <label
                                            class="avatar-upload-btn d-flex align-items-center justify-content-center"
                                            for="avatarInput">
                                            <i class="bi bi-camera" wire:loading.remove wire:target="avatar"></i>

                                            <div wire:loading wire:target="avatar">
                                                <span class="spinner-border spinner-border-sm text-light"
                                                    role="status"></span>
                                            </div>
                                        </label>
                                        <input type="file" wire:model="avatar" id="avatarInput" class="d-none"
                                            accept="image/*">
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">{{ $nama }}</h5>
                                    <p class="text-muted mb-2">{{ $member->no_ahli }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="section-personal">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nama" class="form-control" required>
                                    @error('nama') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="section-contact">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-envelope me-2"></i>Contact Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" wire:model="email" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" wire:model="phone" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 1</label>
                                    <input type="text" wire:model="alamat_1" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" wire:model="alamat_2" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postcode</label>
                                    <input type="text" wire:model="poskod" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" wire:model="bandar" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" wire:model="negeri" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-bar mb-4">
                        <div class="form-actions-buttons">
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <span wire:loading.remove wire:target="save"><i class="bi bi-check-lg me-1"></i> Save
                                    Changes</span>
                                <span wire:loading wire:target="save"><span
                                        class="spinner-border spinner-border-sm"></span> Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>

                @can ('members.delete')
                    <div class="card border-danger" id="section-danger">
                        <div class="card-header bg-danger-light">
                            <h5 class="card-title mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger
                                Zone</h5>
                        </div>
                        <div class="card-body">
                            <div class="danger-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="danger-action-title fw-bold">Delete Account</div>
                                    <div class="danger-action-desc text-muted small">Permanently delete this user. This
                                        action cannot be undone.</div>
                                </div>
                                <button type="button" wire:click="confirmDelete" class="btn btn-danger btn-sm px-4">
                                    <i class="bi bi-trash3 me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('livewire:navigated', () => {
            $(document).on('click', '.toggle-password', function () {
                const input = $(this).siblings('.password-field');
                const icon = $(this).find('i');
                const isPassword = input.attr('type') === 'password';

                input.attr('type', isPassword ? 'text' : 'password');
                icon.toggleClass('bi-eye bi-eye-slash');
            });
        });
    </script>
</div>