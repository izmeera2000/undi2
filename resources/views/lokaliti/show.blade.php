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
                            {{ $lokaliti->dm->namadm }} ({{ $lokaliti->dm->koddm }})
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
                            {{ $lokaliti->dun->namadun }} ({{ $lokaliti->dun->kod_dun }})
                        </a>
                    @else
                        <span class="text-muted">Not Available</span>
                    @endif
                </div>

                <!-- Parlimen Information -->
                <div class="mb-3">
                    <strong>Parlimen:</strong>
                    @if($lokaliti->dun && $lokaliti->dun->parlimen)
                        <a href="{{ route('parlimen.show', $lokaliti->dun->parlimen->id) }}">
                            {{ $lokaliti->dun->parlimen->namapar }}
                        </a>
                    @else
                        <span class="text-muted">Not Available</span>
                    @endif
                </div>
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


        @php
            // Get all Lokaliti with the same kod_lokaliti under this DM
            $sameLokalitis = \App\Models\Lokaliti::where('kod_lokaliti', $lokaliti->kod_lokaliti)
                ->orderBy('effective_from')
                ->get();

            // Filter: only show those with different name or effective dates
            $filteredLokalitis = $sameLokalitis->filter(function ($record) use ($lokaliti) {
                return $record->id !== $lokaliti->id && (
                    $record->nama_lokaliti !== $lokaliti->nama_lokaliti ||
                    $record->effective_from?->format('Y-m-d') !== $lokaliti->effective_from?->format('Y-m-d') ||
                    $record->effective_to?->format('Y-m-d') !== $lokaliti->effective_to?->format('Y-m-d')
                );
            })->values(); // reset keys for carousel
        @endphp

        @if($filteredLokalitis->isNotEmpty())
            <hr>
            <h6 class="mb-3">Other Versions of This Lokaliti</h6>

            <div id="sameLokalitiCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">
                <div class="carousel-inner">

                    @foreach($filteredLokalitis as $index => $record)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="d-flex justify-content-center">
                                <div class="card shadow-sm" style="width: 30rem;">
                                    <div class="card-header bg-light">
                                        <strong>Kod Lokaliti:</strong> {{ $record->kod_lokaliti }}
                                    </div>
                                    <div class="card-body">

                                        <p class="mb-2">
                                            <strong>Nama Lokaliti:</strong>
                                            <a href="{{ route('lokaliti.show', $record->id) }}">
                                                {{ $record->nama_lokaliti }} ({{ $record->kod_lokaliti }})
                                            </a>
                                        </p>

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
                <button class="carousel-control-prev" type="button" data-bs-target="#sameLokalitiCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#sameLokalitiCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>

            </div>
        @endif
    </section>
@endsection