<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rantings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RantingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Ranting')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Ranting')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('description')
                            ->label('Keterangan / Lokasi')
                            ->placeholder('Contoh: Desa Sukamaju, Kecamatan Cibadak')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        FileUpload::make('cover')
                            ->label('Foto Bersama (Fotbar)')
                            ->disk('public')
                            ->directory('rantings')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(8192)
                            ->helperText('Format JPG, PNG, atau WebP (maks. 8 MB). Foto bersama seluruh anggota ranting.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Daftar Anggota')
                    ->description('Tambahkan nama dan foto masing-masing anggota ranting.')
                    ->schema([
                        Repeater::make('members')
                            ->label('Anggota')
                            ->relationship('members')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Anggota')
                                    ->required()
                                    ->maxLength(255),

                                FileUpload::make('photo')
                                    ->label('Foto Anggota')
                                    ->disk('public')
                                    ->directory('ranting-members')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->helperText('Format JPG, PNG, atau WebP (maks. 4 MB).'),
                            ])
                            ->columns(2)
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsed()
                            ->collapseAllAction(fn ($action) => $action->label('Lipat Semua'))
                            ->expandAllAction(fn ($action) => $action->label('Buka Semua'))
                            ->addActionLabel('Tambah Anggota')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
