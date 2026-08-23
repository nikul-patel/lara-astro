@extends('layouts.app')

@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Profile" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-common.component-card title="Personal Information">
            <div class="flex items-center gap-4 mb-6">
                <span class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-brand-500 text-xl font-semibold text-white">
                    {{ Str::of($user->name)->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('') }}
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</p>
                    @if ($user->getRoleNames()->isNotEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->getRoleNames()->join(', ') }}</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Name<span class="text-error-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $inputClass }}" />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Email address<span class="text-error-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inputClass }}" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Change Password">
            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="{{ $labelClass }}">Current Password<span class="text-error-500">*</span></label>
                        <input type="password" name="current_password" required autocomplete="current-password" class="{{ $inputClass }}" />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">New Password<span class="text-error-500">*</span></label>
                        <input type="password" name="password" required autocomplete="new-password" class="{{ $inputClass }}" />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Confirm New Password<span class="text-error-500">*</span></label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $inputClass }}" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Update Password
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
