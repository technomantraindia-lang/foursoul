<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\Communication\Contracts\PushProviderInterface;
use App\Services\Communication\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Central dispatch pipeline for the communication engine.
 *
 * Flow: template -> render -> persist Notification (audit trail) -> deliver
 * through the channel provider. Delivery failures are tracked on the
 * notification row; retries re-run deliver() without duplicating rows.
 */
class CommunicationService
{
    public function __construct(
        protected TemplateRenderer $renderer,
        protected WhatsAppProviderInterface $whatsapp,
        protected PushProviderInterface $push,
    ) {}

    /**
     * Queue a templated notification for a user.
     *
     * @param  array<string, mixed>  $variables
     * @param  array<string, mixed>  $metadata
     */
    public function send(
        User $user,
        string $templateKey,
        array $variables = [],
        ?NotificationChannel $channel = null,
        ?\DateTimeInterface $scheduledAt = null,
        array $metadata = [],
    ): ?Notification {
        $template = NotificationTemplate::where('key', $templateKey)->first();

        if (! $template) {
            Log::warning('Communication template not found.', ['key' => $templateKey]);

            return null;
        }

        $channel ??= NotificationChannel::from($template->channel);

        if ($channel !== NotificationChannel::InApp && ! $this->userAllows($user, $channel)) {
            return null;
        }

        $rendered = $this->renderer->render($template, $variables);

        return Notification::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'notification_template_id' => $template->id,
            'channel' => $channel,
            'subject' => $rendered['subject'],
            'title' => $rendered['title'],
            'body' => $rendered['body'],
            'variables' => $variables,
            'status' => $scheduledAt ? NotificationStatus::Scheduled : NotificationStatus::Pending,
            'scheduled_at' => $scheduledAt,
            'metadata' => $metadata,
        ]);
    }

    /** Deliver a pending/scheduled notification. Safe to retry after failure. */
    public function deliver(Notification $notification): bool
    {
        if ($notification->status === NotificationStatus::Sent) {
            return true;
        }

        try {
            $accepted = match ($notification->channel) {
                NotificationChannel::Email => $this->sendEmail($notification),
                NotificationChannel::WhatsApp => $this->whatsapp->send(
                    (string) $notification->user?->mobile,
                    $notification->body,
                    ['title' => $notification->title],
                ),
                NotificationChannel::Push => $this->push->send(
                    (string) data_get($notification->metadata, 'device_token', ''),
                    $notification->title,
                    $notification->body,
                ),
                NotificationChannel::Sms, NotificationChannel::InApp => true,
            };

            $notification->forceFill([
                'status' => $accepted ? NotificationStatus::Sent : NotificationStatus::Failed,
                'sent_at' => $accepted ? now() : null,
                'failure_reason' => $accepted ? null : 'Provider rejected the message.',
            ])->save();

            return $accepted;
        } catch (\Throwable $e) {
            $notification->forceFill([
                'status' => NotificationStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ])->save();

            Log::error('Notification delivery failed.', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function sendEmail(Notification $notification): bool
    {
        if (! $notification->user?->email) {
            return false;
        }

        Mail::raw($notification->body, function ($message) use ($notification): void {
            $message->to($notification->user->email)
                ->subject($notification->subject ?: $notification->title);
        });

        return true;
    }

    protected function userAllows(User $user, NotificationChannel $channel): bool
    {
        $prefs = $user->notification_preferences ?? [];

        return (bool) ($prefs[$channel->value] ?? true);
    }
}