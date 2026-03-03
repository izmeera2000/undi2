@extends('layouts.app')

@section('title', 'Members List')

@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];

  @endphp
@endsection


@push('styles')

  <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css')}}">

@endpush




@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">


    <section class="section">
      <!-- Stats Cards -->

      <livewire:members.members-list />



    </section>

    <livewire:members.members-list-modal />


    {{-- @include('members.partials.list.modals') --}}



@endsection




  @push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>



  @endpush