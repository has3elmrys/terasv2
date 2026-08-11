<?php

namespace App\Filament\Team\Resources\Lendings;

use App\Filament\Team\Resources\Lendings\Pages\CreateLending;
use App\Filament\Team\Resources\Lendings\Pages\EditLending;
use App\Filament\Team\Resources\Lendings\Pages\ListLendings;
use App\Filament\Team\Resources\Lendings\Schemas\LendingForm;
use App\Filament\Team\Resources\Lendings\Tables\LendingsTable;
use App\Models\Lending;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LendingResource extends Resource
{

    protected static ?int $navigationSort = 4;
    protected static ?string $model = Lending::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function form(Schema $schema): Schema
    {
        return LendingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LendingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLendings::route('/'),
            'create' => CreateLending::route('/create'),
            'edit' => EditLending::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
