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
                0%   { transform: scale(1.04) translate(0, 0); }
                100% { transform: scale(1.16) translate(-1.5%, -1%); }
            }
            .bg-kenburns {
                animation: kenburns 24s ease-in-out infinite alternate;
                filter: contrast(1.12) saturate(1.2) brightness(.92);
            }

            .road {
                position: absolute;
                left: 0; right: 0; bottom: 0;
                height: 110px;
                z-index: 10;
                background: linear-gradient(180deg, #232326 0%, #1b1b1d 40%, #0e0e10 100%);
                border-top: 3px solid rgba(255,255,255,.28);
                box-shadow: 0 -16px 40px rgba(0,0,0,.65);
            }
            .road::after {
                content: "";
                position: absolute;
                left: 0; right: 0; top: 50%;
                height: 6px;
                transform: translateY(-50%);
                background-image: repeating-linear-gradient(90deg, #fbbf24 0 44px, transparent 44px 80px);
                animation: roadScroll 1.3s linear infinite;
                box-shadow: 0 0 10px rgba(251,191,36,.55);
            }
            @keyframes roadScroll {
                0%   { background-position-x: 0; }
                100% { background-position-x: -80px; }
            }

            @keyframes driveIn {
                0%   { transform: translateX(-25vw); opacity: 0; }
                8%   { opacity: 1; }
                88%  { opacity: 1; }
                100% { transform: translateX(80vw); opacity: 0; }
            }
            .car {
                position: absolute;
                bottom: 24px;
                left: 0;
                z-index: 15;
                animation-name: driveIn;
                animation-timing-function: linear;
                animation-iteration-count: infinite;
                filter: drop-shadow(0 8px 10px rgba(0,0,0,.65));
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
            <img src="{{ asset('images/login-bg-garage.jpg') }}" alt="" class="bg-kenburns absolute inset-0 w-full h-full object-cover">

            <!-- Overall mood grade -->
            <div class="absolute inset-0 z-[4]" style="background: linear-gradient(180deg, rgba(0,0,0,.5) 0%, rgba(0,0,0,.25) 30%, rgba(0,0,0,.5) 70%, rgba(0,0,0,.85) 100%);"></div>
            <div class="absolute inset-0 z-[4]" style="background: radial-gradient(1200px 650px at 88% 10%, rgba(220,38,38,.22) 0%, transparent 60%);"></div>

            <!-- Solid dark panel the whole left side sits on, so text stays legible regardless of what's behind it -->
            <div class="absolute inset-y-0 left-0 z-[6] w-full lg:w-[46%]" style="background: linear-gradient(90deg, rgba(0,0,0,.92) 0%, rgba(0,0,0,.78) 55%, rgba(0,0,0,.35) 85%, transparent 100%);"></div>

            <!-- Road strip with lane markings -->
            <div class="road"></div>

            <!-- Luxury coupe driving across, toward the garage -->
            <svg class="car" width="136" height="42" viewBox="0 0 128 40" style="animation-duration: 8.5s; animation-delay: 0s;">
                <ellipse cx="64" cy="37" rx="58" ry="3" fill="black" opacity=".4"/>
                <path d="M6 26 C6 20 12 19 20 18 L34 8 C38 4 46 2 56 2 L82 2 C90 2 96 5 100 10 L110 18 C118 19 122 21 122 26 L122 28 C122 31 119 32 115 32 L13 32 C9 32 6 31 6 28 Z" fill="#b91c1c"/>
                <path d="M37 9 L47 3 L78 3 L92 9 L95 17 L34 17 Z" fill="#0b0f19" opacity=".92"/>
                <rect x="6" y="24" width="116" height="4" fill="#7f1d1d" opacity=".6"/>
                <circle cx="30" cy="32" r="8" fill="#0a0a0a"/>
                <circle cx="30" cy="32" r="3.6" fill="#9ca3af"/>
                <circle cx="98" cy="32" r="8" fill="#0a0a0a"/>
                <circle cx="98" cy="32" r="3.6" fill="#9ca3af"/>
                <rect x="112" y="18" width="10" height="4" rx="2" fill="#fef9c3"/>
                <rect x="4" y="19" width="8" height="3" rx="1.5" fill="#dc2626"/>
            </svg>
            <svg class="car" width="106" height="34" viewBox="0 0 100 32" style="animation-duration: 10.5s; animation-delay: 4.3s;">
                <ellipse cx="50" cy="29" rx="45" ry="2.6" fill="black" opacity=".4"/>
                <path d="M5 21 C5 16 10 15 17 14 L28 6 C31 3 38 1 46 1 L64 1 C70 1 75 4 78 8 L86 14 C92 15 95 17 95 21 L95 23 C95 25 93 26 90 26 L10 26 C7 26 5 25 5 23 Z" fill="#111827"/>
                <path d="M30 7 L38 2 L62 2 L73 7 L75 13 L27 13 Z" fill="#1f2937" opacity=".9"/>
                <circle cx="24" cy="26" r="6.2" fill="#0a0a0a"/>
                <circle cx="24" cy="26" r="2.8" fill="#d1d5db"/>
                <circle cx="76" cy="26" r="6.2" fill="#0a0a0a"/>
                <circle cx="76" cy="26" r="2.8" fill="#d1d5db"/>
                <rect x="87" y="14" width="8" height="3" rx="1.5" fill="#fef9c3"/>
                <rect x="3" y="15" width="6" height="2.4" rx="1.2" fill="#dc2626"/>
            </svg>

            <div class="relative z-20 min-h-screen flex flex-col">
                <!-- Top brand strip -->
                <div class="px-6 sm:px-10 pt-8">
                    <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-10 w-auto drop-shadow-lg">
                </div>

                <div class="flex-1 flex items-end">
                    <div class="w-full flex flex-col lg:flex-row items-center justify-between gap-10 px-6 sm:px-10 lg:px-16 pb-10 lg:pb-14 mb-[110px] lg:mb-[120px]">
                        <!-- Copy block -->
                        <div class="w-full lg:w-[44%] text-center lg:text-left order-2 lg:order-1">
                            <span class="inline-block text-red-500 text-xs font-bold tracking-[0.3em] uppercase mb-2">Dealer Management System</span>
                            <h1 class="text-5xl sm:text-6xl font-extrabold italic uppercase text-white tracking-tight leading-[0.95]" style="transform: skewX(-3deg);">Auto<span class="text-red-500">Force</span><br>DMS</h1>
                            <div class="h-1 w-20 bg-red-600 mx-auto lg:mx-0 mt-4 mb-4 rounded-full"></div>
                            <p class="text-base text-slate-200 max-w-sm mx-auto lg:mx-0">Digital vehicle inspections, customer transparency and workshop management — in one platform.</p>
                            <ul class="mt-6 space-y-2 text-left max-w-sm mx-auto lg:mx-0">
                                <li class="flex items-start gap-2 text-sm text-slate-200">
                                    <span class="text-red-500 mt-0.5">&bull;</span>
                                    <span>Multi-branch workshop, invoicing and inventory in one place.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-200">
                                    <span class="text-red-500 mt-0.5">&bull;</span>
                                    <span>Built for role-based access — admin, manager, advisor, technician.</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Login card -->
                        <div class="card-glow animate-float-in w-full max-w-sm bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl border-t-4 border-red-600 px-8 py-8 order-1 lg:order-2">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
