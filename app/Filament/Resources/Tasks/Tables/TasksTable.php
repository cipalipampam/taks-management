<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'todo' => 'gray',
                        'doing' => 'warning',
                        'done' => 'success',
                    })
                    ->sortable(),

                TextColumn::make('deadline')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('assignees.name')
                    ->label('Assigned To')
                    ->listWithLineBreaks()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'todo' => 'To Do',
                        'doing' => 'Doing',
                        'done' => 'Done',
                    ]),

                SelectFilter::make('assignees')
                    ->relationship('assignees', 'name', fn ($query) => $query->role('staff'))
                    ->label('Assigned To'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
