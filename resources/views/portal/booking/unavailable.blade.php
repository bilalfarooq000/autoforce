<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Unavailable — {{ $branch->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <img src="{{ asset('images/autoforce-logo-dark.png') }}" alt="AutoForce" class="h-10 w-auto mx-auto mb-6">
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-lg font-bold text-gray-900 mb-2">This branch isn't taking bookings right now</h1>
            <p class="text-sm text-gray-500">{{ $branch->name }}{{ $branch->city ? ' — '.$branch->city : '' }} is currently closed for online booking requests. Please call the garage directly, or try one of our other branches.</p>
        </div>
    </div>
</body>
</html>
