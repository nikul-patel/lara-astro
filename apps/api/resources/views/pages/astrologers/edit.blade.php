@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Astrologer" />

    <x-common.component-card title="Edit {{ $astrologer->name }}">
        <form method="POST" action="{{ route('astrologers.update', $astrologer) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('pages.astrologers._form')
        </form>
    </x-common.component-card>
@endsection
