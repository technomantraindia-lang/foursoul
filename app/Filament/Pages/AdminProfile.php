<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AdminStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\EditProfile;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Prompt 2B (10, 11): Administration Portal profile page.
 *
 * Allows an authenticated Admin to edit Name / Avatar, and Email only with
 * secure unique-email validation. Password change lives in its own section.
 * Displays Email, Status and Last Login as read-only information.
 *
 * Roles/permissions UI intentionally omitted until Prompt 2C.
 */
class AdminProfile extends EditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Account'))
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->string()
                            ->maxLength(255),

                        FileUpload::make('avatar_path')
                            ->label(__('Avatar'))
                            ->image()
                            ->directory('admins/avatars')
                            ->disk('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->rule('mimes:png,jpg,jpeg,webp')
                            ->imagePreviewHeight('150'),

                        TextInput::make('email')
                            ->label(__('Email address'))
                            ->email()
                            ->required()
                            ->string()
                            ->maxLength(255)
                            ->unique(table: 'admins', ignorable: fn () => $this->getUser()),

                        Placeholder::make('status_display')
                            ->label(__('Status'))
                            ->content(fn (): string => $this->getUser()->status->label()),

                        Placeholder::make('last_login_display')
                            ->label(__('Last Login'))
                            ->content(fn (): string => $this->getUser()->last_login_at
                                ? $this->getUser()->last_login_at->format('Y-m-d H:i:s T')
                                : __('Never')),
                    ]),

                Section::make(__('Update Password'))
                    ->aside()
                    ->description(__('Leave blank to keep your current password.'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('Current Password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->rule(fn (): \Closure => fn (string $attribute, mixed $value, \Closure $fail): ?bool => filled($value) && ! Hash::check((string) $value, (string) $this->getUser()->password)
                                ? $fail(__('The provided current password is incorrect.'))
                                : null),

                        TextInput::make('new_password')
                            ->label(__('New Password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->nullable()
                            ->same('new_password_confirmation')
                            ->rule(Password::min(10)->letters()->numbers()),

                        TextInput::make('new_password_confirmation')
                            ->label(__('Confirm New Password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    /**
     * Persist profile changes. Only fillable, expected attributes are written;
     * status and login-tracking fields can never be changed from this form.
     */
    public function handleRecordUpdate(Model $record, array $data): Model
    {
        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (array_key_exists('avatar_path', $data)) {
            $updates['avatar_path'] = $data['avatar_path'];
        }

        if (filled($data['new_password'] ?? null)) {
            $updates['password'] = $data['new_password']; // hashed via model cast
        }

        $record->update($updates);

        return $record;
    }
}
