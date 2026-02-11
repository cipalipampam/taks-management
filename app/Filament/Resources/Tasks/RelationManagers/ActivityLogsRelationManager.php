<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity Log';

    protected static ?string $recordTitleAttribute = 'description';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('tasks.manage') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Event')
                    ->wrap(),
                TextColumn::make('causer.name')
                    ->label('Actor')
                    ->placeholder('-'),
                TextColumn::make('properties')
                    ->label('Details')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : '-')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('At')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
