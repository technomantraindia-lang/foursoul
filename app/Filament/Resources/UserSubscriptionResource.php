<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SubscriptionStatus;
use App\Filament\Resources\UserSubscriptionResource\Pages;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Member subscriptions';

    protected static ?string $modelLabel = 'subscription';

    protected static ?string $pluralModelLabel = 'member subscriptions';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Subscription details')->columns(2)->schema([
                Select::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subscription_plan_id')
                    ->label('Membership Plan')
                    ->relationship('plan', 'name')
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                        static fn (SubscriptionStatus $status): array => [$status->value => $status->label()]
                    ))
                    ->default(SubscriptionStatus::Active->value)
                    ->required()
                    ->native(false),
                Select::make('currency')
                    ->options([
                        'INR' => 'INR (₹)',
                        'AED' => 'AED (د.إ)',
                        'USD' => 'USD ($)',
                    ])
                    ->default('INR')
                    ->required(),
                TextInput::make('total_amount')
                    ->label('Amount Paid')
                    ->numeric()
                    ->default(0.00),
                DatePicker::make('started_at')
                    ->label('Started Date')
                    ->default(now()),
                DatePicker::make('expires_at')
                    ->label('Expiration Date')
                    ->default(now()->addDays(30)),
                TextInput::make('activation_source')
                    ->label('Source / Notes')
                    ->default('admin_granted'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('plan.name')
                    ->label('Membership Plan')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'sadhana', 'sadhana vip' => 'warning',
                        'transform' => 'info',
                        'commit' => 'primary',
                        'begin' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof SubscriptionStatus ? $state : SubscriptionStatus::tryFrom((string) $state)) {
                        SubscriptionStatus::Active => 'success',
                        SubscriptionStatus::Trialing => 'info',
                        SubscriptionStatus::Pending => 'warning',
                        SubscriptionStatus::Expired, SubscriptionStatus::Cancelled, SubscriptionStatus::Ended => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($record): string => ($record->currency ?? 'INR') . ' ' . number_format((float) ($record->total_amount ?? 0), 2))
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->date('d M Y')
                    ->color(fn ($record): string => $record->expires_at && now()->gt($record->expires_at) ? 'danger' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subscription_plan_id')
                    ->label('Filter by Plan')
                    ->options(SubscriptionPlan::pluck('name', 'id')),
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                        static fn (SubscriptionStatus $status): array => [$status->value => $status->label()]
                    )),
            ])
            ->actions([
                Action::make('extendMonth')
                    ->label('+30 Days')
                    ->icon('heroicon-m-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (UserSubscription $record): void {
                        $base = ($record->expires_at && now()->lt($record->expires_at)) ? $record->expires_at : now();
                        $newExpiry = (clone $base)->addDays(30);
                        $record->update([
                            'expires_at' => $newExpiry,
                            'current_period_end' => $newExpiry,
                            'status' => SubscriptionStatus::Active,
                        ]);

                        Notification::make()
                            ->title('Subscription Extended')
                            ->body("Extended until {$newExpiry->format('d M Y')}")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No member subscriptions yet')
            ->emptyStateDescription('Create a subscription or wait for online customer payments.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSubscriptions::route('/'),
            'create' => Pages\CreateUserSubscription::route('/create'),
            'edit' => Pages\EditUserSubscription::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::guard('admin')->check();
    }
}
