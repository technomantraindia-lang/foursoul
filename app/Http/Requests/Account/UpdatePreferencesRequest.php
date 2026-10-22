<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Enums\UserStatus;
use App\Support\Currency;
use App\Support\Locales;
use App\Support\Timezones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * §20 — Preferences update validation.
 *
 * Currency options come exclusively from the existing Currency enum
 * (AED / USD / INR). No second independent currency list exists.
 */
class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // §29 — a user may only ever update their own preferences; the
        // controller resolves the user from the authenticated session,
        // never from request input.
        return $this->user() !== null
            && $this->user()->status === UserStatus::Active;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preferred_currency' => strtoupper(trim((string) $this->input('preferred_currency'))),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'preferred_language' => ['required', 'string', Rule::in(array_keys(Locales::all()))],
            'preferred_currency' => ['required', 'string', Rule::in(Currency::codes())],
            'timezone' => ['required', 'string', Rule::in(Timezones::all())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferred_language.in' => 'Please choose a supported language.',
            'preferred_currency.in' => 'Please choose a supported currency (AED, USD or INR).',
            'timezone.in' => 'Please choose a valid timezone.',
        ];
    }
}