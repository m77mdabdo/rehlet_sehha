<?php

declare(strict_types=1);

namespace App\Filament\Resources\Testimonials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /*
             * Drag to reorder. sort_order is what the public site orders by,
             * so this is the only screen that controls the order things appear
             * in — typing numbers into a field to reorder a list is how you get
             * two items both called 3.
             */
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('context')->label('السياق')->limit(40),
                TextColumn::make('rating')->label('التقييم'),
                IconColumn::make('is_active')->label('ظاهر')->boolean(),
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
