<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfficerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pengurus')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap & Gelar')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('position')
                            ->label('Jabatan')
                            ->placeholder('Contoh: Ketua Umum')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('period')
                            ->label('Masa Khidmat / Periode')
                            ->placeholder('Contoh: 2024 - 2029')
                            ->maxLength(100),

                        FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->directory('officers')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->helperText('Format JPG, PNG, atau WebP (maks. 4 MB). Rasio ideal 8:7.')
                            ->columnSpanFull(),

                        RichEditor::make('bio')
                            ->label('Biografi Singkat')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
