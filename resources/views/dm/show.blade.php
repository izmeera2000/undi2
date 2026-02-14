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

            <div class="mb-3">
                <strong>Kod DM:</strong> {{ $dm->kod_dm }}
            </div>

            <div class="mb-3">
                <strong>Nama DM:</strong> {{ $dm->namadm }}
            </div>

            <div class="mb-3">
                <strong>Dun:</strong>
                @if($dm->dun)
                    <a href="{{ route('dun.show', $dm->dun->id) }}">
                        {{ $dm->dun->namadun }}
                    </a>
                @else
                    <span class="text-muted">Not Assigned</span>
                @endif
            </div>

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

        </div>
    </div>
</section>
@endsection
