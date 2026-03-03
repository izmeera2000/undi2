@extends('layouts.app')

@section('title', 'Task')



@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Task', 'url' => route('task.index')],
      ['label' => 'List', 'url' => route('task.index')],
    ];

  @endphp

@endsection


@push('styles')


@endpush


@section('content')
  <section class="section">
    <div class="todo-container">
      <div class="todo-main">
        {{-- THE NEW LIVEWIRE COMPONENT --}}
        <livewire:tasks.task-manager />
      </div>
    </div>

    {{-- Keep your modals --}}
    {{-- @include('task.partials.modal.add_category') --}}
    <livewire:tasks.add-task-modal />
    <livewire:tasks.edit-task-modal />

    <livewire:tasks.view-task-modal />
  </section>
@endsection

@push('scripts')
  {{-- NO MORE JQUERY GET/RENDER LOGIC NEEDED HERE! --}}
@endpush