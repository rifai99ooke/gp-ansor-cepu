<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rantings;

use App\Filament\Resources\Rantings\Pages\CreateRanting;
use App\Filament\Resources\Rantings\Pages\EditRanting;
use App\Filament\Resources\Rantings\Pages\ListRantings;
use App\Filament\Resources\Rantings\Schemas\RantingForm;
use App\Filament\Resources\Rantings\Tables\RantingsTable;
use App\Models\Ranting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RantingResource extends Resource
{
    protected static ?string $model = Ranting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Ranting';

    protected static ?string $modelLabel = 'Ranting';

    protected static ?string $pluralModelLabel = 'Ranting';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return RantingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RantingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRantings::route('/'),
            'create' => CreateRanting::route('/create'),
            'edit' => EditRanting::route('/{record}/edit'),
        ];
    }
}
