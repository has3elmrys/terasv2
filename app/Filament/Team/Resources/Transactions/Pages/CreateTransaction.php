<?php

namespace App\Filament\Team\Resources\Transactions\Pages;

use App\Filament\Team\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
