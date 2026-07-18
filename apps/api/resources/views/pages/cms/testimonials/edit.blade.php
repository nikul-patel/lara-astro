@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Testimonial" />

    <x-common.component-card title="Edit {{ $testimonial->name }}">
        <form method="POST" action="{{ route('testimonials.update', $testimonial) }}">
            @csrf
            @method('PUT')
            @include('pages.cms.testimonials._form')
        </form>
    </x-common.component-card>
@endsection
