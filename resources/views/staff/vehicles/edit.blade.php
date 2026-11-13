<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ $vehicle->registration_number }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.vehicles.update', $vehicle) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="registration_number" value="Registration Number" />
                    <x-text-input id="registration_number" name="registration_number" class="block mt-1 w-full" :value="old('registration_number', $vehicle->registration_number)" required />
                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="make" value="Make" />
                    <x-text-input id="make" name="make" class="block mt-1 w-full" :value="old('make', $vehicle->make)" required />
                </div>
                <div>
                    <x-input-label for="model" value="Model" />
                    <x-text-input id="model" name="model" class="block mt-1 w-full" :value="old('model', $vehicle->model)" required />
                </div>
                <div>
                    <x-input-label for="year" value="Year" />
                    <x-text-input id="year" name="year" class="block mt-1 w-full" :value="old('year', $vehicle->year)" />
                </div>
                <div>
                    <x-input-label for="chassis_number" value="Chassis Number" />
                    <x-text-input id="chassis_number" name="chassis_number" class="block mt-1 w-full" :value="old('chassis_number', $vehicle->chassis_number)" />
                </div>
                <div>
                    <x-input-label for="engine_number" value="Engine Number" />
                    <x-text-input id="engine_number" name="engine_number" class="block mt-1 w-full" :value="old('engine_number', $vehicle->engine_number)" />
                </div>
                <div>
                    <x-input-label for="color" value="Color" />
                    <x-text-input id="color" name="color" class="block mt-1 w-full" :value="old('color', $vehicle->color)" />
                </div>
                <div>
                    <x-input-label for="mileage" value="Mileage (km)" />
                    <x-text-input id="mileage" name="mileage" type="number" class="block mt-1 w-full" :value="old('mileage', $vehicle->mileage)" required />
                </div>
                <div>
                    <x-input-label for="photo" value="Vehicle Photo" />
                    <input id="photo" name="photo" type="file" accept="image/*" class="block mt-1 w-full text-sm" />
                </div>
                <div>
                    <x-input-label for="vehicle_type" value="Vehicle Type" />
                    <select id="vehicle_type" name="vehicle_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @foreach (['car', 'bike', 'van', 'truck', 'other'] as $type)
                            <option value="{{ $type }}" @selected($vehicle->vehicle_type === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="fuel_type" value="Fuel Type" />
                    <select id="fuel_type" name="fuel_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @foreach (['petrol', 'diesel', 'hybrid', 'electric', 'cng'] as $fuel)
                            <option value="{{ $fuel }}" @selected($vehicle->fuel_type === $fuel)>{{ ucfirst($fuel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="transmission" value="Transmission" />
                    <select id="transmission" name="transmission" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @foreach (['manual', 'automatic'] as $t)
                            <option value="{{ $t }}" @selected($vehicle->transmission === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="purchase_date" value="Purchase Date" />
                    <x-text-input id="purchase_date" name="purchase_date" type="date" class="block mt-1 w-full" :value="old('purchase_date', $vehicle->purchase_date?->format('Y-m-d'))" />
                </div>
                <div>
                    <x-input-label for="insurance_expiry" value="Insurance Expiry" />
                    <x-text-input id="insurance_expiry" name="insurance_expiry" type="date" class="block mt-1 w-full" :value="old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d'))" />
                </div>
                <div>
                    <x-input-label for="next_service_due_date" value="Next Service Due (Date)" />
                    <x-text-input id="next_service_due_date" name="next_service_due_date" type="date" class="block mt-1 w-full" :value="old('next_service_due_date', $vehicle->next_service_due_date?->format('Y-m-d'))" />
                </div>
                <div>
                    <x-input-label for="next_service_due_mileage" value="Next Service Due (Mileage)" />
                    <x-text-input id="next_service_due_mileage" name="next_service_due_mileage" type="number" class="block mt-1 w-full" :value="old('next_service_due_mileage', $vehicle->next_service_due_mileage)" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $vehicle->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.vehicles.show', $vehicle) }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
