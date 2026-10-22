<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Filament\Resources\QuestionResource\Pages;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string | \UnitEnum | null $navigationGroup = 'Assessments';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Select::make('question_category_id')
                ->relationship('category', 'name')
                ->label('Category'),
            Textarea::make('question_text')->required()->columnSpanFull(),
            Textarea::make('explanation')->columnSpanFull(),
            TextInput::make('marks')->numeric()->default(1)->required(),
            TextInput::make('negative_marks')->numeric()->default(0),
            Select::make('type')->options(QuestionType::class)->required(),
            Select::make('difficulty')->options(QuestionDifficulty::class)->default('medium')->required(),
            Select::make('status')->options(QuestionStatus::class)->default('draft')->required(),
            KeyValue::make('metadata'),
            Repeater::make('options')
                ->relationship()
                ->schema([
                    TextInput::make('option_text')->required(),
                    Toggle::make('is_correct'),
                    TextInput::make('sort_order')->numeric()->default(0),
                ])
                ->columns(3)
                ->visible(fn ($get) => in_array($get('type'), ['single_choice', 'multiple_choice', 'true_false'])),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('question_text')->limit(60)->searchable(),
            TextColumn::make('category.name')->label('Category'),
            TextColumn::make('type')->badge(),
            TextColumn::make('difficulty')->badge(),
            TextColumn::make('status')->badge(),
            TextColumn::make('marks'),
        ])->actions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}


