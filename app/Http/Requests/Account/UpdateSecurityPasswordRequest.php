<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validation for the change-password form (§19).
 *
 * The current password is checked against the authenticated user's hash
 * in the controller/service layer — it is never revealed or logged.
 */
final class UpdateSecurityPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],

            'password' => [
                'required',
                'string',
                Password::min(10)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => __('Please enter your current password.'),
            'password.different' => __('The new password must be different from the current password.'),
            'password.confirmed' => __('The password confirmation does not match.'),
        ];
    }
}