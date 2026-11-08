<?php

declare(strict_types=1);

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumentasi Galeri')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Kegiatan / Foto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DatePicker::make('event_date')
                            ->label('Tanggal Kegiatan')
                            ->default(now())
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label('Foto Dokumentasi')
                            ->disk('public')
                            ->directory('galleries')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(6144)
                            ->required()
                            ->helperText('Unggah foto dokumentasi (rasio 16:9 disarankan, maks. 6 MB).')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Keterangan / Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
