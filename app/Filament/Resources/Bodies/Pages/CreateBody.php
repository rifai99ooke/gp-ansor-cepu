<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodies\Pages;

use App\Filament\Resources\Bodies\BodyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBody extends CreateRecord
{
    protected static string $resource = BodyResource::class;

    protected static bool $canCreateAnother = false;
}
