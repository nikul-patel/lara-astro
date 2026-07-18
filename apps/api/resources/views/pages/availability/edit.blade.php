@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Availability Slot" />

    <x-common.component-card title="Edit Slot">
        <form method="POST" action="{{ route('availability.update', $slot) }}">
            @csrf
            @method('PUT')
            @include('pages.availability._form')
        </form>
    </x-common.component-card>
@endsection
