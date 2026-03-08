@extends('layouts.app')

@section('title', 'Manage Group')

@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Members', 'url' => route('members.list')];
        $crumbs[] = ['label' => 'Groups', 'url' => route('members.groups.index')];
        $crumbs[] = ['label' => $group->name, 'url' => route('members.groups.manage', $group)];
    @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">
@endpush

@section('content')
    <section class="section">
        <div class="row g-4">
            {{-- LEFT SIDE - GROUP DETAILS --}}
            <div class="col-lg-5 col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-layer-group me-2"></i> Group Details</h4>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('members.groups.update', $group) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold"><i class="fas fa-tag me-1"></i> Group Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $group->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold"><i class="fas fa-align-left me-1"></i>
                                    Description</label>
                                <textarea name="description" rows="4"
                                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $group->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Update Group</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDE - MEMBERS --}}
            <div class="col-lg-7 col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-users me-2"></i> Members</h4>
                    </div>
                    <div class="card-body p-4">
                        {{-- Add Member --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2"><i class="fas fa-user-plus me-1"></i> Add
                                Member</label>
                            <form action="{{ route('members.groups.invite', $group) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="text" name="no_ahli" class="form-control" placeholder="No Ahli" required>
                                <button class="btn btn-primary px-4">Add</button>
                            </form>
                        </div>

                        {{-- Members Table --}}
                        <table class="table table-striped" id="membersTable">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                     <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                        </table>

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

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>



    <script>
        $(document).ready(function () {
            $('#membersTable').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ route("members.groups.membersList", $group) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    error: function (xhr) {
                        if (xhr.status === 401) window.location.href = "{{ route('login') }}";
                        if (xhr.status === 419) location.reload();
                    }
                }, columns: [
                    { data: 'members', name: 'members' },
                     { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50]
            });
        });
    </script>
@endpush