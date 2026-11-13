<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register Vehicle</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.vehicles.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Customer</h3>
                @if ($customer)
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div>
                            <div class="font-medium text-gray-900">{{ $customer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $customer->phone }} @if($customer->email) · {{ $customer->email }} @endif</div>
                        </div>
                        <a href="{{ route('staff.vehicles.create') }}" class="text-xs text-red-600 hover:underline shrink-0">Change customer</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="customer_name" value="Customer Name" />
                            <x-text-input id="customer_name" name="customer_name" class="block mt-1 w-full" :value="old('customer_name')" required />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="customer_phone" value="Phone (WhatsApp/SMS)" />
                            <x-text-input id="customer_phone" name="customer_phone" class="block mt-1 w-full" :value="old('customer_phone')" required placeholder="03XX-XXXXXXX" />
                            <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="customer_email" value="Email (optional)" />
                            <x-text-input id="customer_email" name="customer_email" type="email" class="block mt-1 w-full" :value="old('customer_email')" />
                            <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        If the phone number already exists, the vehicle will be linked to that existing customer. Prefer
                        <a href="{{ route('staff.customers.create') }}" class="text-red-600 hover:underline">registering the customer first</a>
                        and picking them from the <a href="{{ route('staff.customers.index') }}" class="text-red-600 hover:underline">Customers</a> list instead.
                    </p>
                @endif
            </div>

            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Vehicle Identification</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="registration_number" value="Registration Number" />
                        <x-text-input id="registration_number" name="registration_number" class="block mt-1 w-full" :value="old('registration_number')" required />
                        <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="make" value="Make" />
                        <x-text-input id="make" name="make" class="block mt-1 w-full" :value="old('make')" required placeholder="Toyota" />
                        <x-input-error :messages="$errors->get('make')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="model" value="Model" />
                        <x-text-input id="model" name="model" class="block mt-1 w-full" :value="old('model')" required placeholder="Corolla" />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="year" value="Year" />
                        <x-text-input id="year" name="year" class="block mt-1 w-full" :value="old('year')" placeholder="2020" />
                    </div>
                    <div>
                        <x-input-label for="chassis_number" value="Chassis Number" />
                        <x-text-input id="chassis_number" name="chassis_number" class="block mt-1 w-full" :value="old('chassis_number')" />
                    </div>
                    <div>
                        <x-input-label for="engine_number" value="Engine Number" />
                        <x-text-input id="engine_number" name="engine_number" class="block mt-1 w-full" :value="old('engine_number')" />
                    </div>
                    <div>
                        <x-input-label for="color" value="Color" />
                        <x-text-input id="color" name="color" class="block mt-1 w-full" :value="old('color')" />
                    </div>
                    <div>
                        <x-input-label for="mileage" value="Mileage (km)" />
                        <x-text-input id="mileage" name="mileage" type="number" class="block mt-1 w-full" :value="old('mileage')" required />
                        <x-input-error :messages="$errors->get('mileage')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="photo" value="Vehicle Photo" />
                        <input id="photo" name="photo" type="file" accept="image/*" class="block mt-1 w-full text-sm" />
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Specification</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="vehicle_type" value="Vehicle Type" />
                        <select id="vehicle_type" name="vehicle_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="car">Car</option>
                            <option value="bike">Bike</option>
                            <option value="van">Van</option>
                            <option value="truck">Truck</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="fuel_type" value="Fuel Type" />
                        <select id="fuel_type" name="fuel_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="petrol">Petrol</option>
                            <option value="diesel">Diesel</option>
                            <option value="hybrid">Hybrid</option>
                            <option value="electric">Electric</option>
                            <option value="cng">CNG</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="transmission" value="Transmission" />
                        <select id="transmission" name="transmission" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Ownership & Service Planning</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="purchase_date" value="Purchase Date" />
                        <x-text-input id="purchase_date" name="purchase_date" type="date" class="block mt-1 w-full" :value="old('purchase_date')" />
                    </div>
                    <div>
                        <x-input-label for="insurance_expiry" value="Insurance Expiry" />
                        <x-text-input id="insurance_expiry" name="insurance_expiry" type="date" class="block mt-1 w-full" :value="old('insurance_expiry')" />
                    </div>
                    <div></div>
                    <div>
                        <x-input-label for="next_service_due_date" value="Next Service Due (Date)" />
                        <x-text-input id="next_service_due_date" name="next_service_due_date" type="date" class="block mt-1 w-full" :value="old('next_service_due_date')" />
                    </div>
                    <div>
                        <x-input-label for="next_service_due_mileage" value="Next Service Due (Mileage)" />
                        <x-text-input id="next_service_due_mileage" name="next_service_due_mileage" type="number" class="block mt-1 w-full" :value="old('next_service_due_mileage')" />
                    </div>
                </div>
                <div class="mt-4">
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.vehicles.index') }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Register Vehicle</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
