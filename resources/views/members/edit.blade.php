@extends('layouts.app')

@section('title', 'Edit Profile')



@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];
        $crumbs[] = ['label' => 'Profile', 'url' => route('members.show', $member)];
        $crumbs[] = ['label' => 'Edit', 'url' => route('members.edit', $member)];
     @endphp
@endsection

@section('content')
    <!-- Welcome & Stats Row -->



    <section class="section">
        <!-- Profile Cover & Card -->

        <livewire:members-profile-edit :member="$member"/>

    </section>


@endsection

@push('scripts')


    

    





@endpush