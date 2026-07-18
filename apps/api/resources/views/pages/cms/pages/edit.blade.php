@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Page" />

    <x-common.component-card title="Edit {{ $page->title }}">
        <form method="POST" action="{{ route('pages.update', $page) }}">
            @csrf
            @method('PUT')
            @include('pages.cms.pages._form')
        </form>
    </x-common.component-card>
@endsection
