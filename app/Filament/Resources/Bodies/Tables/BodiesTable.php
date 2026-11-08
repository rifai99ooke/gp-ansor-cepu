<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodies\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class BodiesTable
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

                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nama Badan / Lembaga'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
