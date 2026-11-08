<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers\Pages;

use App\Filament\Resources\Officers\OfficerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficer extends EditRecord
{
    protected static string $resource = OfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
