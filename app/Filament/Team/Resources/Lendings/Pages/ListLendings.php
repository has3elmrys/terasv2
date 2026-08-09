<?php

namespace App\Filament\Team\Resources\Lendings\Pages;

use App\Filament\Team\Resources\Lendings\LendingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLendings extends ListRecords
{
    protected static string $resource = LendingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
