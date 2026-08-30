<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('slug')->label('الرابط')->extraAttributes(['dir' => 'ltr'])->toggleable(),

                // Counted rather than guessed: a category with no articles is
                // a page with nothing on it, and it is worth seeing at a glance.
                TextColumn::make('posts_count')->counts('posts')->label('عدد المقالات'),

                IconColumn::make('is_active')->label('ظاهر')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
