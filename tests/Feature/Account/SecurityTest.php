<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/account/security');

        $response->assertStatus(200);
    }

    public function test_password_can_be_changed_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPass123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => 'CurrentPass123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertRedirect('/account/security');
        $response->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
        $this->assertFalse(Hash::check('CurrentPass123!', $user->fresh()->password));
    }

    public function test_change_password_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPass123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => '',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('CurrentPass123!', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPass123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => 'WrongPassword999!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('CurrentPass123!', $user->fresh()->password));
    }

    public function test_change_password_requires_confirmation_match(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPass123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => 'CurrentPass123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'Different789!',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue(Hash::check('CurrentPass123!', $user->fresh()->password));
    }

    public function test_new_password_must_differ_from_current(): void
    {
        $user = User::factory()->create([
            'password' => 'SamePassword123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => 'SamePassword123!',
            'password' => 'SamePassword123!',
            'password_confirmation' => 'SamePassword123!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_guest_cannot_access_security_page(): void
    {
        $response = $this->get('/account/security');

        $response->assertRedirect('/login');
    }
}