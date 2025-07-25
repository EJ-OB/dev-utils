<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class UserResourceForm
{
    /**
     * @return Component[]
     */
    public static function make(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(User::class, 'email', ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('password')
                ->password()
                ->required()
                ->maxLength(255)
                ->visibleOn(['create']),

            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable(),
        ];
    }
}
