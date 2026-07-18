@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Astrologer" />

    <x-common.component-card title="New Astrologer">
        <form method="POST" action="{{ route('astrologers.store') }}" enctype="multipart/form-data">
            @csrf
            @include('pages.astrologers._form')
        </form>
    </x-common.component-card>
@endsection
