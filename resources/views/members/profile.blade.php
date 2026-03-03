@extends('layouts.app')

@section('title', 'Profile')

@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];
    $crumbs[] = ['label' => 'Profile', 'url' => route('members.show', $member)];
   @endphp
@endsection

@section('content')
  <section class="section">
    <livewire:members.members-profile :member="$member" />
  </section>
@endsection