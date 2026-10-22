<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Support\CountryCodes;
use App\Support\Locales;
use App\Support\Timezones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for profile updates (§17–18).
 *
 * Email is intentionally absent — it is read-only until a secure
 * email-change verification flow exists. Avatar uploads are validated
 * for MIME type, extension and size (§18).
 */
final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Users may only update their own profile; the controller acts on
        // $request->user() exclusively, so route manipulation cannot edit
        // another user (§29).
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile_country_code')) {
            $this->merge([
                'mobile_country_code' => CountryCodes::normalize(
                    (string) $this->input('mobile_country_code')
                ),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],

            'mobile_country_code' => [
                'nullable',
                'string',
                'max:8',
                Rule::in(array_values(CountryCodes::all())),
            ],
            'mobile_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]{6,15}$/',
                Rule::requiredIf(fn () => $this->filled('mobile_country_code')),
            ],

            'preferred_language' => [
                'nullable',
                'string',
                Rule::in(array_keys(Locales::all())),
            ],

            'timezone' => [
                'nullable',
                'string',
                'max:64',
                Rule::in(Timezones::all()),
            ],

            // §18 — secure avatar upload constraints.
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2 MB
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => __('Please enter your full name.'),
            'mobile_number.regex' => __('Mobile number must contain 6–15 digits only.'),
            'avatar.image' => __('The avatar must be an image file.'),
            'avatar.mimes' => __('Allowed avatar types: JPG, PNG or WebP.'),
            'avatar.max' => __('The avatar must not exceed 2 MB.'),
        ];
    }
}