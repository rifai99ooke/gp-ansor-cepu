<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodies;

use App\Filament\Resources\Bodies\Pages\CreateBody;
use App\Filament\Resources\Bodies\Pages\EditBody;
use App\Filament\Resources\Bodies\Pages\ListBodies;
use App\Filament\Resources\Bodies\Schemas\BodyForm;
use App\Filament\Resources\Bodies\Tables\BodiesTable;
use App\Models\Body;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BodyResource extends Resource
{
    protected static ?string $model = Body::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Badan & Lembaga';

    protected static ?string $modelLabel = 'Badan & Lembaga';

    protected static ?string $pluralModelLabel = 'Badan & Lembaga';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return BodyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BodiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBodies::route('/'),
            'create' => CreateBody::route('/create'),
            'edit' => EditBody::route('/{record}/edit'),
        ];
    }
}
