<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EntitlementStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\UserSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Marks expired subscriptions and locks their dependent access.
 *
 * History is preserved: subscriptions, entitlements, enrollments and any
 * lesson progress are never deleted — access state simply becomes unavailable.
 */
class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire subscriptions past their end date and lock dependent entitlements/enrollments (history preserved)';

    public function handle(): int
    {
        $now = now();

        $expired = UserSubscription::query()
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No subscriptions to expire.');

            return self::SUCCESS;
        }

        $count = DB::transaction(function () use ($expired, $now) {
            $n = 0;

            foreach ($expired as $subscription) {
                $subscription->update([
                    'status' => SubscriptionStatus::Expired->value,
                    'ended_at' => $subscription->ended_at ?? $now,
                ]);

                // Lock entitlements without deleting them.
                $subscription->entitlements()
                    ->whereIn('status', [EntitlementStatus::Active->value, EntitlementStatus::Trial->value])
                    ->update(['status' => EntitlementStatus::Expired->value]);

                // Lock enrollments; progress/history rows remain untouched.
                $subscription->enrollments()
                    ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Trial->value])
                    ->update(['status' => EnrollmentStatus::Expired->value]);

                $n++;
            }

            return $n;
        });

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}