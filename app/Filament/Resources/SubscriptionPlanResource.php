<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PlanBillingType;
use App\Enums\PlanCourseAccessType;
use App\Enums\PlanStatus;
use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'Subscriptions';

    protected static ?string $navigationLabel = 'Membership Plans';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Plan Identity & Duration')
                ->description('Configure the membership tier name, duration and visibility.')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Plan Name (e.g. BEGIN, COMMIT, TRANSFORM, SADHANA)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('metadata.duration')
                        ->label('Duration Label (e.g. 1 MONTH, 3 MONTHS, YEARLY)')
                        ->placeholder('3 MONTHS')
                        ->required(),

                    Forms\Components\Select::make('billing_type')
                        ->label('Billing Cycle')
                        ->options(collect(PlanBillingType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->default(PlanBillingType::Monthly->value)
                        ->required(),

                    Forms\Components\TextInput::make('duration_days')
                        ->label('Access Duration (Days)')
                        ->numeric()
                        ->default(30)
                        ->required(),

                    Forms\Components\TextInput::make('metadata.pause_days')
                        ->label('Plan-Pause Flexibility (Days)')
                        ->numeric()
                        ->default(0)
                        ->helperText('Number of allowed freeze days (e.g. 7, 14, 45)'),

                    Forms\Components\Select::make('status')
                        ->options(collect(PlanStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->default(PlanStatus::Active->value)
                        ->required(),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured Badge (e.g. "Most Popular" / Highlight)')
                        ->default(false),

                    Forms\Components\TextInput::make('metadata.savings')
                        ->label('Savings Badge (e.g. 55% OFF)')
                        ->placeholder('55% OFF'),

                    Forms\Components\TextInput::make('short_description')
                        ->label('Savings / Highlight Note')
                        ->placeholder('SAVE Rs. 2,298 · 55% OFF')
                        ->maxLength(255),
                ]),

            Section::make('Multi-Currency Pricing')
                ->description('Set prices matching the frontend INR, AED and USD tables.')
                ->columns(['default' => 1, 'md' => 3])
                ->schema([
                    Fieldset::make('₹ INR Pricing (India)')->columns(1)->schema([
                        Forms\Components\TextInput::make('price_inr')
                            ->label('Final Price (₹)')
                            ->numeric()
                            ->required()
                            ->prefix('₹'),
                        Forms\Components\TextInput::make('metadata.strikethrough_inr')
                            ->label('Original Strikethrough')
                            ->placeholder('Rs. 4,197'),
                        Forms\Components\TextInput::make('metadata.per_month_inr')
                            ->label('Per-Month Breakdown')
                            ->placeholder('just Rs. 633 / month'),
                    ]),

                    Fieldset::make('AED Pricing (UAE)')->columns(1)->schema([
                        Forms\Components\TextInput::make('price_aed')
                            ->label('Final Price (AED)')
                            ->numeric()
                            ->required()
                            ->prefix('AED'),
                        Forms\Components\TextInput::make('metadata.strikethrough_aed')
                            ->label('Original Strikethrough')
                            ->placeholder('AED 447'),
                        Forms\Components\TextInput::make('metadata.per_month_aed')
                            ->label('Per-Month Breakdown')
                            ->placeholder('just AED 100 / month'),
                    ]),

                    Fieldset::make('$ USD Pricing (Worldwide)')->columns(1)->schema([
                        Forms\Components\TextInput::make('price_usd')
                            ->label('Final Price ($)')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('metadata.strikethrough_usd')
                            ->label('Original Strikethrough')
                            ->placeholder('$237'),
                        Forms\Components\TextInput::make('metadata.per_month_usd')
                            ->label('Per-Month Breakdown')
                            ->placeholder('just $33 / month'),
                    ]),
                ]),

            Section::make('Included Features & Benefits')
                ->description('Bullet points displayed on the membership card in the frontend.')
                ->schema([
                    Forms\Components\TagsInput::make('metadata.features')
                        ->label('Feature Bullet Points')
                        ->placeholder('Type a feature and press Enter (e.g. "Unlimited Daily Live Classes")')
                        ->separator(',')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'xl' => 4,
            ])
            ->columns([
                Stack::make([
                    Split::make([
                        Tables\Columns\TextColumn::make('name')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('primary'),

                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->grow(false),
                    ]),

                    Split::make([
                        Tables\Columns\TextColumn::make('metadata.duration')
                            ->badge()
                            ->color('gray')
                            ->default(fn ($record) => $record->billing_type?->label() ?? 'Plan'),

                        Tables\Columns\IconColumn::make('is_featured')
                            ->boolean()
                            ->trueIcon('heroicon-s-star')
                            ->falseIcon('heroicon-o-star')
                            ->trueColor('warning')
                            ->grow(false),
                    ]),

                    Tables\Columns\TextColumn::make('price_inr')
                        ->formatStateUsing(function ($state, $record) {
                            $inr = $record->price_inr ? '₹' . number_format((float) $record->price_inr) : '—';
                            $aed = $record->price_aed ? 'AED ' . number_format((float) $record->price_aed) : '—';
                            $usd = $record->price_usd ? '$' . number_format((float) $record->price_usd) : '—';
                            return "{$inr}  |  {$aed}  |  {$usd}";
                        })
                        ->size(TextSize::Medium)
                        ->weight('semibold'),

                    Tables\Columns\TextColumn::make('short_description')
                        ->color('success')
                        ->weight('medium')
                        ->size(TextSize::Small),

                    Tables\Columns\TextColumn::make('metadata.pause_days')
                        ->formatStateUsing(fn ($state) => $state ? "❄️ {$state} Freeze Days" : "❄️ 0 Freeze Days")
                        ->size(TextSize::Small)
                        ->color('gray'),

                    Tables\Columns\TextColumn::make('metadata.features')
                        ->label('Features')
                        ->formatStateUsing(function ($state) {
                            if (!is_array($state) || empty($state)) {
                                return 'Standard membership access';
                            }
                            return '• ' . implode("\n• ", array_slice($state, 0, 3)) . (count($state) > 3 ? "\n• +" . (count($state) - 3) . " more..." : '');
                        })
                        ->size(TextSize::Small)
                        ->color('gray')
                        ->wrap(),
                ])->space(3),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::guard('admin')->check();
    }
}
