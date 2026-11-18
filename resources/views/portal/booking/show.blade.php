<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book a Service — {{ $branch->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="max-w-xl mx-auto px-4 py-8 space-y-6">
        <div class="text-center">
            <img src="{{ asset('images/autoforce-logo-dark.png') }}" alt="AutoForce" class="h-10 w-auto mx-auto mb-2">
            <h1 class="text-xl font-bold text-gray-900">Book a Service</h1>
            <p class="text-sm text-gray-500">{{ $branch->name }}{{ $branch->city ? ' — '.$branch->city : '' }}</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.book.store', $branch) }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Your Name</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="03XX-XXXXXXX" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email (optional)</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle Reg (optional)</label>
                    <input type="text" name="vehicle_registration_number" value="{{ old('vehicle_registration_number') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Make (optional)</label>
                    <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Model (optional)</label>
                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            @if ($serviceTypes->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Service Needed (optional)</label>
                    <select name="service_type_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">— Not sure yet —</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}" @selected(old('service_type_id') == $serviceType->id)>{{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Preferred Date & Time</label>
                <input type="datetime-local" name="requested_at" value="{{ old('requested_at') }}" required
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Anything else? (optional)</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">
                Request Booking
            </button>
            <p class="text-xs text-gray-400 text-center">The garage will confirm your slot by phone or SMS.</p>
        </form>
    </div>
</body>
</html>
