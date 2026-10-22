<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\QuizStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Quiz;
use App\Filament\Resources\QuizResource\Pages;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Assessments';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Quiz Details')->schema([
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
                ])->columns(2),

                Section::make('Scoring & Rules')->schema([
                    Forms\Components\TextInput::make('passing_marks')
                        ->numeric()->minValue(0)->default(0)->required(),
                    Forms\Components\TextInput::make('total_marks')
                        ->numeric()->minValue(0)->default(0)->required(),
                    Forms\Components\TextInput::make('max_attempts')
                        ->numeric()->minValue(1)->default(1)->required(),
                    Forms\Components\TextInput::make('duration_minutes')
                        ->numeric()->minValue(0)->default(0)
                        ->helperText('0 = no time limit'),
                    Forms\Components\Toggle::make('shuffle_questions'),
                    Forms\Components\Toggle::make('shuffle_options'),
                    Forms\Components\TextInput::make('random_question_count')
                        ->numeric()->minValue(0)->default(0)
                        ->helperText('0 = use all attached questions'),
                ])->columns(3),

                Section::make('Questions')->schema([
                    Forms\Components\CheckboxList::make('questions')
                        ->relationship('questions', 'question_text')
                        ->searchable()
                        ->bulkToggleable()
                        ->columnSpanFull(),
                ]),

                Section::make('Publishing')->schema([
                    Forms\Components\Select::make('status')
                        ->options(QuizStatus::class)
                        ->default(QuizStatus::Draft->value)
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('attempts_count')->counts('attempts')->label('Attempts'),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(QuizStatus::class),
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
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}


