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
                    {{ $dun->effective_from ? $dun->effective_from : 'Not Set' }}
                </div>

                <!-- Effective To -->
                <div class="mb-3">
                    <strong>Effective To:</strong>
                    {{ $dun->effective_to ? $dun->effective_to : 'Not Set' }}
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

        @php
            // Get all DUNs with the same kod_dun
            $sameDuns = \App\Models\Dun::where('kod_dun', $dun->kod_dun)
                ->orderBy('effective_from')
                ->get();

            // Filter: only show those with different name or effective dates
            $filteredDuns = $sameDuns->filter(function ($record) use ($dun) {
                return $record->id !== $dun->id && (
                    $record->namadun !== $dun->namadun ||
                    $record->effective_from?->format('Y-m-d') !== $dun->effective_from?->format('Y-m-d') ||
                    $record->effective_to?->format('Y-m-d') !== $dun->effective_to?->format('Y-m-d')
                );
            })->values(); // reset keys for carousel
        @endphp

        @if($filteredDuns->isNotEmpty())
            <hr>
            <h6 class="mb-3">Other Versions of This DUN</h6>

            <div id="sameDunCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">
                <div class="carousel-inner">

                    @foreach($filteredDuns as $index => $record)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="d-flex justify-content-center">
                                <div class="card shadow-sm" style="width: 30rem;">
                                    <div class="card-header bg-light">
                                        <strong>Kod DUN:</strong> {{ $record->kod_dun }}
                                    </div>
                                    <div class="card-body">

                                        <p class="mb-2">
                                            <strong>Nama DUN:</strong>
                                            <a href="{{ route('dun.show', $record->id) }}">
                                                {{ $record->namadun }} ({{ $record->kod_dun }})
                                            </a>
                                        </p>

                                        <div class="mb-2">
                                            <strong>Parlimen:</strong>
                                            @if($record->parlimen)
                                                <a href="{{ route('parlimen.show', $record->parlimen->id) }}">
                                                    {{ $record->parlimen->namapar }}
                                                </a>
                                            @else
                                                <span class="text-muted">Not Assigned</span>
                                            @endif
                                        </div>

                                        <p class="mb-1">
                                            <strong>Effective From:</strong>
                                            {{ $record->effective_from ?? 'Not Set' }}
                                        </p>

                                        <p class="mb-0">
                                            <strong>Effective To:</strong>
                                            {{ $record->effective_to ?? 'Not Set' }}
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#sameDunCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#sameDunCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>

            </div>
        @endif
    </section>
@endsection