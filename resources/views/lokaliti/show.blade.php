@extends('layouts.app')

@section('title', 'Lokaliti Details')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Lokaliti', 'url' => route('lokaliti.index')],
            ['label' => 'Details', 'url' => route('lokaliti.show', $lokaliti->id)],
        ];
    @endphp
@endsection

@section('content')
    <section class="section">
        <div class="card g-4 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Lokaliti Details</h5>
                <a href="{{ route('lokaliti.edit', $lokaliti->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            </div>

            <div class="card-body">
                <!-- Kod Lokaliti -->
                <div class="mb-3">
                    <strong>Kod Lokaliti:</strong> {{ $lokaliti->kod_lokaliti }}
                </div>

                <!-- Nama Lokaliti -->
                <div class="mb-3">
                    <strong>Nama Lokaliti:</strong> {{ $lokaliti->nama_lokaliti }}
                </div>

                <!-- DM Information -->
                <div class="mb-3">
                    <strong>DM:</strong>
                    @if($lokaliti->dm)
                        <a href="{{ route('dm.show', $lokaliti->dm->id) }}">
                            {{ $lokaliti->dm->namadm ?? 'N/A' }} ({{ $lokaliti->dm->koddm ?? 'N/A' }})
                        </a>
                    @else
                        <span class="text-muted">Not Assigned</span>
                    @endif
                </div>

                <!-- DUN Information -->
                <div class="mb-3">
                    <strong>DUN:</strong>
                    @if($lokaliti->dun)
                        <a href="{{ route('dun.show', $lokaliti->dun->id) }}">
                            {{ $lokaliti->dun->namadun ?? 'N/A' }} ({{ $lokaliti->dun->kod_dun ?? 'N/A' }})
                        </a>
                    @else
                        <span class="text-muted">Not Available</span>
                    @endif
                </div>
                <!-- Parlimen Information -->
                <strong>Parlimen:</strong>
                @if($lokaliti->parlimen)
                    {{ $lokaliti->parlimen->namaparlimen }} ({{ $lokaliti->parlimen->id }})
                @else
                    <span class="text-muted">Not Available</span>
                @endif

                <!-- Effective From -->
                <div class="mb-3">
                    <strong>Effective From:</strong>
                    @if($lokaliti->effective_from)
                        {{ $lokaliti->effective_from}}
                    @else
                        <span class="text-muted">Not Set</span>
                    @endif
                </div>

                <!-- Effective To -->
                <div class="mb-3">
                    <strong>Effective To:</strong>
                    @if($lokaliti->effective_to)
                        {{ $lokaliti->effective_to }}
                    @else
                        <span class="text-muted">Not Set</span>
                    @endif
                </div>

            </div>
        </div>
    </section>
@endsection