<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Articles are the one content type that is NOT drag-ordered.
 *
 * They have no sort_order column and should not have one: articles are ordered
 * by when they were published, which is a fact rather than a preference, and a
 * blog whose order is hand-arranged stops being a chronology.
 */
class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable()->wrap(),
                TextColumn::make('category')->label('التصنيف')->toggleable(),
                TextColumn::make('published_at')
                    ->label('اتنشر في')
                    ->dateTime('j F Y', timezone: config('clinic.timezone'))
                    ->placeholder('مسودة')
                    ->sortable(),
                IconColumn::make('is_featured')->label('مميز')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label('منشور')
                    ->nullable()
                    ->attribute('published_at'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
