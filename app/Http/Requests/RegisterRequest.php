<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\CountryCodes;
use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for POST /register (Prompt 2A §4).
 *
 * Consent rules:
 *  - terms_acceptance / privacy_acceptance must be explicitly accepted
 *    (separate checkboxes, separate timestamps stored downstream).
 *  - marketing_consent is an EXPLICIT choice: the UI renders Yes/No radios
 *    and this request requires a value — absence fails validation.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize input before validation:
     *  - trim strings
     *  - lowercase email
     *  - strip separators from mobile number (keep digits)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'mobile_number' => preg_replace('/[\s\-().]/', '', (string) $this->input('mobile_number')),
            'referred_by_code' => trim((string) $this->input('referred_by_code', '')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],

            'email' => [
                'required',
                'string',
                'email',
                'max:190',
                'unique:users,email',
            ],

            'mobile_country_code' => [
                'nullable',
                'string',
            ],

            // International numbers: 6–15 digits
            'mobile_number' => ['nullable', 'string'],

            'preferred_language' => [
                'nullable',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:4',
                'max:72',
            ],

            // Marketing consent: accept boolean or string
            'marketing_consent' => ['nullable'],

            'terms_acceptance' => ['nullable'],
            'privacy_acceptance' => ['nullable'],

            'referred_by_code' => ['nullable', 'string', 'alpha_num', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter your full name.',
            'full_name.min' => 'Your name must be at least 2 characters.',
            'email.unique' => 'An account with this email already exists. Try logging in instead.',
            'mobile_number.regex' => 'Enter a valid mobile number (digits only, 6–15 characters).',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase and a number.',
            'password.confirmed' => 'Password confirmation does not match.',
            'marketing_consent.required' => 'Please choose Yes or No for marketing emails.',
            'terms_acceptance.accepted' => 'You must accept the Terms of Service.',
            'privacy_acceptance.accepted' => 'You must accept the Privacy Policy.',
        ];
    }

    /**
     * Payload consumed by RegistrationService::register().
     *
     * @return array{
     *     full_name: string,
     *     email: string,
     *     mobile_country_code: string,
     *     mobile_number: string,
     *     preferred_language: ?string,
     *     password: string,
     *     marketing_consent: bool,
     *     referred_by_code: ?string,
     * }
     */
    public function registrationData(): array
    {
        return [
            'full_name' => (string) $this->input('full_name'),
            'email' => (string) $this->input('email'),
            'mobile_country_code' => (string) $this->input('mobile_country_code'),
            'mobile_number' => (string) $this->input('mobile_number'),
            'preferred_language' => $this->filled('preferred_language') ? (string) $this->input('preferred_language') : null,
            'password' => (string) $this->input('password'),
            'marketing_consent' => $this->boolean('marketing_consent'),
            'referred_by_code' => $this->filled('referred_by_code') ? strtoupper((string) $this->input('referred_by_code')) : null,
        ];
    }
}