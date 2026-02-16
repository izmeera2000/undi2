@extends('layouts.app')

@section('title', 'Dun Details')

@section('breadcrumb')
@php
    $crumbs = [
        ['label' => 'Dun', 'url' => route('dun.index')],
        ['label' => 'Details', 'url' => route('dun.show', $dun->id)],
    ];
@endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Dun Details</h5>
            <a href="{{ route('dun.edit', $dun->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>

        <div class="card-body">
            <!-- Kod Dun -->
            <div class="mb-3">
                <strong>Kod Dun:</strong> {{ $dun->kod_dun }}
            </div>

            <!-- Nama Dun -->
            <div class="mb-3">
                <strong>Nama Dun:</strong> {{ $dun->namadun }}
            </div>

            <!-- Parlimen -->
            <div class="mb-3">
                <strong>Parlimen:</strong> 
                @if($dun->parlimen)
                    <a href="{{ route('parlimen.show', $dun->parlimen->id) }}">
                        {{ $dun->parlimen->namapar }}
                    </a>
                @else
                    <span class="text-muted">Not Assigned</span>
                @endif
            </div>

            <!-- Status -->
            <div class="mb-3">
                <strong>Status:</strong> 
                {{ ucfirst($dun->status) }} <!-- Display 'Active' or 'Inactive' -->
            </div>

            <!-- Effective From -->
            <div class="mb-3">
                <strong>Effective From:</strong> 
                {{ $dun->effective_from ? $dun->effective_from->format('Y-m-d') : 'Not Set' }}
            </div>

            <!-- Effective To -->
            <div class="mb-3">
                <strong>Effective To:</strong> 
                {{ $dun->effective_to ? $dun->effective_to->format('Y-m-d') : 'Not Set' }}
            </div>

            <hr>

            <!-- DMs under this Dun -->
            <h6>DMs under this Dun</h6>

            @if($dun->dms->isEmpty())
                <p class="text-muted">No DMs found under this Dun.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kod DM</th>
                                <th>Nama DM</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dun->dms as $index => $dm)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dm->koddm }}</td>
                                    <td>{{ $dm->namadm }}</td>
                                    <td>
                                        <a href="{{ route('dm.show', $dm->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        <a href="{{ route('dm.edit', $dm->id) }}" class="btn btn-sm btn-outline-secondary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</section>
@endsection
