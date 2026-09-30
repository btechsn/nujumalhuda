@php
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
    @endpush

    <div class="nh-login">
        <img class="nh-login-photo" src="{{ asset('brand/slide-centre.jpg') }}" alt="">
        <div class="nh-login-veil" aria-hidden="true"></div>
        <main class="nh-login-card">
            {{ $slot }}
        </main>
    </div>
</x-filament-panels::layout.base>
