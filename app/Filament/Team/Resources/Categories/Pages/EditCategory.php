<?php

namespace App\Filament\Team\Resources\Categories\Pages;

use App\Filament\Team\Resources\Categories\CategoryResource;
use App\Models\Budget;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected ?string $budgetAmountLimit = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['budget_amount_limit'] = $this->record->budget?->amount_limit;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->budgetAmountLimit = $data['budget_amount_limit'] ?? null;
        unset($data['budget_amount_limit']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (filled($this->budgetAmountLimit)) {
            Budget::updateOrCreate(
                ['team_id' => $this->record->team_id, 'category_id' => $this->record->id],
                ['amount_limit' => $this->budgetAmountLimit]
            );
        } else {
            Budget::where('category_id', $this->record->id)->delete();
        }
    }
}
