<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UsersTable
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
                    ->label('Nama Lengkap'),

                TextColumn::make('username')
                    ->label('Username')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->id === auth()->id() || once(static fn (): int => User::query()->count()) <= 1),
            ]);
    }
}
