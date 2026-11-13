<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AutoForce') }}</title>
        <link rel="icon" href="{{ asset('images/autoforce-logo-dark.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div x-data="{ mobileOpen: false }" class="flex h-screen overflow-hidden">

            <!-- Desktop sidebar -->
            <div class="hidden lg:flex">
                @include('layouts.sidebar')
            </div>

            <!-- Mobile sidebar (drawer) -->
            <div x-show="mobileOpen" x-cloak class="lg:hidden fixed inset-0 z-40 flex" style="display: none;">
                <div class="fixed inset-0 bg-black/40" @click="mobileOpen = false"></div>
                <div class="relative flex z-50">
                    @include('layouts.sidebar')
                </div>
            </div>

            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top bar -->
                <header class="bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <button @click="mobileOpen = true" class="lg:hidden text-gray-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <div class="flex-1 min-w-0">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <h1 class="font-semibold text-gray-800">{{ config('app.name', 'AutoForce') }}</h1>
                                @endisset
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Flash status -->
                @if (session('status'))
                    <div class="px-4 sm:px-6 pt-4">
                        <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
