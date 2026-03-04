@extends('layouts.app')

@section('title', 'Election List')

@section('breadcrumb')
    @php
        // Build dynamic crumbs based on request
        $crumbs = [
            ['label' => 'Elections', 'url' => route('elections.index')],
            ['label' => $election->getLabelAttribute(), 'url' => route('elections.index')],
        ];
    @endphp
@endsection


@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Election Details</h4>

            <a href="{{ route('elections.index') }}" class="btn btn-secondary btn-sm">
                Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Type</div>
                    <div class="col-md-9">{{ $election->type }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Number</div>
                    <div class="col-md-9">{{ $election->number }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Year</div>
                    <div class="col-md-9">{{ $election->year }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Created By</div>
                    <div class="col-md-9">
                        {{ $election->creator->name ?? '-' }}
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-3 fw-bold">Created At</div>
                    <div class="col-md-9">
                        {{ $election->created_at?->format('d M Y H:i') }}
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection