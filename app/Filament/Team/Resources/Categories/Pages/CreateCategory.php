<?php

namespace App\Filament\Team\Resources\Categories\Pages;

use App\Filament\Team\Resources\Categories\CategoryResource;
use App\Models\Budget;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected ?string $budgetAmountLimit = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->budgetAmountLimit = $data['budget_amount_limit'] ?? null;
        unset($data['budget_amount_limit']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (filled($this->budgetAmountLimit)) {
            Budget::create([
                'team_id' => $this->record->team_id,
                'category_id' => $this->record->id,
                'amount_limit' => $this->budgetAmountLimit,
            ]);
        }
    }
}
