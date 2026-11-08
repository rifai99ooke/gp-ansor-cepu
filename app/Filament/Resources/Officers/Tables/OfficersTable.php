<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class OfficersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordUrl(null)
            ->reorderable('sort_order')
            ->beforeReordering(function ($livewire): void {
                $livewire->tableSort = null;
            })
            ->reorderRecordsTriggerAction(fn (Action $action): Action => $action->hidden())
            ->defaultSort('sort_order')
            ->columns([
                ViewColumn::make('drag_handle')
                    ->label('')
                    ->view('filament.tables.columns.drag-handle')
                    ->disabledClick()
                    ->width('48px'),

                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nama Pengurus'),

                TextColumn::make('position')
                    ->label('Jabatan')
                    ->badge()
                    ->color('success'),

                TextColumn::make('period')
                    ->label('Periode'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
