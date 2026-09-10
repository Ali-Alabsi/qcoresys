<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'service_slug' => ['nullable', 'string', 'exists:services,slug'],
            'description' => ['required', 'string', 'max:5000'],
            'preferred_contact_method' => ['nullable', 'string', 'max:100'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('website')) {
                $validator->errors()->add('website', 'Spam detected.');
            }
        });
    }
}
