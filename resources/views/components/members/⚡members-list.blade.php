<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Member;
use App\Models\Group;
use App\Models\Dun;
use App\Models\Dm;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $name, $email, $avatar, $selectedGroups = [];
    public $dun_id, $kod_dm;
    public $dms = [];

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:members,email',
        'avatar' => 'nullable|image|max:1024',
        'selectedGroups' => 'required|array',
    ];

    // Standard hook for PHP compatibility < 8.4
    public function updatedDunId($value)
    {
        $this->dms = Dm::where('kod_dun', $value)->get();
        $this->kod_dm = null;
    }

    public function saveMember()
    {
        $this->validate();

        $avatarPath = $this->avatar ? $this->avatar->store('avatars', 'public') : null;

        $member = Member::create([
            'nama' => $this->name, // Ensure column name matches your DB
            'email' => $this->email,
            'avatar' => $avatarPath,
            'kod_dm' => $this->kod_dm,
        ]);

        if ($this->selectedGroups) {
            $member->groups()->sync($this->selectedGroups);
        }

        $this->reset(['name', 'email', 'avatar', 'selectedGroups', 'dun_id', 'kod_dm', 'dms']);

        $this->dispatch('close-modal');
        session()->flash('success', 'Member added successfully!');
    }

    public function deleteMember($id)
    {
        Member::findOrFail($id)->delete();
        session()->flash('success', 'Member deleted successfully!');
    }

    public function with()
    {
        return [
            'members' => Member::with('groups')
                ->where('nama', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
            'groups' => Group::all(),
            'duns' => Dun::all(),
        ];
    }
};
?>

<div>
    <!-- Search & Add Member -->
    <div class="row g-3 mb-3 align-items-center">
        <div class="col-md-4">
            {{-- In V4, wire:model is deferred by default. Use .live for instant search --}}


            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                    placeholder="Search members...">
            </div>
        </div>

        <div class="col-md-8 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-plus-lg me-1"></i> Add Member
            </button>
        </div>
    </div>

    <!-- Members Table -->
    <div class="card shadow-sm">
        <div class="card-body p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Groups</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                        <tr wire:key="{{ $member->id }}">
                            <td>


                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $member->profile_picture ? Storage::url($member->profile_picture) : asset('assets/img/avatars/avatar-placeholder.webp') }}"
                                        alt="avatar" class="rounded-circle me-2 border object-fit-cover"
                                        style="width: 40px; aspect-ratio: 1/1;">

                                    <div>
                                        <a href="http://undi2/members/{{ $member->id }}" class="fw-semibold">
                                            {{ $member->nama }}
                                        </a>
                                        <div class="text-muted small">{{ $member->no_ahli }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($member->groups->isNotEmpty())
                                    @foreach($member->groups as $group)
                                        <span class="badge bg-primary text-white border me-1">
                                            {{ $group->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No groups</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                {{-- wire:confirm is built into Livewire 4 --}}
                                {{-- <button wire:click="deleteMember({{ $member->id }})"
                                    wire:confirm="Are you sure you want to delete this member?"
                                    class="btn btn-link text-danger btn-sm">Delete</button> --}}


                                <div class="btn-group">
                                    <a href="http://undi2/members/{{ $member->id }}" class="btn btn-sm btn-light"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3">
                {{ $members->links() }}
            </div>
        </div>
    </div>



 
</div>