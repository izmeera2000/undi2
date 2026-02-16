@extends('layouts.app')

@section('title', 'DM Details')

@section('breadcrumb')
@php
    $crumbs = [
        ['label' => 'DM', 'url' => route('dm.index')],
        ['label' => 'Details', 'url' => route('dm.show', $dm->id)],
    ];
@endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>DM Details</h5>
            <a href="{{ route('dm.edit', $dm->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>

        <div class="card-body">
            <!-- Kod DM -->
            <div class="mb-3">
                <strong>Kod DM:</strong> {{ $dm->koddm }}
            </div>

            <!-- Nama DM -->
            <div class="mb-3">
                <strong>Nama DM:</strong> {{ $dm->namadm }}
            </div>

            <!-- DUN Information -->
            <div class="mb-3">
                <strong>DUN:</strong>
                @if($dm->dun)
                    <a href="{{ route('dun.show', $dm->dun->id) }}">
                        {{ $dm->dun->namadun }} ({{ $dm->dun->kod_dun }})
                    </a>
                @else
                    <span class="text-muted">Not Assigned</span>
                @endif
            </div>

            <!-- Parlimen Information -->
            <div class="mb-3">
                <strong>Parlimen:</strong>
                @if($dm->dun && $dm->dun->parlimen)
                    <a href="{{ route('parlimen.show', $dm->dun->parlimen->id) }}">
                        {{ $dm->dun->parlimen->namapar }}
                    </a>
                @else
                    <span class="text-muted">Not Available</span>
                @endif
            </div>

            <!-- Effective From -->
            <div class="mb-3">
                <strong>Effective From:</strong> 
                @if($dm->effective_from)
                    {{ $dm->effective_from->format('Y-m-d') }}
                @else
                    <span class="text-muted">Not Set</span>
                @endif
            </div>

            <!-- Effective To -->
            <div class="mb-3">
                <strong>Effective To:</strong> 
                @if($dm->effective_to)
                    {{ $dm->effective_to->format('Y-m-d') }}
                @else
                    <span class="text-muted">Not Set</span>
                @endif
            </div>

<hr>

<!-- Lokalitis under this DM -->
<h6>Lokalitis under this DM</h6>

@if($dm->lokalitis->isEmpty())
    <p class="text-muted">No Lokalitis found under this DM.</p>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kod Lokaliti</th>
                    <th>Nama Lokaliti</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dm->lokalitis as $index => $lokaliti)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lokaliti->kod_lokaliti }}</td>
                        <td>{{ $lokaliti->nama_lokaliti }}</td>
                        <td>
                            <a href="{{ route('lokaliti.show', $lokaliti->id) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                            <a href="{{ route('lokaliti.edit', $lokaliti->id) }}" class="btn btn-sm btn-outline-secondary">
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
