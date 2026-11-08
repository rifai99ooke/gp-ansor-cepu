<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rantings\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class RantingsTable
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

                TextColumn::make('name')
                    ->label('Nama Ranting'),

                TextColumn::make('members_count')
                    ->label('Jumlah Anggota')
                    ->counts('members')
                    ->badge()
                    ->color('success'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
