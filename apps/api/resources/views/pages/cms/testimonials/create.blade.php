@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Testimonial" />

    <x-common.component-card title="Add Testimonial">
        <form method="POST" action="{{ route('testimonials.store') }}">
            @csrf
            @include('pages.cms.testimonials._form')
        </form>
    </x-common.component-card>
@endsection
