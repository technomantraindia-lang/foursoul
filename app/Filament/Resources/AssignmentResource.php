<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\CourseLesson;
use App\Filament\Resources\AssignmentResource\Pages;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Assessments';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Assignment Details')->schema([
                    Forms\Components\Select::make('course_id')
                        ->label('Course')
                        ->relationship('course', 'title')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Forms\Components\Select::make('course_lesson_id')
                        ->label('Lesson (optional)')
                        ->options(fn (Get $get) => CourseLesson::query()
                            ->whereHas('section', fn ($query) => $query->where('course_id', $get('course_id')))
                            ->pluck('title', 'id'))
                        ->searchable()
                        ->nullable(),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('instructions')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Rules')->schema([
                    Forms\Components\DateTimePicker::make('due_at'),
                    Forms\Components\TextInput::make('max_marks')
                        ->numeric()->minValue(0)->default(100)->required(),
                    Forms\Components\Toggle::make('allow_resubmission'),
                    Forms\Components\TextInput::make('max_attempts')
                        ->numeric()->minValue(1)->default(1)->required(),
                    Forms\Components\TagsInput::make('accepted_file_types')
                        ->helperText('e.g. pdf, docx, zip'),
                    Forms\Components\TextInput::make('max_file_size_kb')
                        ->numeric()->minValue(1)
                        ->suffix('KB'),
                ])->columns(3),

                Section::make('Publishing')->schema([
                    Forms\Components\Select::make('status')
                        ->options(AssignmentStatus::class)
                        ->default(AssignmentStatus::Draft->value)
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->sortable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('submissions_count')->counts('submissions')->label('Submissions'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(AssignmentStatus::class),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}


