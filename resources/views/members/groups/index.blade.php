{{-- resources/views/members/groups/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Groups</h1>

    {{-- Create Group Button --}}
    <a href="{{ route('members.groups.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Create Group
    </a>

    @if($groups->count())
        <div class="accordion" id="groupsAccordion">
            @foreach($groups as $group)
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center" id="heading{{ $group->id }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $group->id }}" aria-expanded="true" aria-controls="collapse{{ $group->id }}">
                                {{ $group->name }}
                            </button>
                        </h5>

                        <div>
                            <a href="{{ route('members.groups.edit', $group) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <form action="{{ route('members.groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this group?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="collapse{{ $group->id }}" class="collapse" aria-labelledby="heading{{ $group->id }}" data-bs-parent="#groupsAccordion">
                        <div class="card-body">
                            <h6>Members</h6>
                            @if($group->members->count())
                                <ul class="list-group mb-2">
                                    @foreach($group->members as $member)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $member->name ?? $member->email ?? 'Unnamed Member' }}
                                            <form action="{{ route('members.groups.removeMember', [$group, $member]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this member?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>No members yet.</p>
                            @endif

                            {{-- Invite Member Form --}}
                            <form action="{{ route('members.groups.invite', $group) }}" method="POST" class="d-flex mt-3">
                                @csrf
                                <input type="email" name="email" class="form-control me-2" placeholder="Member Email" required>
                                <button class="btn btn-success" type="submit">Invite</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p>No groups available.</p>
    @endif
</div>
@endsection