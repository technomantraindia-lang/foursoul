<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Enums\NotificationChannel;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Invoice;
use App\Models\LiveSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\UserSubscription;
use App\Services\Commerce\InvoiceService;
use App\Support\CurrencyFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Reminder engine. Builds variables from domain records and dispatches
 * templated notifications through CommunicationService.
 *
 * Supported reminders:
 * subscription_expiry, upcoming_live_session, assignment_due, quiz_due,
 * certificate_issued, payment_success, invoice_ready
 */
class ReminderService
{
    public function __construct(protected CommunicationService $communication) {}

    public function subscriptionExpiring(UserSubscription $subscription, int $daysRemaining): void
    {
        $user = $subscription->user;
        if (! $user) {
            return;
        }

        $this->dispatch($user, 'subscription_expiring', [
            'student_name' => $user->name,
            'plan_name' => (string) data_get($subscription, 'plan.name', 'your plan'),
            'subscription_expiry' => optional($subscription->ends_at)->format('d M Y') ?? '',
            'days_remaining' => (string) $daysRemaining,
        ]);
    }

    public function liveSessionStarting(LiveSession $session): void
    {
        foreach ($this->sessionRecipients($session) as $user) {
            $this->dispatch($user, 'live_session_starting', [
                'student_name' => $user->name,
                'session_title' => $session->title,
                'course_name' => (string) data_get($session, 'course.title', ''),
                'starts_at' => optional($session->starts_at)->format('d M Y h:i A') ?? '',
            ]);
        }
    }

    public function liveSessionScheduled(LiveSession $session): void
    {
        foreach ($this->sessionRecipients($session) as $user) {
            $this->dispatch($user, 'live_session_scheduled', [
                'student_name' => $user->name,
                'session_title' => $session->title,
                'course_name' => (string) data_get($session, 'course.title', ''),
                'starts_at' => optional($session->starts_at)->format('d M Y h:i A') ?? '',
            ]);
        }
    }

    public function replayAvailable(LiveSession $session): void
    {
        foreach ($this->sessionRecipients($session) as $user) {
            $this->dispatch($user, 'replay_available', [
                'student_name' => $user->name,
                'session_title' => $session->title,
                'course_name' => (string) data_get($session, 'course.title', ''),
            ]);
        }
    }

    public function assignmentDue(Assignment $assignment): void
    {
        foreach ($this->assignmentRecipients($assignment) as $user) {
            $this->dispatch($user, 'assignment_due', [
                'student_name' => $user->name,
                'assignment_title' => $assignment->title,
                'due_at' => optional($assignment->due_at)->format('d M Y h:i A') ?? '',
            ]);
        }
    }

    public function quizDue(Quiz $quiz): void
    {
        foreach ($this->quizRecipients($quiz) as $user) {
            $this->dispatch($user, 'quiz_due', [
                'student_name' => $user->name,
                'quiz_title' => $quiz->title,
                'available_until' => optional($quiz->available_until)->format('d M Y h:i A') ?? '',
            ]);
        }
    }

    public function certificateIssued(Certificate $certificate): void
    {
        $user = $certificate->user;
        if (! $user) {
            return;
        }

        $this->dispatch($user, 'certificate_issued', [
            'student_name' => $user->name,
            'certificate_number' => $certificate->certificate_number,
            'course_name' => (string) data_get($certificate, 'course.title', ''),
        ]);
    }

    public function paymentSuccess(Payment $payment): void
    {
        $user = $payment->user;
        if (! $user) {
            return;
        }

        $this->dispatch($user, 'payment_success', [
            'student_name' => $user->name,
            'payment_amount' => CurrencyFormatter::format(
                (float) $payment->amount,
                $payment->currency ?? 'INR',
            ),
            'order_number' => (string) data_get($payment, 'order.order_number', ''),
        ]);
    }

    public function invoiceReady(Invoice $invoice): void
    {
        $user = $invoice->user;
        if (! $user) {
            return;
        }

        $this->dispatch($user, 'invoice_ready', [
            'student_name' => $user->name,
            'invoice_number' => $invoice->invoice_number,
            'payment_amount' => CurrencyFormatter::format((float) $invoice->total, $invoice->currency ?? 'INR'),
        ]);
    }

    protected function dispatch($user, string $templateKey, array $variables): void
    {
        $notification = $this->communication->send($user, $templateKey, $variables);

        if ($notification) {
            \App\Jobs\SendNotificationJob::dispatch($notification);
        }
    }

    /** @return iterable<\App\Models\User> */
    protected function sessionRecipients(LiveSession $session): iterable
    {
        return $session->course?->enrollments()->with('user')->get()->pluck('user')->filter() ?? collect();
    }

    /** @return iterable<\App\Models\User> */
    protected function assignmentRecipients(Assignment $assignment): iterable
    {
        return $assignment->course?->enrollments()->with('user')->get()->pluck('user')->filter() ?? collect();
    }

    /** @return iterable<\App\Models\User> */
    protected function quizRecipients(Quiz $quiz): iterable
    {
        return $quiz->course?->enrollments()->with('user')->get()->pluck('user')->filter() ?? collect();
    }
}