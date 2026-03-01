@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
  @php $crumbs = []; @endphp
@endsection

@section('content')
  <div class="row g-4">

    <div class="col col-md-6">
 <livewire:welcome-banner />
    </div>

    <div class="col col-md-6 g-4">
      <livewire:weather-widget  lazy />
    </div>

    <div class="col-md-4">
       <livewire:upcoming-events lazy  />

    </div>

    <div class="col-lg-8">
       <livewire:tasks lazy/>

    </div>

  </div>
@endsection

@push('scripts')
  
@endpush