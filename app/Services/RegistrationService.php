<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\CountryCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates user accounts from validated registration input.
 *
 * Responsibilities (Prompt 2A §4, §6, §30):
 *  - persist the account with system-generated UUID + status
 *  - normalize mobile country code / number
 *  - store consent timestamps + versions (terms/privacy separate)
 *  - generate a unique referral code for the new user
 *  - optionally link a referrer by referral code
 *
 * The caller must pass already-validated data (see RegisterRequest).
 */
class RegistrationService
{
    /**
     * @param array{
     *     full_name: string,
     *     email: string,
     *     mobile_country_code: string,
     *     mobile_number: string,
     *     preferred_language?: ?string,
     *     password: string,
     *     marketing_consent: bool,
     *     referred_by_code?: ?string,
     * } $data Validated registration payload.
     * @param string|null $registrationSource Logical source marker (e.g. 'web').
     */
    public function register(array $data, ?string $registrationSource = 'web'): User
    {
        return DB::transaction(function () use ($data, $registrationSource): User {
            $user = new User;

            $user->full_name = trim($data['full_name']);
            $user->syncNameParts();

            $user->email = strtolower(trim($data['email']));

            // Mobile stored separately: dialing code without "+" is normalized
            // to "+XXX" form; digits are kept as-is after stripping separators.
            $user->mobile_country_code = CountryCodes::normalize($data['mobile_country_code'] ?? null) ?? '+91';
            $user->mobile_number = preg_replace('/[\s\-()]/', '', (string) ($data['mobile_number'] ?? ''));

            if (! empty($data['preferred_language'])) {
                $user->preferred_language = $data['preferred_language'];
            }

            $user->password = Hash::make($data['password']);

            $user->registration_source = $registrationSource;

            // Consent timestamps + versions (§4).
            $user->applyConsents((bool) $data['marketing_consent']);

            // Referrer linkage happens before save so it lands in one insert.
            if (! empty($data['referred_by_code'])) {
                $referrer = User::query()
                    ->where('referral_code', strtoupper(trim($data['referred_by_code'])))
                    ->first();

                if ($referrer !== null) {
                    $user->referred_by_user_id = $referrer->id;
                }
            }

            $user->save();

            // Unique personal referral code for the new user.
            $user->forceFill(['referral_code' => self::generateReferralCode()])->save();

            return $user;
        });
    }

    private static function generateReferralCode(): string
    {
        return User::generateReferralCode();
    }
}