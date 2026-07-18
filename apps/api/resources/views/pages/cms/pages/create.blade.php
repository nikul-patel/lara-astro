@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Page" />

    <x-common.component-card title="Add Page">
        <form method="POST" action="{{ route('pages.store') }}">
            @csrf
            @include('pages.cms.pages._form')
        </form>
    </x-common.component-card>
@endsection
