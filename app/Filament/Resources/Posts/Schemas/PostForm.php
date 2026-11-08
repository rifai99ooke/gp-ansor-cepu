<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Utama')
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Publikasi')
                            ->options([
                                PostType::Berita->value => 'Berita',
                            ])
                            ->default(PostType::Berita->value)
                            ->hidden()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul (16:9)')
                            ->disk('public')
                            ->directory('posts')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->imageEditor()
                            ->imageCropAspectRatio('16:9')
                            ->helperText('Format JPG, PNG, atau WebP (maks. 4 MB).')
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Publikasi')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                PostStatus::Draft->value => 'Draf',
                                PostStatus::Published->value => 'Terbit (Published)',
                            ])
                            ->default(PostStatus::Published->value)
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('Jadikan Berita Utama (Headline)')
                            ->helperText('Ditampilkan di banner besar bagian atas beranda.')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
