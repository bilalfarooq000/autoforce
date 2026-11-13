<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AutoForce') }}</title>
        <link rel="icon" href="{{ asset('images/autoforce-logo-dark.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">

            <!-- Branding panel -->
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden items-center justify-center bg-black">
                <img src="{{ asset('images/login-bg-car.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-60">
                <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.55) 40%, rgba(20,0,0,.75) 75%, rgba(0,0,0,.92) 100%);"></div>
                <div class="absolute inset-0" style="background: radial-gradient(900px 500px at 15% 10%, rgba(200,0,0,.18) 0%, transparent 60%);"></div>

                <div class="relative z-10 text-center px-12">
                    <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-24 w-auto mx-auto mb-8 drop-shadow-lg">
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow">AutoForce DMS</h1>
                    <p class="text-base text-slate-300 mt-3 max-w-sm mx-auto drop-shadow">Digital vehicle inspections, customer transparency and workshop management — in one platform.</p>
                </div>
            </div>

            <!-- Form panel -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center px-6 py-12 bg-gray-50">
                <div class="lg:hidden mb-8 text-center">
                    <img src="{{ asset('images/autoforce-logo-dark.png') }}" alt="AutoForce" class="h-16 w-auto mx-auto">
                    <p class="text-sm text-gray-500 mt-2 font-semibold tracking-wide uppercase">AutoForce DMS</p>
                </div>

                <div class="w-full max-w-sm bg-white shadow-xl rounded-2xl border-t-4 border-red-600 px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
