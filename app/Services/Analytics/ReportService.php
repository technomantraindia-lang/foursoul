<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\LiveSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\QuizAttempt;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

/**
 * Central report engine. All report definitions and aggregations live here.
 */
class ReportService
{
    public function __construct(
        protected AnalyticsService $analytics,
    ) {}

    /**
     * Available report definitions (key => label).
     *
     * @return array<string, string>
     */
    public function definitions(): array
    {
        return [
            'revenue' => 'Revenue',
            'orders' => 'Orders',
            'payments' => 'Payments',
            'refunds' => 'Refunds',
            'subscriptions' => 'Subscriptions',
            'courses' => 'Courses',
            'students' => 'Students',
            'instructors' => 'Instructors',
            'certificates' => 'Certificates',
            'assignments' => 'Assignments',
            'quizzes' => 'Quizzes',
            'attendance' => 'Attendance',
            'notifications' => 'Notifications',
            'cms' => 'CMS Content',
        ];
    }

    /**
     * Run a report by key with filters.
     */
    public function run(string $report, array $filters = []): array
    {
        return match ($report) {
            'revenue' => $this->revenue($filters),
            'orders' => $this->orders($filters),
            'payments' => $this->payments($filters),
            'refunds' => $this->refunds($filters),
            'subscriptions' => $this->subscriptions($filters),
            'courses' => $this->courses($filters),
            'students' => $this->students($filters),
            'instructors' => $this->instructors($filters),
            'certificates' => $this->certificates($filters),
            'assignments' => $this->assignments($filters),
            'quizzes' => $this->quizzes($filters),
            'attendance' => $this->attendance($filters),
            default => throw new \InvalidArgumentException("Unknown report [{$report}]."),
        };
    }

    // ------------------------------------------------------------------
    // Commerce reports
    // ------------------------------------------------------------------

    public function revenue(array $filters = []): array
    {
        $granularity = $filters['granularity'] ?? 'month';
        $format = match ($granularity) {
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v',
            'quarter' => '%Y-Q%q',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $query = Payment::query()
            ->where('status', PaymentStatus::Succeeded->value)
            ->selectRaw($this->periodSelect('paid_at', $format).', SUM(amount) as total, COUNT(*) as transactions')
            ->groupBy('period')
            ->orderBy('period');

        if (! empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }

        $byPeriod = $this->analytics->applyDateRange($query, 'paid_at', $filters)->get();

        return [
            'by_period' => $byPeriod->all(),
            'total' => (int) Payment::where('status', PaymentStatus::Succeeded->value)->sum('amount'),
            'mrr' => app(KpiService::class)->mrr(),
            'arr' => app(KpiService::class)->arr(),
        ];
    }

    public function orders(array $filters = []): array
    {
        return [
            'total' => $this->analytics->applyDateRange(Order::query(), 'created_at', $filters)->count(),
            'paid' => $this->analytics->applyDateRange(Order::query(), 'created_at', $filters)->where('status', OrderStatus::Paid->value)->count(),
            'pending' => $this->analytics->applyDateRange(Order::query(), 'created_at', $filters)->where('status', OrderStatus::Pending->value)->count(),
            'cancelled' => $this->analytics->applyDateRange(Order::query(), 'created_at', $filters)->where('status', OrderStatus::Cancelled->value)->count(),
        ];
    }

    public function payments(array $filters = []): array
    {
        return [
            'completed_count' => $this->analytics->applyDateRange(Payment::query(), 'created_at', $filters)->where('status', PaymentStatus::Succeeded->value)->count(),
            'failed_count' => $this->analytics->applyDateRange(Payment::query(), 'created_at', $filters)->where('status', PaymentStatus::Failed->value)->count(),
            'total_amount' => (int) $this->analytics->applyDateRange(Payment::query(), 'created_at', $filters)->where('status', PaymentStatus::Succeeded->value)->sum('amount'),
            'invoices_issued' => $this->analytics->applyDateRange(Invoice::query(), 'created_at', $filters)->count(),
        ];
    }

    public function refunds(array $filters = []): array
    {
        return [
            'count' => $this->analytics->applyDateRange(Refund::query(), 'created_at', $filters)->count(),
            'total_amount' => (int) $this->analytics->applyDateRange(Refund::query(), 'created_at', $filters)->sum('amount'),
        ];
    }

    public function subscriptions(array $filters = []): array
    {
        return [
            'active' => UserSubscription::where('status', 'active')->count(),
            'expired' => UserSubscription::where('status', 'expired')->count(),
            'cancelled' => UserSubscription::where('status', 'cancelled')->count(),
            'ltv_foundation_total_revenue' => (int) Payment::where('status', PaymentStatus::Succeeded->value)->sum('amount'),
        ];
    }

    // ------------------------------------------------------------------
    // Learning reports
    // ------------------------------------------------------------------

    public function courses(array $filters = []): array
    {
        $query = Course::query()
            ->select('courses.id', 'courses.title', 'courses.slug')
            ->selectRaw('(SELECT COUNT(*) FROM enrollments WHERE enrollments.course_id = courses.id) as enrollments')
            ->selectRaw('(SELECT COALESCE(SUM(payments.amount), 0) FROM order_items JOIN orders ON orders.id = order_items.order_id JOIN payments ON payments.order_id = orders.id AND payments.status = ? WHERE order_items.purchasable_id = courses.id AND order_items.purchasable_type = ?) as revenue', [PaymentStatus::Succeeded->value, Course::class])
            ->selectRaw('(SELECT COUNT(*) FROM course_progress WHERE course_progress.course_id = courses.id AND course_progress.completed_at IS NOT NULL) as completions');

        if (! empty($filters['course_id'])) {
            $query->where('courses.id', $filters['course_id']);
        }

        return ['rows' => $query->get()];
    }

    public function students(array $filters = []): array
    {
        $userId = $filters['user_id'] ?? null;

        return [
            'enrollments' => Enrollment::when($userId, fn ($q) => $q->where('user_id', $userId))->count(),
            'completed_courses' => CourseProgress::when($userId, fn ($q) => $q->where('user_id', $userId))->whereNotNull('completed_at')->count(),
            'quiz_attempts' => QuizAttempt::when($userId, fn ($q) => $q->where('user_id', $userId))->count(),
            'assignments_submitted' => AssignmentSubmission::when($userId, fn ($q) => $q->where('user_id', $userId))->count(),
            'certificates' => Certificate::when($userId, fn ($q) => $q->where('user_id', $userId))->count(),
        ];
    }

    public function instructors(array $filters = []): array
    {
        $instructorId = $filters['instructor_id'] ?? null;

        $query = Course::query()
            ->select('courses.created_by')
            ->selectRaw('COUNT(DISTINCT courses.id) as courses')
            ->selectRaw('(SELECT COUNT(*) FROM enrollments JOIN courses c2 ON c2.id = enrollments.course_id WHERE c2.created_by = courses.created_by) as students')
            ->groupBy('courses.created_by');

        if ($instructorId) {
            $query->having('courses.created_by', '=', $instructorId);
        }

        return ['rows' => $query->get()];
    }

    public function certificates(array $filters = []): array
    {
        return [
            'issued' => $this->analytics->applyDateRange(Certificate::query(), 'issued_at', $filters)->count(),
        ];
    }

    public function assignments(array $filters = []): array
    {
        return [
            'submitted' => $this->analytics->applyDateRange(AssignmentSubmission::query(), 'submitted_at', $filters)->count(),
            'graded' => $this->analytics->applyDateRange(AssignmentSubmission::query(), 'submitted_at', $filters)->whereNotNull('graded_at')->count(),
        ];
    }

    public function quizzes(array $filters = []): array
    {
        return [
            'attempts' => $this->analytics->applyDateRange(QuizAttempt::query(), 'submitted_at', $filters)->count(),
            'passed' => $this->analytics->applyDateRange(QuizAttempt::query(), 'submitted_at', $filters)->whereColumn('score', '>=', 'pass_score')->count(),
        ];
    }

    public function attendance(array $filters = []): array
    {
        return [
            'sessions' => $this->analytics->applyDateRange(LiveSession::query(), 'scheduled_at', $filters)->count(),
            'attendance_records' => DB::table('session_attendances')->count(),
        ];
    }

    protected function periodSelect(string $column, string $mysqlFormat): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $sqlite = match ($mysqlFormat) {
                '%Y-%m-%d' => '%Y-%m-%d',
                '%Y' => '%Y',
                default => '%Y-%m',
            };

            return "strftime('{$sqlite}', {$column}) as period";
        }

        return "DATE_FORMAT({$column}, '{$mysqlFormat}') as period";
    }
}