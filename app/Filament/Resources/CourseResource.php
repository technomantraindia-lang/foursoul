<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string | \UnitEnum | null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Course Details')->columns(2)->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    TextInput::make('slug')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('status')
                        ->options(CourseStatus::class)
                        ->required()
                        ->default(CourseStatus::Draft->value)
                        ->native(false),

                    Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

                Section::make('Live Class & Meeting Link (Zoom / Google Meet)')
                    ->description('Members-only access: Visible only to customers with an active paid membership.')
                    ->columns(2)
                    ->schema([
                        Select::make('meeting_platform')
                            ->label('Platform')
                            ->options([
                                'Zoom' => 'Zoom Meeting',
                                'Google Meet' => 'Google Meet',
                                'Microsoft Teams' => 'Microsoft Teams',
                                'YouTube Live' => 'YouTube Live / Stream',
                                'Other' => 'Other / Custom Link',
                            ])
                            ->default('Zoom')
                            ->native(false),

                        TextInput::make('meeting_link')
                            ->label('Meeting URL / Link')
                            ->url()
                            ->placeholder('https://zoom.us/j/123456789 or https://meet.google.com/abc-defg-hij'),

                        TextInput::make('meeting_passcode')
                            ->label('Passcode / Password (Optional)')
                            ->placeholder('e.g. 108108'),

                        TextInput::make('meeting_schedule')
                            ->label('Class Schedule / Timing')
                            ->placeholder('e.g. Daily 06:00 AM - 07:00 AM IST'),

                        Toggle::make('requires_membership')
                            ->label('Requires Paid Membership')
                            ->helperText('When enabled, the meeting link is locked for non-members and only accessible to active paid members.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('meeting_platform')
                    ->label('Live Meeting')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Zoom' => 'info',
                        'Google Meet' => 'success',
                        default => 'gray',
                    })
                    ->placeholder('No Live Link'),

                IconColumn::make('requires_membership')
                    ->label('Membership Locked')
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('success'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (CourseStatus $state): string => match ($state) {
                        CourseStatus::Draft => 'gray',
                        CourseStatus::Published => 'success',
                        CourseStatus::Archived => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CourseStatus::class),

                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No courses yet')
            ->emptyStateDescription('Create your first course to start building the catalogue.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SectionsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
