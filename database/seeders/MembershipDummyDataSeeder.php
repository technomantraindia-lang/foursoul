<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembershipDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            'begin' => SubscriptionPlan::where('slug', 'begin')->first(),
            'commit' => SubscriptionPlan::where('slug', 'commit')->first(),
            'transform' => SubscriptionPlan::where('slug', 'transform')->first(),
            'sadhana' => SubscriptionPlan::where('slug', 'sadhana')->first(),
        ];

        $members = [
            [
                'name' => 'Dhruv Parekh',
                'email' => 'dhruv1@gmail.com',
                'plan_slug' => 'sadhana',
                'currency' => 'INR',
                'amount' => 6499.00,
                'gateway' => 'razorpay_upi',
                'months_ago' => 0,
            ],
            [
                'name' => 'Dhruv Parekh (Secondary)',
                'email' => 'dhruv@gmail.com',
                'plan_slug' => 'transform',
                'currency' => 'INR',
                'amount' => 3499.00,
                'gateway' => 'razorpay_netbanking',
                'months_ago' => 1,
            ],
            [
                'name' => 'Vivek Patel',
                'email' => 'vive@gmail.com',
                'plan_slug' => 'commit',
                'currency' => 'AED',
                'amount' => 299.00,
                'gateway' => 'stripe_gcc',
                'months_ago' => 0,
            ],
            [
                'name' => 'Demo Student',
                'email' => 'student@foursouls.test',
                'plan_slug' => 'begin',
                'currency' => 'USD',
                'amount' => 79.00,
                'gateway' => 'stripe_card',
                'months_ago' => 0,
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@gmail.com',
                'plan_slug' => 'sadhana',
                'currency' => 'INR',
                'amount' => 6499.00,
                'gateway' => 'razorpay_card',
                'months_ago' => 0,
            ],
            [
                'name' => 'Aarav Mehta',
                'email' => 'aarav.mehta@gmail.com',
                'plan_slug' => 'transform',
                'currency' => 'INR',
                'amount' => 3499.00,
                'gateway' => 'razorpay_upi',
                'months_ago' => 1,
            ],
            [
                'name' => 'Rhea Patel',
                'email' => 'rhea.patel@gmail.com',
                'plan_slug' => 'commit',
                'currency' => 'INR',
                'amount' => 1899.00,
                'gateway' => 'razorpay_upi',
                'months_ago' => 2,
            ],
            [
                'name' => 'Karan Malhotra',
                'email' => 'karan.m@gmail.com',
                'plan_slug' => 'begin',
                'currency' => 'INR',
                'amount' => 1399.00,
                'gateway' => 'razorpay_netbanking',
                'months_ago' => 0,
            ],
            [
                'name' => 'Fatima Al-Zahra',
                'email' => 'fatima.z@gmail.com',
                'plan_slug' => 'sadhana',
                'currency' => 'AED',
                'amount' => 999.00,
                'gateway' => 'stripe_gcc',
                'months_ago' => 1,
            ],
            [
                'name' => 'Sarah Jenkins',
                'email' => 'sarah.j@gmail.com',
                'plan_slug' => 'transform',
                'currency' => 'USD',
                'amount' => 189.00,
                'gateway' => 'stripe_card',
                'months_ago' => 0,
            ],
        ];

        foreach ($members as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'full_name' => $data['name'],
                    'first_name' => explode(' ', $data['name'])[0] ?? $data['name'],
                    'last_name' => explode(' ', $data['name'])[1] ?? '',
                    'password' => Hash::make('password'),
                    'status' => UserStatus::Active,
                    'email_verified_at' => now()->subDays(10),
                    'marketing_consent' => true,
                    'preferred_currency' => $data['currency'],
                ]
            );

            $plan = $plans[$data['plan_slug']] ?? null;
            if (!$plan) continue;

            $startDate = now()->subMonths($data['months_ago'])->subDays(rand(1, 10));
            $durationDays = $plan->duration_days ?? 30;
            $expiresDate = (clone $startDate)->addDays($durationDays);

            // 1. Order
            $order = Order::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(substr(md5($user->email . $plan->id), 0, 8)),
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'currency' => $data['currency'],
                    'subtotal' => $data['amount'],
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $data['amount'],
                    'status' => OrderStatus::Paid,
                    'paid_at' => $startDate,
                    'expires_at' => $expiresDate,
                ]
            );

            // 2. Payment
            Payment::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'gateway' => $data['gateway'],
                    'gateway_reference' => 'pay_' . Str::random(16),
                    'status' => PaymentStatus::Succeeded->value,
                    'currency' => $data['currency'],
                    'amount' => $data['amount'],
                    'paid_at' => $startDate,
                ]
            );

            // 3. User Subscription
            UserSubscription::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'status' => SubscriptionStatus::Active,
                    'currency' => $data['currency'],
                    'unit_price' => $data['amount'],
                    'total_amount' => $data['amount'],
                    'started_at' => $startDate,
                    'current_period_start' => $startDate,
                    'current_period_end' => $expiresDate,
                    'expires_at' => $expiresDate,
                    'activation_source' => 'direct_payment',
                    'source_reference' => $order->order_number,
                ]
            );
        }
    }
}
