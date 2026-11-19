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

        <style>
            @keyframes kenburns {
                0%   { transform: scale(1) translate(0, 0); }
                100% { transform: scale(1.12) translate(-1.5%, -1%); }
            }
            .bg-kenburns {
                animation: kenburns 22s ease-in-out infinite alternate;
            }

            /* Cars driving across the bottom, toward the garage */
            .car-lane {
                position: absolute;
                left: 0;
                width: 100%;
                height: 44px;
                z-index: 15;
                pointer-events: none;
            }
            @keyframes driveIn {
                0%   { transform: translateX(-20vw); opacity: 0; }
                10%  { opacity: 1; }
                85%  { opacity: 1; }
                100% { transform: translateX(78vw); opacity: 0; }
            }
            .car {
                position: absolute;
                bottom: 0;
                animation-name: driveIn;
                animation-timing-function: linear;
                animation-iteration-count: infinite;
                filter: drop-shadow(0 4px 6px rgba(0,0,0,.5));
            }

            @keyframes floatCard {
                0%   { opacity: 0; transform: translateY(16px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .animate-float-in {
                animation: floatCard .7s ease-out both;
            }
            @keyframes glowPulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,.35), 0 25px 60px -15px rgba(0,0,0,.6); }
                50%      { box-shadow: 0 0 0 6px rgba(220,38,38,.12), 0 25px 60px -15px rgba(0,0,0,.6); }
            }
            .card-glow {
                animation: glowPulse 3.5s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen w-full overflow-hidden bg-black">
            <!-- Full-screen branding photo with slow cinematic zoom -->
            <img src="{{ asset('images/login-bg-garage.jpg') }}" alt="" class="bg-kenburns absolute inset-0 w-full h-full object-cover scale-105">

            <div class="absolute inset-0 z-[5]" style="background: linear-gradient(180deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.4) 35%, rgba(0,0,0,.65) 70%, rgba(0,0,0,.92) 100%);"></div>
            <div class="absolute inset-0 z-[5]" style="background: radial-gradient(1100px 600px at 85% 15%, rgba(220,38,38,.2) 0%, transparent 60%);"></div>
            <!-- Dedicated dark panel behind the left-side text so it stays legible over the busy photo -->
            <div class="absolute inset-0 z-[5]" style="background: radial-gradient(520px 420px at 8% 92%, rgba(0,0,0,.85) 0%, rgba(0,0,0,.55) 45%, transparent 75%);"></div>

            <!-- Cars driving across the road toward the garage -->
            <div class="car-lane" style="bottom: 17%;">
                <svg class="car" width="92" height="34" viewBox="0 0 92 34" style="animation-duration: 9s; animation-delay: 0s;">
                    <ellipse cx="46" cy="30" rx="44" ry="3" fill="black" opacity=".35"/>
                    <rect x="4" y="12" width="84" height="14" rx="6" fill="#dc2626"/>
                    <path d="M18 12 L30 2 H62 L74 12 Z" fill="#111827"/>
                    <circle cx="22" cy="27" r="6" fill="#0a0a0a"/>
                    <circle cx="70" cy="27" r="6" fill="#0a0a0a"/>
                    <circle cx="88" cy="18" r="2.4" fill="#fff" opacity=".95"/>
                    <circle cx="4" cy="18" r="2" fill="#f87171"/>
                </svg>
            </div>
            <div class="car-lane" style="bottom: 13.5%;">
                <svg class="car" width="78" height="30" viewBox="0 0 78 30" style="animation-duration: 11s; animation-delay: 4s;">
                    <ellipse cx="39" cy="26" rx="37" ry="2.6" fill="black" opacity=".35"/>
                    <rect x="3" y="10" width="72" height="12" rx="5" fill="#e5e7eb"/>
                    <path d="M15 10 L26 1 H54 L64 10 Z" fill="#111827"/>
                    <circle cx="19" cy="23" r="5" fill="#0a0a0a"/>
                    <circle cx="59" cy="23" r="5" fill="#0a0a0a"/>
                    <circle cx="75" cy="15" r="2" fill="#fff" opacity=".95"/>
                    <circle cx="3" cy="15" r="1.7" fill="#f87171"/>
                </svg>
            </div>

            <!-- Top brand strip -->
            <div class="relative z-20 px-6 sm:px-10 pt-8">
                <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-10 w-auto drop-shadow-lg">
            </div>

            <div class="relative z-20 min-h-[calc(100vh-5rem)] flex items-center justify-center lg:justify-end px-6 sm:px-10 lg:pr-20 py-10">
                <div class="hidden lg:block absolute left-12 bottom-20 max-w-md text-left">
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-[0_2px_8px_rgba(0,0,0,.9)]">AutoForce <span class="text-red-500">DMS</span></h1>
                    <p class="text-base text-slate-100 mt-3 max-w-sm drop-shadow-[0_2px_6px_rgba(0,0,0,.9)]">Digital vehicle inspections, customer transparency and workshop management — in one platform.</p>
                    <ul class="mt-6 space-y-2 text-left">
                        <li class="flex items-start gap-2 text-sm text-slate-100 drop-shadow-[0_2px_6px_rgba(0,0,0,.9)]">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Multi-branch workshop, invoicing and inventory in one place.</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-100 drop-shadow-[0_2px_6px_rgba(0,0,0,.9)]">
                            <span class="text-red-500 mt-0.5">&bull;</span>
                            <span>Built for role-based access — admin, manager, advisor, technician.</span>
                        </li>
                    </ul>
                </div>

                <div class="card-glow animate-float-in w-full max-w-sm bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl border-t-4 border-red-600 px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
