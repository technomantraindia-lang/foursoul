<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionProvider: string
{
    case Zoom = 'zoom';
    case GoogleMeet = 'google_meet';
    case MicrosoftTeams = 'microsoft_teams';
    case Jitsi = 'jitsi';
    case Custom = 'custom';
    case Youtube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::Zoom => 'Zoom',
            self::GoogleMeet => 'Google Meet',
            self::MicrosoftTeams => 'Microsoft Teams',
            self::Jitsi => 'Jitsi',
            self::Custom => 'Custom',
            self::Youtube => 'YouTube Live',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
