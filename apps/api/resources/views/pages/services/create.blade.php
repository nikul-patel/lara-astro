@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Service" />

    <x-common.component-card title="New Service">
        <form method="POST" action="{{ route('services.store') }}">
            @csrf
            @include('pages.services._form')
        </form>
    </x-common.component-card>
@endsection
