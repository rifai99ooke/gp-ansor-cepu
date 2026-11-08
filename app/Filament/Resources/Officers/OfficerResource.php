<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers;

use App\Filament\Resources\Officers\Pages\CreateOfficer;
use App\Filament\Resources\Officers\Pages\EditOfficer;
use App\Filament\Resources\Officers\Pages\ListOfficers;
use App\Filament\Resources\Officers\Schemas\OfficerForm;
use App\Filament\Resources\Officers\Tables\OfficersTable;
use App\Models\Officer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfficerResource extends Resource
{
    protected static ?string $model = Officer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Pengurus';

    protected static ?string $modelLabel = 'Pengurus';

    protected static ?string $pluralModelLabel = 'Pengurus';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return OfficerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficers::route('/'),
            'create' => CreateOfficer::route('/create'),
            'edit' => EditOfficer::route('/{record}/edit'),
        ];
    }
}
