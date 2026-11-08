<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPostsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Berita Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()->ofType(PostType::Berita)->latest('published_at')->orderByDesc('id')->limit(6)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->weight('semibold')
                    ->limit(85),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (PostStatus|string $state): string => $state instanceof PostStatus ? $state->label() : ucfirst((string) $state))
                    ->color(fn (PostStatus|string $state): string => ($state === PostStatus::Published || $state === 'published') ? 'success' : 'gray'),

                TextColumn::make('views_count')
                    ->label('Dilihat')
                    ->numeric(),

                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime('d M Y'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Ubah')
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
