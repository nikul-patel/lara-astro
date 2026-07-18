@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Course" />

    <x-common.component-card title="Add Course">
        <form method="POST" action="{{ route('courses.store') }}">
            @csrf
            @include('pages.courses._form')
        </form>
    </x-common.component-card>
@endsection
