@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Service" />

    <x-common.component-card title="Edit {{ $service->name }}">
        <form method="POST" action="{{ route('services.update', $service) }}">
            @csrf
            @method('PUT')
            @include('pages.services._form')
        </form>
    </x-common.component-card>
@endsection
