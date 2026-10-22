<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\Webhook;
use App\Services\Webhook\WebhookFrameworkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_signature_within_replay_window_is_accepted(): void
    {
        $service = app(WebhookFrameworkService::class);
        $timestamp = (string) time();
        $body = '{"ok":true}';
        $secret = 'tenant-secret';
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        $this->assertTrue($service->verifyInbound($secret, $body, $signature, $timestamp));
    }

    public function test_replayed_old_timestamp_is_rejected(): void
    {
        $service = app(WebhookFrameworkService::class);
        $timestamp = (string) (time() - 10_000);
        $body = '{"ok":true}';
        $secret = 'tenant-secret';
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        $this->assertFalse($service->verifyInbound($secret, $body, $signature, $timestamp));
    }

    public function test_wrong_secret_is_rejected(): void
    {
        $service = app(WebhookFrameworkService::class);
        $timestamp = (string) time();
        $body = '{"ok":true}';
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'tenant-secret');

        $this->assertFalse($service->verifyInbound('other-secret', $body, $signature, $timestamp));
    }

    public function test_webhook_row_is_tenant_owned(): void
    {
        $tenant = Tenant::query()->create(['company_name' => 'Hook Co']);
        $hook = Webhook::query()->create([
            'tenant_id' => $tenant->id,
            'url' => 'https://hooks.example.test',
            'events' => ['order.paid'],
            'secret' => 'abc',
            'is_active' => true,
        ]);

        $this->assertSame($tenant->id, (int) $hook->tenant_id);
    }
}
