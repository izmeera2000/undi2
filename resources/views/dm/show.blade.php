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
                    <strong>Kod DM:</strong> {{ $dm->kod_dm }}
                </div>

                <!-- Nama DM -->
                <div class="mb-3">
                    <strong>Nama DM:</strong> {{ $dm->nama_dm }}
                </div>

                <!-- DUN Information -->
                <div class="mb-3">
                    <strong>DUN:</strong>
                    @if($dm->dun)
                        <a href="{{ route('dun.show', $dm->dun->id) }}">
                            {{ $dm->dun->nama_dun }} ({{ $dm->dun->kod_dun }})
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
                            {{ $dm->dun->parlimen->nama_par }}
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

                @php
                    $activeLokalitis = $dm->lokalitis->filter(function ($lokaliti) use ($dm) {

                        $dmTo = $dm->effective_to ?? now(); // or Carbon::maxValue() if needed

                        return
                            $lokaliti->effective_from <= $dmTo &&
                            (
                                is_null($lokaliti->effective_to) ||
                                $lokaliti->effective_to >= $dm->effective_from
                            );
                    });
                @endphp

                @if($activeLokalitis->isEmpty())
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
                                @foreach($activeLokalitis as $index => $lokaliti)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $lokaliti->kod_lokaliti }}</td>
                                        <td>{{ $lokaliti->nama_lokaliti }}</td>
                                        <td>
                                            <a href="{{ route('lokaliti.show', $lokaliti->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('lokaliti.edit', $lokaliti->id) }}"
                                                class="btn btn-sm btn-outline-secondary">
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
            $sameDms = \App\Models\Dm::where('kod_dm', $dm->kod_dm)
                ->orderBy('effective_from')
                ->get();

            $filteredDms = $sameDms->filter(function ($record) use ($dm) {
                return $record->id !== $dm->id && (
                    $record->nama_dm !== $dm->nama_dm ||
                    $record->effective_from != $dm->effective_from ||
                    $record->effective_to != $dm->effective_to
                );
            });
            $filteredDms = $filteredDms->values(); // reindex keys
         @endphp

        @if($filteredDms->isNotEmpty())
            <hr>
            <h6 class="mb-3">Same Kod DM</h6>

            <div id="sameKodDmCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">
                <div class="carousel-inner">

                    @foreach($filteredDms as $index => $record)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="d-flex justify-content-center">
                                <div class="card shadow-sm" style="width: 30rem;">
                                    <div class="card-header bg-light">
                                        <strong>Kod DM:</strong> {{ $record->kod_dm }}
                                    </div>
                                    <div class="card-body">

                                        <p class="mb-2">
                                            <strong>Nama DM:</strong>
                                            <a href="{{ route('dm.show', $record->id) }}">
                                                {{ $record->nama_dm }} ({{ $record->kod_dm }})
                                            </a>
                                        </p>

                                        <div class="mb-2">
                                            <strong>DUN:</strong>
                                            @if($record->dun)
                                                <a href="{{ route('dun.show', $record->dun->id) }}">
                                                    {{ $record->dun->nama_dun }} ({{ $record->dun->kod_dun }})
                                                </a>
                                            @else
                                                <span class="text-muted">Not Assigned</span>
                                            @endif
                                        </div>

                                        <div class="mb-2">
                                            <strong>Parlimen:</strong>
                                            @if($record->dun && $record->dun->parlimen)
                                                <a href="{{ route('parlimen.show', $record->dun->parlimen->id) }}">
                                                    {{ $record->dun->parlimen->nama_par }}
                                                </a>
                                            @else
                                                <span class="text-muted">Not Available</span>
                                            @endif
                                        </div>

                                        <p class="mb-1">
                                            <strong>Effective From:</strong>
                                            {{ $record->effective_from?->format('Y-m-d') ?? 'Not Set' }}
                                        </p>

                                        <p class="mb-0">
                                            <strong>Effective To:</strong>
                                            {{ $record->effective_to?->format('Y-m-d') ?? 'Not Set' }}
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#sameKodDmCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#sameKodDmCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>

            </div>
        @endif


    </section>
@endsection