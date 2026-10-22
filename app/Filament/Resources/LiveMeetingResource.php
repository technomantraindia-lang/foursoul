<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LiveSessionStatus;
use App\Enums\SessionProvider;
use App\Filament\Resources\LiveMeetingResource\Pages;
use App\Models\LiveSession;
use App\Models\SubscriptionPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LiveMeetingResource extends Resource
{
    protected static ?string $model = LiveSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-video-camera';

    protected static string | \UnitEnum | null $navigationGroup = 'Members Area';

    protected static ?string $navigationLabel = 'Live Zoom Links';

    protected static ?string $modelLabel = 'Live Zoom Class';

    protected static ?string $pluralModelLabel = 'Live Zoom Links';

    protected static ?string $slug = 'live-meetings';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('video_type', 'live_meeting')
                    ->orWhereNotNull('meeting_url');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Live Online Meeting Details')
                ->description('Configure the live Zoom or Google Meet session that members will attend.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Live Class Title')
                        ->placeholder('e.g. Sunrise Surya & Daily Pranayama Live Class')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('provider')
                        ->label('Platform Provider')
                        ->options([
                            'zoom' => '📹 Zoom Meeting',
                            'google_meet' => '🎥 Google Meet',
                            'teams' => '💼 Microsoft Teams',
                            'custom' => '🌐 Other Web Link',
                        ])
                        ->default('zoom')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Publication Status')
                        ->options(collect(LiveSessionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                        ->default(LiveSessionStatus::Published->value)
                        ->required(),

                    Forms\Components\TextInput::make('meeting_url')
                        ->label('Live Meeting Link (Zoom / Meet URL)')
                        ->url()
                        ->placeholder('https://zoom.us/j/9876543210 or https://meet.google.com/abc-defg-hij')
                        ->required()
                        ->helperText('Members with an active membership plan will see this clickable join link.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('meeting_password')
                        ->label('Passcode / Password (Optional)')
                        ->placeholder('e.g. SOUL108')
                        ->helperText('Displayed to members with a 1-click copy button on the frontend.'),

                    Forms\Components\DateTimePicker::make('scheduled_start_at')
                        ->label('Class Start Time')
                        ->default(now()->addMinutes(30))
                        ->required(),

                    Forms\Components\DateTimePicker::make('scheduled_end_at')
                        ->label('Class End Time')
                        ->default(now()->addMinutes(90)),

                    Forms\Components\Textarea::make('description')
                        ->label('Class Instructions / Notes for Members')
                        ->rows(3)
                        ->placeholder('e.g. Turn your camera on for real-time alignment feedback. Have a yoga mat and water bottle ready.')
                        ->columnSpanFull(),
                ]),

            Section::make('Membership Plan Access')
                ->description('Specify which membership plans can join this live session.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('min_plan_tier')
                        ->label('Which Plan Can Access This Live Class?')
                        ->options([
                            'all' => '🌟 All Active Plans (Begin, Commit, Transform, Sadhana)',
                            'begin' => '🌱 Begin & Above (All Paid Members)',
                            'commit' => '🌿 Commit & Above (3 Months & Up)',
                            'transform' => '🌳 Transform & Above (6 Months & Up)',
                            'sadhana' => '👑 Sadhana VIP Only (Annual Members Only)',
                            'custom' => '🎯 Specific Plans Only (Select below)',
                        ])
                        ->default('all')
                        ->required()
                        ->live(),

                    Forms\Components\CheckboxList::make('allowed_plans')
                        ->label('Choose Allowed Plans')
                        ->options(fn () => SubscriptionPlan::where('status', 'active')->pluck('name', 'slug'))
                        ->visible(fn (Forms\Get $get) => $get('min_plan_tier') === 'custom')
                        ->columns(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_locked_for_non_members')
                        ->label('Lock For Customers Without Membership')
                        ->helperText('When enabled, non-members or expired users will see a locked screen and upgrade prompt.')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Platform')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                        'zoom' => '📹 Zoom',
                        'google_meet' => '🎥 Meet',
                        'teams' => '💼 Teams',
                        default => '🌐 Live',
                    })
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'zoom' => 'primary',
                        'google_meet' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('meeting_url')
                    ->label('Meeting Link')
                    ->limit(28)
                    ->copyable()
                    ->copyMessage('Meeting link copied!')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('meeting_password')
                    ->label('Passcode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('None'),

                Tables\Columns\TextColumn::make('min_plan_tier')
                    ->label('Plan Access')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'all' => '🌟 All Members',
                        'begin' => '🌱 Begin & Up',
                        'commit' => '🌿 Commit & Up',
                        'transform' => '🌳 Transform & Up',
                        'sadhana' => '👑 Sadhana VIP',
                        'custom' => '🎯 Custom Plans',
                        default => 'All Members',
                    })
                    ->color(fn ($state) => match ($state) {
                        'sadhana' => 'warning',
                        'transform' => 'info',
                        'commit' => 'primary',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('scheduled_start_at')
                    ->label('Class Schedule')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('min_plan_tier')
                    ->label('Plan Access')
                    ->options([
                        'all' => '🌟 All Members',
                        'begin' => '🌱 Begin & Up',
                        'commit' => '🌿 Commit & Up',
                        'transform' => '🌳 Transform & Up',
                        'sadhana' => '👑 Sadhana VIP',
                    ]),
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'zoom' => 'Zoom',
                        'google_meet' => 'Google Meet',
                        'teams' => 'Teams',
                    ]),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Add Live Zoom Link')
                    ->icon('heroicon-m-plus'),
            ])
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
            'index' => Pages\ListLiveMeetings::route('/'),
            'create' => Pages\CreateLiveMeeting::route('/create'),
            'edit' => Pages\EditLiveMeeting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return Auth::guard('admin')->check();
    }

    public static function canEdit($record): bool
    {
        return Auth::guard('admin')->check();
    }

    public static function canDelete($record): bool
    {
        return Auth::guard('admin')->check();
    }

    public static function canViewAny(): bool
    {
        return Auth::guard('admin')->check();
    }
}
