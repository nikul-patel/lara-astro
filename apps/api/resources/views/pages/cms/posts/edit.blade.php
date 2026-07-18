@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Post" />

    <x-common.component-card title="Edit {{ $post->title }}">
        <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('pages.cms.posts._form')
        </form>
    </x-common.component-card>
@endsection
