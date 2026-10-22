<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LiveSessionStatus;
use App\Filament\Resources\VideoLessonResource\Pages;
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

class VideoLessonResource extends Resource
{
    protected static ?string $model = LiveSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-play-circle';

    protected static string | \UnitEnum | null $navigationGroup = 'Members Area';

    protected static ?string $navigationLabel = 'Videos & Masterclasses';

    protected static ?string $modelLabel = 'Video / Masterclass';

    protected static ?string $pluralModelLabel = 'Videos & Masterclasses';

    protected static ?string $slug = 'video-lessons';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->whereIn('video_type', ['youtube', 'uploaded_video', 'replay'])
                    ->orWhereNotNull('video_url')
                    ->orWhereNotNull('video_file')
                    ->orWhereNotNull('replay_url');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Video Content & Format')
                ->description('Add a YouTube video link, upload an MP4/WebM video file, or attach a class replay.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Video / Masterclass Title')
                        ->placeholder('e.g. Therapeutic Spine & Joint Mobility Masterclass')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('video_type')
                        ->label('Video Format')
                        ->options([
                            'youtube' => '▶️ YouTube Video URL',
                            'uploaded_video' => '📁 Upload Video File (MP4 / WebM)',
                            'replay' => '⏪ Replay / Live Class Recording',
                        ])
                        ->default('youtube')
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('status')
                        ->label('Publication Status')
                        ->options(collect(LiveSessionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                        ->default(LiveSessionStatus::Published->value)
                        ->required(),

                    Forms\Components\TextInput::make('video_url')
                        ->label('YouTube Video Link')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=dQw4w9WgXcQ or https://youtu.be/...')
                        ->required(fn (Forms\Get $get) => $get('video_type') === 'youtube')
                        ->visible(fn (Forms\Get $get) => in_array($get('video_type'), ['youtube', null], true))
                        ->helperText('Paste any public or unlisted YouTube video URL. It will automatically be embedded for members.')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('video_file')
                        ->label('Upload Video File (MP4, WebM up to 500MB)')
                        ->directory('session-videos')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska'])
                        ->maxSize(512000)
                        ->required(fn (Forms\Get $get) => $get('video_type') === 'uploaded_video')
                        ->visible(fn (Forms\Get $get) => $get('video_type') === 'uploaded_video')
                        ->helperText('Upload an MP4 or WebM video file directly to the platform.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('replay_url')
                        ->label('Replay / External Video Link (Optional)')
                        ->url()
                        ->placeholder('https://...')
                        ->visible(fn (Forms\Get $get) => $get('video_type') === 'replay')
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('scheduled_start_at')
                        ->label('Publish / Release Date')
                        ->default(now()),

                    Forms\Components\Textarea::make('description')
                        ->label('Description / Learning Outcomes')
                        ->rows(3)
                        ->placeholder('e.g. A focused 45-minute practice designed to decompress the spine, open tight hips, and soothe nervous system fatigue.')
                        ->columnSpanFull(),
                ]),

            Section::make('Membership Plan Access')
                ->description('Specify which membership plans can watch this video.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('min_plan_tier')
                        ->label('Which Plan Can Watch This Video?')
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
                        ->helperText('When enabled, non-paying visitors will see a locked screen and upgrade prompt.')
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

                Tables\Columns\TextColumn::make('video_type')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'youtube' => '▶️ YouTube',
                        'uploaded_video' => '📁 Uploaded Video',
                        'replay' => '⏪ Replay',
                        default => '▶️ Video',
                    })
                    ->color(fn ($state) => match ($state) {
                        'youtube' => 'danger',
                        'uploaded_video' => 'success',
                        'replay' => 'gray',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('video_url')
                    ->label('Video Source')
                    ->limit(32)
                    ->copyable()
                    ->placeholder('Uploaded File')
                    ->color('gray'),

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
                    ->label('Release Date')
                    ->dateTime('d M Y')
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
                Tables\Filters\SelectFilter::make('video_type')
                    ->label('Format')
                    ->options([
                        'youtube' => 'YouTube Video',
                        'uploaded_video' => 'Uploaded Video',
                        'replay' => 'Replay / Archive',
                    ]),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Upload Video / Link')
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
            'index' => Pages\ListVideoLessons::route('/'),
            'create' => Pages\CreateVideoLesson::route('/create'),
            'edit' => Pages\EditVideoLesson::route('/{record}/edit'),
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
