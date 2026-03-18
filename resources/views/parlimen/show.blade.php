@extends('layouts.app')

@section('title', 'Parlimen Details')

@section('breadcrumb')
@php
    $crumbs = [
        ['label' => 'Parlimen', 'url' => route('parlimen.index')],
        ['label' => 'Details', 'url' => route('parlimen.show', $parlimen->id)],
    ];
@endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Parlimen Details</h5>
            <a href="{{ route('parlimen.edit', $parlimen->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <strong>Kod Parlimen:</strong> {{ $parlimen->kod_par }}
            </div>
            <div class="mb-3">
                <strong>Nama Parlimen:</strong> {{ $parlimen->nama_par }}
            </div>

            <hr>

            <h6>DUNs under this Parlimen</h6>

            @if($parlimen->duns->isEmpty())
                <p class="text-muted">No DUNs found under this Parlimen.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kod DUN</th>
                                <th>Nama DUN</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parlimen->duns as $index => $dun)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dun->kod_dun }}</td>
                                    <td>{{ $dun->nama_dun }}</td>
                                    <td>
                                        <a href="{{ route('dun.show', $dun->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        <a href="{{ route('dun.edit', $dun->id) }}" class="btn btn-sm btn-outline-secondary">
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
