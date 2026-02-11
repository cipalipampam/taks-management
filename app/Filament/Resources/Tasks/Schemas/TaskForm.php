<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Task title'),

                RichEditor::make('description')
                    ->columnSpanFull()
                    ->placeholder('Task description'),

                Select::make('status')
                    ->options([
                        'todo' => 'To Do',
                        'doing' => 'Doing',
                        'done' => 'Done',
                    ])
                    ->default('todo')
                    ->required(),

                DateTimePicker::make('deadline')
                    ->nullable(),

                Select::make('assignees')
                    ->relationship('assignees', 'name', fn ($query) => $query->role('staff'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Assign to staff'),
            ]);
    }
}
