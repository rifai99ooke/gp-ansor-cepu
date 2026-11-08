<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BodyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Badan & Lembaga')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Badan / Lembaga')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        FileUpload::make('logo')
                            ->label('Logo / Lambang')
                            ->disk('public')
                            ->directory('bodies')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->maxSize(4096)
                            ->helperText('Format SVG, PNG, WebP, atau JPG (maks. 4 MB).')
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Deskripsi Lengkap')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
