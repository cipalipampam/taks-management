<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Full Name'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Email Address'),

                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->maxLength(255)
                    ->placeholder('Password'),

                Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => $query->where('name', '!=', 'admin'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Roles (staff & supervisor)')
                    ->helperText('User dapat memiliki lebih dari satu role (misal staff sekaligus supervisor).')
                    ->dehydrated(true),

                Select::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Permission tambahan')
                    ->helperText('Permission ini ditambahkan langsung ke user tanpa mengubah role.')
                    ->dehydrated(true),
            ]);
    }
}
