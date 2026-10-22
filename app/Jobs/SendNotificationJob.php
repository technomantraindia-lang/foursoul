<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Notification;
use App\Services\Communication\CommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued delivery for a persisted Notification row.
 * Retries are handled by the queue; final failure is recorded on the row
 * (communication audit) by CommunicationService::deliver().
 */
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public Notification $notification)
    {
        $this->onQueue('notifications');
    }

    public function handle(CommunicationService $service): void
    {
        $service->deliver($this->notification);
    }
}