<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


/**
 * Prompt 2B (§3, §4): Admin account model.
 *
 * Fully separated from the customer `User` model — admins authenticate
 * through the dedicated `admin` guard and the Filament panel at /admin.
 *
 * Filament compatibility: implements FilamentUser::canAccessPanel()
 * so only `active` admins may enter the panel (server-side gate).
 */
#[ObservedBy([\App\Observers\AdminObserver::class])]
class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected string $guard_name = 'admin';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'email_verified_at',
        'password',
        'avatar_path',
        'status',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => AdminStatus::class,
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Prompt 2B (§4): server-side panel access gate.
     *
     * Only active admins may access the panel. Suspended/disabled/deleted
     * are always denied. Deleted admins never resolve via default queries.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return ! $this->trashed() && $this->status->canAccessPanel();
    }

    /**
     * Inactive admins must never pass ability checks, including Super Admin bypass.
     */
    public function can($abilities, $arguments = []): bool
    {
        if ($this->trashed() || $this->status !== AdminStatus::Active) {
            return false;
        }

        return parent::can($abilities, $arguments);
    }

    /**
     * Whether this admin may authenticate (used by login flows/tests).
     */
    public function canAuthenticate(): bool
    {
        return $this->status->canAuthenticate();
    }

    /**
     * Avatar URL for Filament display; null falls back to initials.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path ? \Illuminate\Support\Facades\Storage::url($this->avatar_path) : null;
    }
}
