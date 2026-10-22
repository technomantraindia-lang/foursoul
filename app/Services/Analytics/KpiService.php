<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\BlogPost;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\QuizAttempt;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

/**
 * Central KPI engine. All KPI calculations are centralized here.
 */
class KpiService
{
    public function __construct(
        protected AnalyticsService $analytics,
    ) {}

    /**
     * @return array<string, float|int>
     */
    public function all(array $filters = []): array
    {
        return [
            'revenue' => $this->revenue($filters),
            'mrr' => $this->mrr(),
            'arr' => $this->arr(),
            'orders' => $this->orders($filters),
            'conversion_rate' => $this->conversionRate($filters),
            'active_students' => $this->activeStudents(),
            'active_courses' => $this->activeCourses(),
            'completion_percentage' => $this->completionPercentage($filters),
            'quiz_pass_percentage' => $this->quizPassPercentage($filters),
            'attendance_percentage' => $this->attendancePercentage($filters),
            'certificates_issued' => $this->certificatesIssued($filters),
            'average_rating' => $this->averageRating(),
            'blog_views' => $this->blogViews($filters),
        ];
    }

    public function revenue(array $filters = []): int
    {
        return (int) $this->analytics->applyDateRange(
            Payment::query()->where('status', PaymentStatus::Succeeded->value),
            'paid_at',
            $filters,
        )->sum('amount');
    }

    public function mrr(): float
    {
        // Active recurring subscriptions normalized to their monthly value.
        // The subscription price is the purchase-time snapshot, so analytics
        // remains correct even when an admin later changes a plan's price.
        $monthlyRevenue = UserSubscription::query()
            ->active()
            ->with('plan:id,billing_type')
            ->get()
            ->sum(function (UserSubscription $subscription): float {
                $price = (float) $subscription->unit_price;
                $billingType = $subscription->plan?->billing_type?->value;

                return match ($billingType) {
                    'quarterly' => $price / 3,
                    'half_yearly' => $price / 6,
                    'yearly' => $price / 12,
                    'fixed_duration' => $price / max(($subscription->plan?->duration_days ?? 30) / 30, 1),
                    'lifetime' => 0.0,
                    default => $price,
                };
            });

        return round((float) $monthlyRevenue, 2);
    }

    public function arr(): float
    {
        return round($this->mrr() * 12, 2);
    }

    public function orders(array $filters = []): int
    {
        return (int) $this->analytics->applyDateRange(Order::query(), 'created_at', $filters)->count();
    }

    public function conversionRate(array $filters = []): float
    {
        [$from, $to] = $this->analytics->resolveDateRange($filters);

        $visitors = DB::table('analytics_audit_logs')
            ->whereBetween('created_at', [$from, $to])
            ->distinct('actor_id')
            ->count('actor_id');

        if ($visitors === 0) {
            return 0.0;
        }

        $purchasers = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Paid->value)
            ->distinct('user_id')
            ->count('user_id');

        return round(($purchasers / $visitors) * 100, 2);
    }

    public function activeStudents(): int
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('enrollments', fn ($q) => $q->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Completed->value]))
            ->count();
    }

    public function activeCourses(): int
    {
        return Course::query()->published()->count();
    }

    public function completionPercentage(array $filters = []): float
    {
        $total = CourseProgress::query();
        $completed = (clone $total)->whereNotNull('completed_at');

        $total = $this->analytics->applyDateRange($total, 'last_accessed_at', $filters)->count();
        if ($total === 0) {
            return 0.0;
        }

        $completedCount = $this->analytics->applyDateRange(CourseProgress::query(), 'last_accessed_at', $filters)
            ->whereNotNull('completed_at')->count();

        return round(($completedCount / $total) * 100, 2);
    }

    public function quizPassPercentage(array $filters = []): float
    {
        $query = QuizAttempt::query()->where('status', 'completed');
        $total = $this->analytics->applyDateRange(clone $query, 'submitted_at', $filters)->count();

        if ($total === 0) {
            return 0.0;
        }

        $passed = $this->analytics->applyDateRange(QuizAttempt::query(), 'submitted_at', $filters)
            ->where('status', 'completed')->where('passed', true)->count();

        return round(($passed / $total) * 100, 2);
    }

    public function attendancePercentage(array $filters = []): float
    {
        $query = SessionAttendance::query();
        $total = $this->analytics->applyDateRange(clone $query, 'joined_at', $filters)->count();

        if ($total === 0) {
            return 0.0;
        }

        $attended = $this->analytics->applyDateRange(SessionAttendance::query(), 'joined_at', $filters)
            ->where('present', true)->count();

        return round(($attended / $total) * 100, 2);
    }

    public function certificatesIssued(array $filters = []): int
    {
        return (int) $this->analytics->applyDateRange(Certificate::query(), 'issued_on', $filters)->count();
    }

    public function averageRating(): float
    {
        // Foundation: course ratings table not yet present; returns 0 until ratings exist.
        return 0.0;
    }

    public function blogViews(array $filters = []): int
    {
        return (int) BlogPost::query()->sum('view_count');
    }
}
