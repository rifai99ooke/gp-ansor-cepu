<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rantings\Pages;

use App\Filament\Resources\Rantings\RantingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRanting extends CreateRecord
{
    protected static string $resource = RantingResource::class;

    protected static bool $canCreateAnother = false;
}
