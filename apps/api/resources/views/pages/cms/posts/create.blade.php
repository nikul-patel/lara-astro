@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Post" />

    <x-common.component-card title="Add Post">
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
            @csrf
            @include('pages.cms.posts._form')
        </form>
    </x-common.component-card>
@endsection
