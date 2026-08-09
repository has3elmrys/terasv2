<?php

namespace App\Filament\Team\Resources\Lenders\Pages;

use App\Filament\Team\Resources\Lenders\LenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLenders extends ListRecords
{
    protected static string $resource = LenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
