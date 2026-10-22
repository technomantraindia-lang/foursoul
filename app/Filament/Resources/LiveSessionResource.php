<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LiveSessionStatus;
use App\Enums\SessionProvider;
use App\Filament\Resources\LiveSessionResource\Pages;
use App\Models\LiveSession;
use App\Models\SubscriptionPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LiveSessionResource extends Resource
{
    protected static ?string $model = LiveSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-video-camera';

    protected static string | \UnitEnum | null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Member Content';

    protected static ?string $modelLabel = 'Member Content';

    protected static ?string $pluralModelLabel = 'Member Content';

    protected static ?string $slug = 'member-content';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Content')
                ->description('Add one live class link or video for your members.')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 2])
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->placeholder('e.g. Morning Yoga Class')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Hidden::make('video_type')
                        ->default('uploaded_video'),

                    Forms\Components\Hidden::make('metadata.member_content')
                        ->default(true),

                    Forms\Components\Select::make('metadata.content_kind')
                        ->label('What do you want to add?')
                        ->options([
                            'video' => 'Video',
                            'meeting' => 'Meeting link',
                        ])
                        ->default('video')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('video_type', $state === 'meeting' ? 'live_meeting' : 'youtube');
                            $set('metadata.video_source', $state === 'video' ? 'youtube' : null);
                            $set('provider', $state === 'meeting' ? SessionProvider::Zoom->value : SessionProvider::Custom->value);
                            $set('meeting_url', null);
                            $set('meeting_password', null);
                            $set('video_url', null);
                            $set('video_file', null);
                            if ($state === 'video') {
                                $set('scheduled_end_at', null);
                            }
                        })
                        ->columnSpanFull(),

                    Forms\Components\Select::make('metadata.video_source')
                        ->label('Video format / source')
                        ->options([
                            'youtube' => '🎥 YouTube link (Fastest & recommended)',
                            'external_video' => '🌐 Direct video URL (MP4 / WebM / CDN)',
                            'uploaded_video' => '📁 Upload video file (MP4 / WebM from computer)',
                        ])
                        ->default('youtube')
                        ->required(fn (Get $get): bool => $get('metadata.content_kind') === 'video')
                        ->visible(fn (Get $get): bool => $get('metadata.content_kind') === 'video')
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('video_type', $state ?: 'uploaded_video');
                        }),

                    Forms\Components\Select::make('provider')
                        ->label('Live platform')
                        ->options([
                            SessionProvider::Zoom->value => 'Zoom',
                            SessionProvider::GoogleMeet->value => 'Google Meet',
                            SessionProvider::MicrosoftTeams->value => 'Microsoft Teams',
                            SessionProvider::Jitsi->value => 'Jitsi',
                            SessionProvider::Custom->value => 'Other',
                        ])
                        ->default(SessionProvider::Custom->value)
                        ->required(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting')
                        ->visible(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('meeting_url')
                        ->label('Live class link')
                        ->url()
                        ->placeholder('https://zoom.us/... or https://meet.google.com/...')
                        ->required(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting')
                        ->visible(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('meeting_password')
                        ->label('Passcode (optional)')
                        ->visible(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting'),

                    Forms\Components\TextInput::make('video_url')
                        ->label('Video URL')
                        ->url()
                        ->placeholder('YouTube URL or direct MP4/WebM URL')
                        ->helperText('Paste a full YouTube URL (e.g. https://www.youtube.com/watch?v=...) or CDN video link.')
                        ->required(fn (Get $get): bool => in_array($get('metadata.video_source'), ['youtube', 'external_video'], true))
                        ->visible(fn (Get $get): bool => in_array($get('metadata.video_source'), ['youtube', 'external_video'], true))
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('video_file')
                        ->label('Select video file')
                        ->disk('public')
                        ->directory('session-videos')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg'])
                        ->maxSize(500 * 1024)
                        ->helperText('💡 Tip: For 0s upload time and instant streaming, select "YouTube link" above. For local file upload, MP4/WebM is supported up to 500MB.')
                        ->required(fn (Get $get): bool => $get('metadata.video_source') === 'uploaded_video')
                        ->visible(fn (Get $get): bool => $get('metadata.video_source') === 'uploaded_video')
                        ->columnSpanFull(),
                ]),

            Section::make('Membership Access')
                ->description('Choose which active membership can open this content.')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 2])
                ->schema([
                    Forms\Components\Hidden::make('min_plan_tier')
                        ->default('custom'),

                    Forms\Components\CheckboxList::make('allowed_plans')
                        ->label('Access')
                        ->options(fn (): array => SubscriptionPlan::query()->active()->ordered()->pluck('name', 'slug')->all())
                        ->helperText('Select one or more memberships that can access this content.')
                        ->required()
                        ->minItems(1)
                        ->columns(['default' => 1, 'sm' => 2])
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            LiveSessionStatus::Draft->value => 'Draft',
                            LiveSessionStatus::Scheduled->value => 'Published',
                        ])
                        ->default(LiveSessionStatus::Draft->value)
                        ->required(),

                    Forms\Components\DateTimePicker::make('scheduled_start_at')
                        ->label('Start / Publish time')
                        ->default(now())
                        ->required(),

                    Forms\Components\DateTimePicker::make('scheduled_end_at')
                        ->label('End time (Live meetings only)')
                        ->default(now()->addHour())
                        ->required(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting')
                        ->visible(fn (Get $get): bool => $get('metadata.content_kind') === 'meeting')
                        ->after('scheduled_start_at')
                        ->nullable(),
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

                Tables\Columns\TextColumn::make('video_type')
                    ->label('Content')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => match ($record->metadata['content_kind'] ?? null) {
                        'meeting' => 'Meeting link',
                        'video' => 'Video',
                        default => match ($state) {
                            'live_meeting' => 'Meeting link',
                            'youtube', 'external_video', 'uploaded_video' => 'Video',
                            default => 'Content',
                        },
                    })
                    ->color(fn ($state) => match ($state) {
                        'live_meeting' => 'primary',
                        'youtube' => 'danger',
                        'external_video', 'uploaded_video' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('allowed_plans')
                    ->label('Access')
                    ->badge()
                    ->getStateUsing(function (LiveSession $record): string {
                        if ($record->min_plan_tier === 'all') {
                            return 'All Members';
                        }

                        $plans = $record->allowed_plans;
                        if (is_string($plans)) {
                            $decoded = json_decode($plans, true);
                            $plans = is_array($decoded) ? $decoded : [$plans];
                        } elseif (! is_array($plans)) {
                            $plans = $record->min_plan_tier && $record->min_plan_tier !== 'custom' ? [$record->min_plan_tier] : [];
                        }

                        $slugs = array_values(array_filter(array_map(
                            static fn (mixed $s): string => Str::slug((string) $s),
                            (array) $plans
                        )));

                        if (empty($slugs)) {
                            return $record->is_locked_for_non_members ? 'Members Only' : 'Free / Public';
                        }

                        $planNames = SubscriptionPlan::query()
                            ->whereIn('slug', $slugs)
                            ->orderBy('sort_order')
                            ->pluck('name')
                            ->all();

                        return ! empty($planNames)
                            ? implode(', ', $planNames)
                            : implode(', ', array_map('strtoupper', $slugs));
                    })
                    ->color('success')
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_locked_for_non_members')
                    ->label('Gated')
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('scheduled_start_at')
                    ->label('Start time')
                    ->dateTime('d M, h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_end_at')
                    ->label('End time')
                    ->getStateUsing(function (LiveSession $record): string {
                        $isMeeting = $record->video_type === 'live_meeting' || ($record->metadata['content_kind'] ?? null) === 'meeting';
                        if (! $isMeeting) {
                            return '—';
                        }
                        return $record->scheduled_end_at ? $record->scheduled_end_at->format('d M, h:i A') : '—';
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('video_type')
                    ->label('Content')
                    ->options([
                        'live_meeting' => 'Live link',
                        'youtube' => 'YouTube',
                        'external_video' => 'Video link',
                        'uploaded_video' => 'Uploaded video',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        LiveSessionStatus::Draft->value => 'Draft',
                        LiveSessionStatus::Scheduled->value => 'Published',
                    ]),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Add content')
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

    public static function getEloquentQuery(): Builder
    {
        LiveSession::clearExpiredMeetingLinks();

        return parent::getEloquentQuery()
            ->whereJsonContains('metadata->member_content', true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveSessions::route('/'),
            'create' => Pages\CreateLiveSession::route('/create'),
            'edit' => Pages\EditLiveSession::route('/{record}/edit'),
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
        // The Filament panel already protects navigation and routes with the
        // admin guard. Keep this true so the resource is not hidden while the
        // panel is building its navigation tree.
        return true;
    }
}
