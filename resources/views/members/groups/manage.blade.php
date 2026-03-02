@extends('layouts.app')

@section('title', 'Manage Group')

@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Members', 'url' => route('members.list')];
        $crumbs[] = ['label' => 'Groups', 'url' => route('members.groups.index')];
        $crumbs[] = ['label' => $group->name, 'url' => route('members.groups.manage', $group)];
      @endphp
@endsection

@section('content')
    <section class="section">

        <div class="row g-4">

            {{-- LEFT SIDE - GROUP DETAILS --}}
            <div class="col-lg-5">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>
                            Group Details
                        </h4>
                    </div>

                    <div class="card-body p-4">

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('members.groups.update', $group) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-tag me-1"></i> Group Name
                                </label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $group->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-align-left me-1"></i> Description
                                </label>
                                <textarea name="description" rows="4"
                                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $group->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Update Group
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

            {{-- RIGHT SIDE - MEMBERS --}}
            <div class="col-lg-7">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Members
                        </h4>
                    </div>

                    <div class="card-body p-4">

                        {{-- Add Member --}}
                        <div class=" mb-4">
                            <label class="form-label fw-semibold mb-2">
                                <i class="fas fa-user-plus me-1"></i> Add Member
                            </label>

                            <form action="{{ route('members.groups.invite', $group) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="email" name="email" class="form-control" required>
                                <button class="btn btn-primary px-4">
                                    Add
                                </button>
                            </form>
                        </div>

                        {{-- Members List --}}
                        <ul class="list-group list-group-flush  border-top pt-3">
                            @forelse($group->members as $member)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">

                                    <div>
                                        <strong>{{ $member->name ?? 'No Name' }}</strong>
                                        <div class="text-muted small">
                                            {{ $member->email }}
                                        </div>
                                    </div>

                                    <form action="{{ route('members.groups.removeMember', [$group, $member]) }}" method="POST"
                                        onsubmit="return confirm('Remove this member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-user-minus me-1"></i> Remove
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-user-slash me-2"></i>
                                    No members yet
                                </li>
                            @endforelse
                        </ul>



                    </div>
                </div>

            </div>

        </div>

        <div class="mt-4">
            <a href="{{ route('members.groups.index') }}" class="btn btn-light border px-4">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

    </section>
@endsection