@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="row g-4">

    {{-- Welcome Banner (Non-lazy usually safe, but good to key) --}}
    <div class="col-md-6" wire:key="dash-welcome">
        <livewire:welcome-banner />
    </div>

    {{-- Weather - Added unique wire:key --}}
    <div class="col-md-6" wire:key="dash-weather">
        <livewire:weather-widget  />
    </div>

    {{-- Events - Added unique wire:key --}}
    <div class="col-md-4" wire:key="dash-events">
        <livewire:upcoming-events  />
    </div>

    {{-- Tasks - Added unique wire:key --}}
    <div class="col-lg-8" wire:key="dash-tasks">
        <livewire:tasks  />
    </div>

  </div>
@endsection