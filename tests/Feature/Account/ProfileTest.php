<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/account/profile');

        $response->assertStatus(200);
        $response->assertSee($user->full_name);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Original Name',
            'mobile_country_code' => '+971',
            'mobile_number' => '501234567',
        ]);

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => 'Updated Name',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'preferred_language' => 'en',
            'timezone' => 'Asia/Dubai',
        ]);

        $response->assertRedirect('/account/profile');
        $response->assertSessionHas('status');

        $this->assertSame('Updated Name', $user->fresh()->full_name);
        $this->assertSame('+91', $user->fresh()->mobile_country_code);
        $this->assertSame('9876543210', $user->fresh()->mobile_number);
        $this->assertSame('Asia/Dubai', $user->fresh()->timezone);
    }

    public function test_profile_update_requires_full_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => '',
            'preferred_language' => 'en',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['full_name']);
    }

    public function test_profile_update_rejects_invalid_mobile_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => 'Valid Name',
            'mobile_country_code' => '+971',
            'mobile_number' => 'abc123',
            'preferred_language' => 'en',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['mobile_number']);
    }

    public function test_profile_update_rejects_unsupported_language(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => 'Valid Name',
            'preferred_language' => 'fr',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['preferred_language']);
    }

    public function test_profile_update_rejects_unknown_timezone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => 'Valid Name',
            'preferred_language' => 'en',
            'timezone' => 'Mars/Olympus_Mons',
        ]);

        $response->assertSessionHasErrors(['timezone']);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get('/account/profile');

        $response->assertRedirect('/login');
    }

    public function test_avatar_upload_stores_file_and_updates_path(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/account/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.png', 300, 300),
        ]);

        $response->assertRedirect('/account/profile');

        $path = (string) $user->fresh()->avatar_path;

        $this->assertNotSame('', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/account/profile/avatar', [
            'avatar' => UploadedFile::fake()->create('malicious.php', 100, 'application/x-php'),
        ]);

        $response->assertSessionHasErrors(['avatar']);
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_avatar_upload_rejects_oversized_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // 3 MB image exceeds the 2 MB limit.
        $response = $this->actingAs($user)->post('/account/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('huge.jpg')->size(3072),
        ]);

        $response->assertSessionHasErrors(['avatar']);
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_avatar_upload_replaces_previous_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_path' => 'avatars/old-avatar.png',
        ]);
        Storage::disk('public')->put('avatars/old-avatar.png', 'old');

        $response = $this->actingAs($user)->post('/account/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('new-avatar.png'),
        ]);

        $response->assertRedirect('/account/profile');

        Storage::disk('public')->assertMissing('avatars/old-avatar.png');
        Storage::disk('public')->assertExists((string) $user->fresh()->avatar_path);
    }
}