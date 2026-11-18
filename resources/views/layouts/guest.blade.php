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

                    <ul class="mt-6 space-y-2 text-left max-w-sm mx-auto">
                        <li class="flex items-start gap-2 text-sm text-slate-300 drop-shadow">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Multi-branch workshop, invoicing and inventory in one place.</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-300 drop-shadow">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Built for role-based access — admin, manager, advisor, technician.</span>
                        </li>
                    </ul>
                </div>

                <!-- Cars pulling into the garage -->
                <svg class="absolute bottom-0 left-0 w-full h-20 z-10" viewBox="0 0 800 110" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0" y="70" width="800" height="40" fill="#0a0a0a" />
                    <line x1="0" y1="90" x2="800" y2="90" stroke="#dc2626" stroke-width="3" stroke-dasharray="22 16" opacity="0.8" />

                    <!-- Garage -->
                    <g transform="translate(640,28)">
                        <rect x="0" y="10" width="130" height="60" fill="#1f2937" stroke="#dc2626" stroke-width="2"/>
                        <polygon points="-6,10 65,-22 136,10" fill="#111827" stroke="#dc2626" stroke-width="2"/>
                        <rect x="20" y="28" width="90" height="42" fill="#0a0a0a" stroke="#dc2626" stroke-width="1.5"/>
                        <rect x="28" y="34" width="22" height="34" fill="#1f2937"/>
                        <rect x="56" y="34" width="22" height="34" fill="#1f2937"/>
                        <rect x="84" y="34" width="22" height="34" fill="#1f2937"/>
                    </g>

                    <!-- Car 1 -->
                    <g transform="translate(60,58)">
                        <rect x="0" y="10" width="54" height="16" rx="4" fill="#dc2626"/>
                        <rect x="10" y="0" width="30" height="14" rx="4" fill="#dc2626"/>
                        <circle cx="12" cy="28" r="6" fill="#111827"/>
                        <circle cx="42" cy="28" r="6" fill="#111827"/>
                    </g>

                    <!-- Car 2 -->
                    <g transform="translate(280,60)">
                        <rect x="0" y="9" width="48" height="14" rx="4" fill="#e5e7eb"/>
                        <rect x="9" y="0" width="26" height="12" rx="4" fill="#e5e7eb"/>
                        <circle cx="11" cy="25" r="5.5" fill="#111827"/>
                        <circle cx="37" cy="25" r="5.5" fill="#111827"/>
                    </g>

                    <!-- Car 3, closer to the garage -->
                    <g transform="translate(480,58)">
                        <rect x="0" y="10" width="54" height="16" rx="4" fill="#9ca3af"/>
                        <rect x="10" y="0" width="30" height="14" rx="4" fill="#9ca3af"/>
                        <circle cx="12" cy="28" r="6" fill="#111827"/>
                        <circle cx="42" cy="28" r="6" fill="#111827"/>
                    </g>
                </svg>
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
