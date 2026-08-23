<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Staff accounts. Administrators only — see UserPolicy.
 */
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الحساب')
                ->schema([
                    TextInput::make('name')->label('الاسم')->required()->maxLength(120),

                    TextInput::make('email')
                        ->label('الإيميل')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),

                    TextInput::make('password')
                        ->label('كلمة السر')
                        ->password()
                        ->revealable()
                        ->minLength(12)
                        /*
                         * Required only when creating. On edit an empty box
                         * means "leave it alone" — dehydrated only when filled,
                         * so saving a name change cannot blank somebody's
                         * password.
                         */
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->helperText('١٢ حرف على الأقل. سيبيها فاضية وقت التعديل لو مش عايزة تغيريها.'),

                    Select::make('roles')
                        ->label('الصلاحية')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->required()
                        ->options(fn (): array => Role::query()
                            ->pluck('name', 'name')
                            ->map(fn (string $name): string => match ($name) {
                                'admin' => 'مدير',
                                'doctor' => 'دكتورة',
                                'receptionist' => 'استقبال',
                                default => $name,
                            })
                            ->all())
                        ->helperText('الاستقبال مش بتشوف المعلومات الطبية للمرضى.'),
                ])
                ->columns(2),
        ]);
    }
}
