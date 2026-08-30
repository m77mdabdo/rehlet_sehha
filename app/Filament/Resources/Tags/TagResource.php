<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Support\Bilingual;
use App\Models\Tag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tags are thin enough to keep their form and table in the resource.
 *
 * A tag is a name and a slug. Splitting that across three files the way the
 * larger resources do would be structure for its own sake.
 */
class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\UnitEnum|null $navigationGroup = 'محتوى الموقع';

    protected static ?int $navigationSort = 13;

    public static function getModelLabel(): string
    {
        return 'وسم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'وسوم المقالات';
    }

    public static function getNavigationLabel(): string
    {
        return 'وسوم المقالات';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الوسم')
                ->schema([
                    Bilingual::text('name', 'الاسم'),

                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('slug')->label('الرابط')->extraAttributes(['dir' => 'ltr']),
                TextColumn::make('posts_count')->counts('posts')->label('عدد المقالات'),
            ])
            ->defaultSort('slug')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
