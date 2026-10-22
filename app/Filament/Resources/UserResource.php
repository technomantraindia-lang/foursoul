<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Customer accounts';

    protected static ?string $modelLabel = 'customer';

    protected static ?string $pluralModelLabel = 'customers';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Customer profile')->columns(2)->schema([
                TextInput::make('full_name')->label('Full Name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(190),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->label(fn (string $operation): string => $operation === 'create' ? 'Password' : 'New Password (leave empty to keep current)'),
                Select::make('status')->options(collect(UserStatus::cases())->mapWithKeys(
                    static fn (UserStatus $status): array => [$status->value => $status->label()]
                ))->default(UserStatus::Active->value)->required()->native(false),
                TextInput::make('mobile_country_code')->label('Country code (e.g. +91, +971)')->maxLength(8)->default('+91'),
                TextInput::make('mobile_number')->label('Mobile number')->maxLength(20),
                Select::make('preferred_currency')
                    ->label('Preferred Currency')
                    ->options([
                        'INR' => 'INR (₹)',
                        'AED' => 'AED (د.إ)',
                        'USD' => 'USD ($)',
                    ])
                    ->default('INR')
                    ->native(false),
                TextInput::make('preferred_language')->default('en')->maxLength(10),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('activeSubscription.plan.name')
                    ->label('Membership Plan')
                    ->badge()
                    ->default('No Active Plan')
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'sadhana', 'sadhana vip' => 'warning',
                        'transform' => 'info',
                        'commit' => 'primary',
                        'begin' => 'success',
                        default => 'gray',
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('subscriptions', function (Builder $sq) use ($search): void {
                            $sq->where('status', SubscriptionStatus::Active)
                                ->whereHas('plan', fn (Builder $pq) => $pq->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('activeSubscription.expires_at')
                    ->label('Plan Valid Until')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof UserStatus ? $state : UserStatus::tryFrom((string) $state)) {
                        UserStatus::Active => 'success',
                        UserStatus::Suspended => 'warning',
                        UserStatus::Blocked, UserStatus::Closed => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('membership_plan')
                    ->label('Membership Plan')
                    ->options([
                        'begin' => 'Begin Plan',
                        'commit' => 'Commit Plan',
                        'transform' => 'Transform Plan',
                        'sadhana' => 'Sadhana VIP',
                        'none' => 'No Active Plan',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        if (blank($val)) {
                            return $query;
                        }

                        if ($val === 'none') {
                            return $query->whereDoesntHave('subscriptions', function (Builder $q): void {
                                $q->where('status', SubscriptionStatus::Active);
                            });
                        }

                        return $query->whereHas('subscriptions', function (Builder $q) use ($val): void {
                            $q->where('status', SubscriptionStatus::Active)
                                ->whereHas('plan', fn (Builder $pq): Builder => $pq->where('slug', $val));
                        });
                    }),
                SelectFilter::make('status')->options(collect(UserStatus::cases())->mapWithKeys(
                    static fn (UserStatus $status): array => [$status->value => $status->label()]
                )),
                TrashedFilter::make(),
            ])
            ->actions([
                Action::make('assignPlan')
                    ->label('Assign Plan')
                    ->icon('heroicon-m-sparkles')
                    ->color('warning')
                    ->form([
                        Select::make('subscription_plan_id')
                            ->label('Select Membership Plan')
                            ->options(SubscriptionPlan::pluck('name', 'id'))
                            ->required(),
                        Select::make('duration_days')
                            ->label('Access Duration')
                            ->options([
                                '30' => '1 Month (30 Days)',
                                '90' => '3 Months (90 Days)',
                                '180' => '6 Months (180 Days)',
                                '365' => '1 Year (365 Days)',
                            ])
                            ->default('30')
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $plan = SubscriptionPlan::find($data['subscription_plan_id']);
                        if (!$plan) {
                            return;
                        }

                        $now = now();
                        $days = (int) ($data['duration_days'] ?? 30);
                        $expires = (clone $now)->addDays($days);

                        UserSubscription::updateOrCreate(
                            [
                                'user_id' => $record->id,
                                'subscription_plan_id' => $plan->id,
                            ],
                            [
                                'uuid' => (string) Str::uuid(),
                                'status' => SubscriptionStatus::Active,
                                'currency' => is_string($record->preferred_currency) ? $record->preferred_currency : ($record->preferred_currency?->value ?? 'INR'),
                                'unit_price' => $plan->price_inr ?? 0,
                                'total_amount' => $plan->price_inr ?? 0,
                                'started_at' => $now,
                                'current_period_start' => $now,
                                'current_period_end' => $expires,
                                'expires_at' => $expires,
                                'activation_source' => 'admin_granted',
                            ]
                        );

                        Notification::make()
                            ->title('Membership Plan Assigned')
                            ->body("{$record->full_name} is now on {$plan->name} (valid until {$expires->format('d M Y')}).")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No customer accounts yet')
            ->emptyStateDescription('Customers registered from the frontend or created by admin will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::guard('admin')->check();
    }
}
