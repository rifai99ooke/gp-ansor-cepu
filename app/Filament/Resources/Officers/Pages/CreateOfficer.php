<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers\Pages;

use App\Filament\Resources\Officers\OfficerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficer extends CreateRecord
{
    protected static string $resource = OfficerResource::class;

    protected static bool $canCreateAnother = false;
}
