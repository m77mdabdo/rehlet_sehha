<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('email')->label('الإيميل')->searchable(),
                TextColumn::make('roles.name')
                    ->label('الصلاحية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'مدير',
                        'doctor' => 'دكتورة',
                        'receptionist' => 'استقبال',
                        default => $state,
                    }),

                /*
                 * Whether the second factor is actually set up. An admin
                 * account is REQUIRED to have it, but the requirement is
                 * enforced at login — this column is how the clinic sees, at a
                 * glance, who is still relying on a password alone.
                 */
                IconColumn::make('two_factor')
                    ->label('تحقق بخطوتين')
                    ->getStateUsing(fn (User $record): bool => filled($record->app_authentication_secret))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([]);
    }
}
