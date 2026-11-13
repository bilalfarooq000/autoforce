<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:users,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:vehicles,registration_number'],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'string', 'max:4'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'mileage' => ['required', 'integer', 'min:0'],
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
