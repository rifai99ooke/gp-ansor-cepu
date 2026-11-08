<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Halaman')
                    ->weight('semibold'),

                TextColumn::make('slug')
                    ->label('Lokasi URL')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'visi-misi' => '/profil/visi-misi',
                        'program' => '/program',
                        'dokumen' => '/dokumen',
                        default => '/'.$state,
                    }),

                TextColumn::make('subtitle')
                    ->label('Subjudul')
                    ->limit(45),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
