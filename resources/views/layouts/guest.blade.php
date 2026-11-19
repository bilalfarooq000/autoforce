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
            @keyframes headlightSweep {
                0%   { transform: translateX(-30%); opacity: 0; }
                8%   { opacity: 1; }
                45%  { opacity: 1; }
                55%  { opacity: 0; }
                100% { transform: translateX(130%); opacity: 0; }
            }
            .headlight-streak {
                position: absolute;
                height: 3px;
                width: 220px;
                border-radius: 999px;
                filter: blur(2px);
                animation: headlightSweep 7s linear infinite;
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

            <!-- Moving headlight / taillight streaks to suggest cars passing -->
            <div class="headlight-streak" style="top: 71%; left: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,.9), transparent); animation-delay: 0s;"></div>
            <div class="headlight-streak" style="top: 74%; left: 0; background: linear-gradient(90deg, transparent, rgba(220,38,38,.9), transparent); animation-delay: 3.2s; width: 160px;"></div>
            <div class="headlight-streak" style="top: 69%; left: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent); animation-delay: 5.4s; width: 120px;"></div>

            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.4) 35%, rgba(0,0,0,.65) 70%, rgba(0,0,0,.92) 100%);"></div>
            <div class="absolute inset-0" style="background: radial-gradient(1100px 600px at 85% 15%, rgba(220,38,38,.2) 0%, transparent 60%);"></div>
            <div class="absolute inset-0" style="background: radial-gradient(700px 400px at 10% 90%, rgba(220,38,38,.12) 0%, transparent 70%);"></div>

            <!-- Top brand strip -->
            <div class="relative z-10 px-6 sm:px-10 pt-8">
                <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-10 w-auto drop-shadow-lg">
            </div>

            <div class="relative z-10 min-h-[calc(100vh-5rem)] flex items-center justify-center lg:justify-end px-6 sm:px-10 lg:pr-20 py-10">
                <div class="hidden lg:block absolute left-12 bottom-16 max-w-md text-left">
                    <h1 class="text-5xl font-extrabold text-white tracking-tight drop-shadow-lg">AutoForce <span class="text-red-500">DMS</span></h1>
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

                <div class="card-glow animate-float-in w-full max-w-sm bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl border-t-4 border-red-600 px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
