<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServiceRequestRequest extends FormRequest
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
            'project_type' => ['nullable', 'string', 'max:100'],
            'budget_range' => ['nullable', 'string', 'max:100'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'expected_timeline' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'preferred_contact_method' => ['nullable', 'string', 'max:100'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'subject' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:5120',
                'mimes:pdf,doc,docx,png,jpg,jpeg,zip',
            ],
            'website' => ['nullable', 'max:0'], // honeypot
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('website')) {
                $validator->errors()->add('website', 'Spam detected.');
            }

            if (! $this->filled('service_id') && ! $this->filled('service_slug')) {
                // Optional for general consultation; required for service request routes that pass it.
            }
        });
    }

    public function messages(): array
    {
        return [
            'website.max' => 'Spam detected.',
        ];
    }
}
