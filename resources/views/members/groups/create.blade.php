@extends('layouts.app')

@section('title', 'Create Group')

@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Members', 'url' => route('members.list')];
    $crumbs[] = ['label' => 'Groups', 'url' => route('members.groups.index')];
    $crumbs[] = ['label' => 'Create', 'url' => route('members.groups.create')];
  @endphp
@endsection

@section('content')
<section class="section">

    <div class="row">

            <div class="card  g-4 mb-4">
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        Create New Group
                    </h4>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('members.groups.store') }}" method="POST">
                        @csrf

                        {{-- Group Name --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1"></i> Group Name
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                placeholder="Enter group name"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1 "></i> Description
                            </label>
                            <textarea 
                                name="description" 
                                rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Optional description..."
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('members.groups.index') }}" 
                               class="btn btn-light border px-4">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>

                            <button class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Group
                            </button>
                        </div>

                    </form>

                </div>
            </div>

    </div>

</section>
@endsection