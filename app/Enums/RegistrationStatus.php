<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStatus: string
{
    case Open = 'open';
    case Upcoming = 'upcoming';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Dibuka',
            self::Upcoming => 'Segera',
            self::Closed => 'Ditutup',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-green-100 text-green-800 border-green-200',
            self::Upcoming => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Closed => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}
