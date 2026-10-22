<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\User;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_preferences_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/account/preferences');

        $response->assertStatus(200);
    }

    public function test_aed_currency_accepted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'AED',
            'timezone' => 'Asia/Dubai',
        ]);

        $response->assertRedirect('/account/preferences');
        $response->assertSessionHas('status');

        $this->assertSame(Currency::AED, $user->fresh()->preferred_currency);
    }

    public function test_usd_currency_accepted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $response->assertRedirect('/account/preferences');
        $response->assertSessionHas('status');

        $this->assertSame(Currency::USD, $user->fresh()->preferred_currency);
    }

    public function test_inr_currency_accepted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $response->assertRedirect('/account/preferences');
        $response->assertSessionHas('status');

        $this->assertSame(Currency::INR, $user->fresh()->preferred_currency);
    }

    public function test_unsupported_currency_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'GBP',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['preferred_currency']);
        $this->assertSame(Currency::AED, $user->fresh()->preferred_currency);
    }

    public function test_lowercase_currency_code_is_normalized(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'usd',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(Currency::USD, $user->fresh()->preferred_currency);
    }

    public function test_unsupported_language_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/preferences', [
            'preferred_language' => 'de',
            'preferred_currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['preferred_language']);
    }

    public function test_guest_cannot_update_preferences(): void
    {
        $response = $this->put('/account/preferences', [
            'preferred_language' => 'en',
            'preferred_currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $response->assertRedirect('/login');
    }
}