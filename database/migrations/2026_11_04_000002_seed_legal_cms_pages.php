<?php

declare(strict_types=1);

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $pages = [
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'description' => 'How FourSouls handles information used to operate your account and wellness experience.',
                'html' => '<h2>Privacy at FourSouls</h2><p>This starter page is managed from the admin panel. Replace this copy with your approved Privacy Policy before launch.</p><h3>Information we use</h3><p>FourSouls may use information needed to create accounts, provide classes and memberships, process requests, and support your relationship with us.</p><h3>Contact</h3><p>Contact the FourSouls support team with privacy questions or requests about your information.</p>',
            ],
            'terms-and-conditions' => [
                'title' => 'Terms & Conditions',
                'description' => 'The terms that govern your use of FourSouls services, classes, and membership features.',
                'html' => '<h2>Using FourSouls</h2><p>This starter page is managed from the admin panel. Replace this copy with your approved Terms & Conditions before launch.</p><h3>Our services</h3><p>FourSouls provides wellness content, live classes, courses, and membership features for personal use. Please use the service responsibly and follow any class-specific guidance.</p><h3>Contact</h3><p>Contact the FourSouls support team if you have questions about these terms.</p>',
            ],
        ];

        foreach ($pages as $slug => $page) {
            Page::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'uuid' => (string) Str::uuid(),
                    'title' => $page['title'],
                    'seo_title' => $page['title'] . ' | FourSouls',
                    'seo_description' => $page['description'],
                    'layout' => 'default',
                    'visibility' => 'public',
                    'status' => PageStatus::Published->value,
                    'published_at' => now(),
                    'content' => ['html' => $page['html']],
                    'metadata' => ['system_key' => $slug],
                ],
            );
        }
    }

    public function down(): void
    {
        Page::query()
            ->whereIn('slug', ['privacy-policy', 'terms-and-conditions'])
            ->delete();
    }
};
