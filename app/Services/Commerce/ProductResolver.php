<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\SubscriptionPlan;
use InvalidArgumentException;

/**
 * Phase 5 (§3): Product abstraction.
 *
 * Resolves a purchasable product from its type + id and returns the
 * server-side authoritative price snapshot. Never trust frontend prices.
 *
 * Supported: course, plan. Future: bundle, membership, digital product —
 * add a case here without redesigning callers.
 */
class ProductResolver
{
    public const TYPE_COURSE = 'course';

    public const TYPE_PLAN = 'plan';

    public const TYPES = [self::TYPE_COURSE, self::TYPE_PLAN];

    /**
     * @return array{name:string,description:?string,unit_price:float,currency:string,purchasable:Course|SubscriptionPlan}
     */
    public function resolve(string $type, int|string $id, string $currency): array
    {
        return match ($type) {
            self::TYPE_COURSE => $this->resolveCourse($id, $currency),
            self::TYPE_PLAN => $this->resolvePlan($id, $currency),
            default => throw new InvalidArgumentException("Unsupported product type [{$type}]."),
        };
    }

    private function resolveCourse(int|string $id, string $currency): array
    {
        /** @var Course|null $course */
        $course = Course::query()->published()
            ->when(is_numeric($id), fn ($q) => $q->whereKey((int) $id), fn ($q) => $q->where('uuid', (string) $id))
            ->first();

        if (! $course || $course->purchase_mode === 'plan_only') {
            throw new InvalidArgumentException('This course is not available for standalone purchase.');
        }

        $price = $course->effectivePrice($currency);

        if ($price === null) {
            throw new InvalidArgumentException("This course is not priced in [{$currency}].");
        }

        return [
            'name' => $course->title,
            'description' => $course->short_description ?? null,
            'unit_price' => $price,
            'currency' => strtolower($currency),
            'purchasable' => $course,
        ];
    }

    private function resolvePlan(int|string $id, string $currency): array
    {
        /** @var SubscriptionPlan|null $plan */
        $plan = SubscriptionPlan::query()->purchasable()
            ->when(is_numeric($id), fn ($q) => $q->whereKey((int) $id), fn ($q) => $q->where('uuid', (string) $id))
            ->first();

        if (! $plan) {
            throw new InvalidArgumentException('This subscription plan is not available for purchase.');
        }

        $price = $plan->effectivePrice($currency);

        if ($price === null) {
            throw new InvalidArgumentException("This plan is not priced in [{$currency}].");
        }

        return [
            'name' => $plan->name,
            'description' => $plan->short_description,
            'unit_price' => $price,
            'currency' => strtolower($currency),
            'purchasable' => $plan,
        ];
    }
}