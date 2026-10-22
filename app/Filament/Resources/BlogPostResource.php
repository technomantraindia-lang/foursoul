<?php

namespace App\Filament\Resources;

use App\Enums\PageStatus;
use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string | \UnitEnum | null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 3;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Section::make('Post')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->placeholder('e.g. 5 Simple Yoga Practices for a Peaceful Morning')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(collect(PageStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()]))
                    ->default(PageStatus::Draft->value)
                    ->required(),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Publication Date & Time'),
            ])->columns(2),

            Section::make('Content')->schema([
                Forms\Components\Textarea::make('excerpt')
                    ->label('Summary / Excerpt')
                    ->rows(2)
                    ->columnSpanFull()
                    ->placeholder('Short summary displayed on the journal cards and homepage...')
                    ->helperText('Short summary shown on the blog cards and search results.'),
                Forms\Components\RichEditor::make('content')
                    ->label('Article Body')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('featured_image_path')
                    ->label('Featured Cover Image (Select File)')
                    ->image()
                    ->disk('public')
                    ->directory('blog-images')
                    ->visibility('public')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->maxSize(5120)
                    ->helperText('Click to select or drag & drop a cover image (JPG, PNG, WebP up to 5MB).')
                    ->columnSpanFull(),
            ]),

            Section::make('Taxonomy')->schema([
                Forms\Components\Select::make('blog_category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
            ])->columns(2),

            Section::make('SEO')->schema([
                Forms\Components\TextInput::make('seo_title')->maxLength(255),
                Forms\Components\Textarea::make('seo_description')->rows(2)->columnSpanFull(),
            ])->columns(1)->collapsible()->collapsed(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image_path')
                    ->label('Cover')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl('https://images.unsplash.com/photo-1545205597-3d9d02c29597?w=100&auto=format&fit=crop&q=60'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->placeholder('General'),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->placeholder('FourSouls Guide'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('reading_time_minutes')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PageStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()])),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
