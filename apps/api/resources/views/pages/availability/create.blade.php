@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Availability Slot" />

    <x-common.component-card title="New Availability Slot">
        <form method="POST" action="{{ route('availability.store') }}">
            @csrf
            @include('pages.availability._form')
        </form>
    </x-common.component-card>
@endsection
