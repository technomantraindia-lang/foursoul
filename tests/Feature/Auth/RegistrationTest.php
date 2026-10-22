<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create your account', escape: false);
    }

    public function test_registration_works_with_valid_data(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertRedirect('/verify-email');
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertSame('John Doe', $user->full_name);
        $this->assertSame('+971', $user->mobile_country_code);
        $this->assertSame('501234567', $user->mobile_number);
        $this->assertSame('en', $user->preferred_language);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertNotSame($user->password, 'Password123!');
        $this->assertTrue($user->marketing_consent);
        $this->assertNotNull($user->marketing_consent_at);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertNotEmpty($user->uuid);
    }

    public function test_uuid_is_not_accepted_from_registration_request(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
            'uuid' => 'malicious-uuid',
        ]);

        $response->assertRedirect('/verify-email');
        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertNotSame('malicious-uuid', $user->uuid);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'existing@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'existing@example.com')->count());
    }

    public function test_password_confirmation_is_required(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'different',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame(0, User::count());
    }

    public function test_terms_acceptance_is_required(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '0',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertSessionHasErrors('terms_acceptance');
        $this->assertSame(0, User::count());
    }

    public function test_privacy_acceptance_is_required(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '0',
            'marketing_consent' => '1',
        ]);

        $response->assertSessionHasErrors('privacy_acceptance');
        $this->assertSame(0, User::count());
    }

    public function test_marketing_consent_choice_is_required(): void
    {
        // Marketing consent is an explicit Yes/No choice: omitting the field
        // entirely must fail validation (no silent default).
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
        ]);

        $response->assertSessionHasErrors('marketing_consent');
        $this->assertSame(0, User::count());
    }

    public function test_marketing_decline_is_stored_as_explicit_false(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '0',
        ]);

        $response->assertRedirect('/verify-email');
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertFalse($user->marketing_consent);
        $this->assertNull($user->marketing_consent_at);
        // Terms and privacy remain separate, always-stamped decisions.
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
    }

    public function test_terms_and_privacy_timestamps_are_stored(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertRedirect('/verify-email');
        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertNotNull($user->terms_version);
        $this->assertNotNull($user->privacy_version);
    }

    public function test_marketing_consent_is_stored(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
            'preferred_language' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_acceptance' => '1',
            'privacy_acceptance' => '1',
            'marketing_consent' => '1',
        ]);

        $response->assertRedirect('/verify-email');
        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertTrue($user->marketing_consent);
        $this->assertNotNull($user->marketing_consent_at);
    }
}