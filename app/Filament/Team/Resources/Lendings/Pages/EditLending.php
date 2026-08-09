<?php

namespace App\Filament\Team\Resources\Lendings\Pages;

use App\Filament\Team\Resources\Lendings\LendingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLending extends EditRecord
{
    protected static string $resource = LendingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
