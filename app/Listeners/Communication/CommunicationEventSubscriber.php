<?php

declare(strict_types=1);

namespace App\Listeners\Communication;

use App\Services\Communication\ReminderService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\QueuedClosure;

/**
 * Event-driven communication mapping (Phase 8, Part 9).
 *
 * Maps domain events to notification templates through the centralized
 * CommunicationService pipeline (queued, preference-aware, audited).
 *
 * Template keys follow the convention: <event>.<channel-agnostic>
 * e.g. user.registered, payment.successful, subscription.expiring.
 */
class CommunicationEventSubscriber
{
    public function __construct(
        private readonly ReminderService $reminders,
    ) {}

    public function subscribe(Dispatcher $events): void
    {
        $map = [
            // Event => [template key, reminder type]
            'user.registered' => ['user.registered', null],
            'payment.succeeded' => ['payment.successful', 'payment_success'],
            'order.created' => ['order.created', null],
            'subscription.activated' => ['subscription.activated', null],
            'subscription.expiring' => ['subscription.expiring', 'subscription_expiry'],
            'quiz.submitted' => ['quiz.submitted', null],
            'assignment.reviewed' => ['assignment.reviewed', null],
            'certificate.issued' => ['certificate.issued', 'certificate_issued'],
            'live-session.scheduled' => ['live_session.scheduled', null],
            'live-session.starting' => ['live_session.starting', 'upcoming_live_session'],
            'live-session.replay-available' => ['live_session.replay_available', null],
            'invoice.ready' => ['invoice.ready', 'invoice_ready'],
        ];

        foreach ($map as $event => [$template, $reminderType]) {
            $events->listen($event, function (object $payload) use ($template, $reminderType): void {
                $this->handle($template, $reminderType, $payload);
            });
        }
    }

    private function handle(string $templateKey, ?string $reminderType, object $payload): void
    {
        $user = $payload->user ?? null;
        if (! $user) {
            return;
        }

        $variables = method_exists($payload, 'notificationVariables')
            ? $payload->notificationVariables()
            : (array) $payload;

        if ($reminderType !== null && ! $this->reminders->shouldSend($user, $reminderType)) {
            return;
        }

        \App\Jobs\SendNotificationJob::dispatch(
            $user,
            $templateKey,
            $variables,
        );
    }
}