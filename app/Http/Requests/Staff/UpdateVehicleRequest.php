<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('vehicles', 'registration_number')->ignore($this->route('vehicle'))],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'mileage' => ['required', 'integer', 'min:0', function ($attribute, $value, $fail) {
                $current = $this->route('vehicle')?->mileage;
                if ($current !== null && $value < $current) {
                    $fail("Mileage can't go backwards — last recorded was {$current} km.");
                }
            }],
            'color' => ['nullable', 'string', 'max:50'],
            'vehicle_type' => ['required', 'in:car,bike,van,truck,other'],
            'fuel_type' => ['required', 'in:petrol,diesel,hybrid,electric,cng'],
            'transmission' => ['required', 'in:manual,automatic'],
            'purchase_date' => ['nullable', 'date'],
            'insurance_expiry' => ['nullable', 'date'],
            'next_service_due_date' => ['nullable', 'date'],
            'next_service_due_mileage' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
