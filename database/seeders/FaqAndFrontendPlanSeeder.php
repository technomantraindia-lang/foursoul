<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlanBillingType;
use App\Enums\PlanCourseAccessType;
use App\Enums\PlanStatus;
use App\Models\Faq;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqAndFrontendPlanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. FAQs
        $faqs = [
            [
                'question' => 'How does the Live Class schedule work across different time zones?',
                'answer' => 'We host interactive morning and evening live sessions conducted by master teachers every single day. Whether you are in India (IST), the UAE (GST), the UK (GMT), or North America (EST), multiple live batch slots are available every day so you never miss a class.',
                'category' => 'classes',
                'sort_order' => 1,
            ],
            [
                'question' => 'What is the Plan-Pause flexibility and how do I freeze my subscription?',
                'answer' => 'If you travel, fall ill, or need a personal break, you can pause your membership directly inside your student dashboard. Your remaining days freeze and do not expire. Commit plans have 7 pause days, Transform plans have 14 pause days, and Sadhana yearly members receive up to 45 pause days.',
                'category' => 'membership',
                'sort_order' => 2,
            ],
            [
                'question' => 'Can I upgrade or switch my plan later?',
                'answer' => 'Yes, absolutely. You can upgrade from a 1-month or 3-month plan to Transform or Sadhana at any time. Any remaining days on your current plan will be credited towards your upgrade.',
                'category' => 'membership',
                'sort_order' => 3,
            ],
            [
                'question' => 'What equipment or previous experience do I need to begin?',
                'answer' => 'Zero previous experience is required! All you need is a yoga mat and a device (phone, laptop, tablet, or smart TV). Our teachers provide real-time modifications for beginners as well as advanced posture variations.',
                'category' => 'general',
                'sort_order' => 4,
            ],
            [
                'question' => 'What is the 7-day satisfaction money-back guarantee?',
                'answer' => 'We want you to practice with complete peace of mind. Experience our live classes, teacher corrections, and on-demand library for 7 full days. If FourSouls is not the right fit for your orbit, email us within 7 days for a 100% full refund with no questions asked.',
                'category' => 'membership',
                'sort_order' => 5,
            ],
        ];

        foreach ($faqs as $item) {
            Faq::updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'category' => $item['category'],
                    'sort_order' => $item['sort_order'],
                    'is_published' => true,
                ]
            );
        }

        // 2. Official 4 Plans
        $plans = [
            [
                'name' => 'BEGIN',
                'slug' => 'begin',
                'billing_type' => PlanBillingType::Monthly,
                'duration_days' => 30,
                'price_inr' => 1399,
                'price_aed' => 149,
                'price_usd' => 79,
                'is_featured' => false,
                'sort_order' => 1,
                'short_description' => 'Full price · cancel anytime',
                'metadata' => [
                    'duration' => '1 MONTH',
                    'strikethrough_inr' => null,
                    'strikethrough_aed' => null,
                    'strikethrough_usd' => null,
                    'per_month_inr' => 'just Rs. 1,399 / month',
                    'per_month_aed' => 'just AED 149 / month',
                    'per_month_usd' => 'just $79 / month',
                    'savings' => null,
                    'note' => 'Full price · cancel anytime',
                    'pause_days' => 0,
                    'features' => [
                        'Unlimited Daily Live Morning & Evening Classes',
                        'Full On-Demand Yoga & Pranayama Library',
                        'Live Alignment & Posture Correction',
                        'Private Community Hub Access',
                    ],
                ],
            ],
            [
                'name' => 'COMMIT',
                'slug' => 'commit',
                'billing_type' => PlanBillingType::Quarterly,
                'duration_days' => 90,
                'price_inr' => 1899,
                'price_aed' => 299,
                'price_usd' => 99,
                'is_featured' => false,
                'sort_order' => 2,
                'short_description' => 'SAVE Rs. 2,298 · 55% OFF',
                'metadata' => [
                    'duration' => '3 MONTHS',
                    'strikethrough_inr' => 'Rs. 4,197',
                    'strikethrough_aed' => 'AED 447',
                    'strikethrough_usd' => '$237',
                    'per_month_inr' => 'just Rs. 633 / month',
                    'per_month_aed' => 'just AED 100 / month',
                    'per_month_usd' => 'just $33 / month',
                    'savings' => '55% OFF',
                    'note' => 'SAVE Rs. 2,298 · 55% OFF',
                    'pause_days' => 7,
                    'features' => [
                        'Everything in 1-Month Begin',
                        '90-Day Habit & Consistency Tracker',
                        'Quarterly Mobility Assessment Check-in',
                        'Priority WhatsApp Community Support',
                    ],
                ],
            ],
            [
                'name' => 'TRANSFORM',
                'slug' => 'transform',
                'billing_type' => PlanBillingType::HalfYearly,
                'duration_days' => 180,
                'price_inr' => 3499,
                'price_aed' => 549,
                'price_usd' => 189,
                'is_featured' => false,
                'sort_order' => 3,
                'short_description' => 'SAVE Rs. 4,895 · 58% OFF',
                'metadata' => [
                    'duration' => '6 MONTHS',
                    'strikethrough_inr' => 'Rs. 8,394',
                    'strikethrough_aed' => 'AED 894',
                    'strikethrough_usd' => '$474',
                    'per_month_inr' => 'just Rs. 583 / month',
                    'per_month_aed' => 'just AED 92 / month',
                    'per_month_usd' => 'just $32 / month',
                    'savings' => '58% OFF',
                    'note' => 'SAVE Rs. 4,895 · 58% OFF',
                    'pause_days' => 14,
                    'features' => [
                        'Everything in 3-Month Commit',
                        'Therapeutic Spine & Joint Masterclass',
                        '1 Complimentary Alignment Review Session',
                        'Up to 14 Days Plan-Pause Flexibility',
                    ],
                ],
            ],
            [
                'name' => 'SADHANA',
                'slug' => 'sadhana',
                'billing_type' => PlanBillingType::Yearly,
                'duration_days' => 365,
                'price_inr' => 6499,
                'price_aed' => 999,
                'price_usd' => 349,
                'is_featured' => true,
                'sort_order' => 4,
                'short_description' => 'SAVE Rs. 10,289 · 61% OFF · Most Popular',
                'metadata' => [
                    'duration' => 'YEARLY',
                    'strikethrough_inr' => 'Rs. 16,788',
                    'strikethrough_aed' => 'AED 1,788',
                    'strikethrough_usd' => '$948',
                    'per_month_inr' => 'just Rs. 542 / month',
                    'per_month_aed' => 'just AED 83 / month',
                    'per_month_usd' => 'just $29 / month',
                    'savings' => '61% OFF',
                    'note' => 'SAVE Rs. 10,289 · 61% OFF',
                    'pause_days' => 45,
                    'features' => [
                        'Everything in 6-Month Transform',
                        'Complete Annual Yogic Transformation',
                        '2x Private 1-on-1 Guidance Sessions',
                        'Up to 45 Days Plan-Pause Flexibility',
                        'VIP Weekend Masterclasses & Sound Baths',
                    ],
                ],
            ],
        ];

        foreach ($plans as $p) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $p['name'],
                    'status' => PlanStatus::Active,
                    'course_access_type' => PlanCourseAccessType::AllCourses,
                    'billing_type' => $p['billing_type'],
                    'duration_days' => $p['duration_days'],
                    'price_inr' => $p['price_inr'],
                    'price_aed' => $p['price_aed'],
                    'price_usd' => $p['price_usd'],
                    'is_featured' => $p['is_featured'],
                    'sort_order' => $p['sort_order'],
                    'short_description' => $p['short_description'],
                    'live_sessions_enabled' => true,
                    'community_access_enabled' => true,
                    'published_at' => now(),
                    'metadata' => $p['metadata'],
                ]
            );
        }
    }
}
