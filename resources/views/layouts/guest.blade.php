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
        <div class="relative min-h-screen w-full overflow-hidden bg-black">
            <!-- Full-screen branding photo -->
            <img src="{{ asset('images/login-bg-garage.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.45) 35%, rgba(0,0,0,.65) 70%, rgba(0,0,0,.9) 100%);"></div>
            <div class="absolute inset-0" style="background: radial-gradient(1100px 600px at 85% 15%, rgba(200,0,0,.16) 0%, transparent 60%);"></div>

            <!-- Top brand strip -->
            <div class="relative z-10 px-6 sm:px-10 pt-8">
                <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-10 w-auto drop-shadow-lg">
            </div>

            <div class="relative z-10 min-h-[calc(100vh-5rem)] flex items-center justify-center lg:justify-end px-6 sm:px-10 lg:pr-20 py-10">
                <div class="hidden lg:block absolute left-12 bottom-16 max-w-md text-left">
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">AutoForce DMS</h1>
                    <p class="text-base text-slate-200 mt-3 max-w-sm drop-shadow">Digital vehicle inspections, customer transparency and workshop management — in one platform.</p>
                    <ul class="mt-6 space-y-2 text-left">
                        <li class="flex items-start gap-2 text-sm text-slate-200 drop-shadow">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Multi-branch workshop, invoicing and inventory in one place.</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-200 drop-shadow">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Built for role-based access — admin, manager, advisor, technician.</span>
                        </li>
                    </ul>
                </div>

                <div class="w-full max-w-sm bg-white/95 backdrop-blur shadow-2xl rounded-2xl border-t-4 border-red-600 px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
