<?php

declare(strict_types=1);

namespace App\Enums;

enum PostType: string
{
    case Berita = 'berita';

    public function label(): string
    {
        return match ($this) {
            self::Berita => 'Berita',
        };
    }
}
