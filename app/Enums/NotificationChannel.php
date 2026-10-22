<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationChannel: string
{
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Push = 'push';
    case Sms = 'sms';
    case InApp = 'in_app';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::WhatsApp => 'WhatsApp',
            self::Push => 'Push Notification',
            self::Sms => 'SMS',
            self::InApp => 'In-App',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}