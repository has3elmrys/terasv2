<?php

use App\Filament\Team\Resources\Banks\Pages\ListBanks;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
beforeEach(function () {
    $this->usd = Currency::create([
        'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
        'rate_to_pivot' => '1', 'decimal_places' => 2, 'is_active' => true,
    ]);

    $this->eur = Currency::create([
        'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€',
        'rate_to_pivot' => '2', 'decimal_places' => 2, 'is_active' => true,
    ]);

    $this->user = User::factory()->create();

    $this->team = Team::create([
        'name' => 'Test Team', 'owner_id' => $this->user->id, 'base_currency_id' => $this->usd->id,
    ]);

    $this->team->users()->attach($this->user);

    $this->fromBank = Bank::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'currency_id' => $this->eur->id,
        'name' => 'Cash EUR', 'opening_balance' => '1000', 'opening_balance_date' => now(),
    ]);

    $this->toBank = Bank::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'currency_id' => $this->usd->id,
        'name' => 'Cash USD', 'opening_balance' => '0', 'opening_balance_date' => now(),
    ]);

     $role = Role::findOrCreate('Vault-Admin', 'web');
    foreach (['ViewAny:Bank', 'View:Bank', 'Create:Bank', 'Update:Bank'] as $permission) {
        $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }
    $this->user->assignRole($role);

    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('team'));
    Filament::setTenant($this->team);
});

it('writes a transfer_out/transfer_in pair converted at the auto rate when exchanging currency', function () {
    // 1 EUR = 2 USD (fixture) -> minta 100 USD masuk = 50 EUR keluar
    Livewire::test(ListBanks::class)
        ->callAction('exchange', data: [
            'from_bank_id' => $this->fromBank->id,
            'to_bank_id' => $this->toBank->id,
            'amount_in' => '100',
            'rate_mode' => 'auto',
            'amount_out' => '50',
        ])
        ->assertHasNoActionErrors();

    $out = Transaction::where('bank_id', $this->fromBank->id)->where('type', 'transfer_out')->first();
    $in = Transaction::where('bank_id', $this->toBank->id)->where('type', 'transfer_in')->first();

    expect($out)->not->toBeNull()
        ->and($in)->not->toBeNull()
        ->and($out->transfer_group_id)->toBe($in->transfer_group_id)
        ->and($out->amount)->toBe('50.0000')
        ->and($out->rate_to_base)->toBe('2.000000000000')
        ->and($out->base_amount)->toBe('100.0000')
        ->and($in->amount)->toBe('100.0000')
        ->and($in->rate_to_base)->toBe('1.000000000000')
        ->and($in->base_amount)->toBe('100.0000');
});

it('blocks the exchange and writes nothing when the source bank has insufficient balance', function () {
    $this->fromBank->update(['opening_balance' => '10']); // kurang dari 50 yang dibutuhin

    Livewire::test(ListBanks::class)
        ->callAction('exchange', data: [
            'from_bank_id' => $this->fromBank->id,
            'to_bank_id' => $this->toBank->id,
            'amount_in' => '100',
            'rate_mode' => 'auto',
            'amount_out' => '50',
        ]);

    expect(Transaction::where('bank_id', $this->fromBank->id)->where('type', 'transfer_out')->exists())->toBeFalse()
        ->and(Transaction::where('bank_id', $this->toBank->id)->where('type', 'transfer_in')->exists())->toBeFalse();
});
