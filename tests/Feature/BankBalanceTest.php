<?php

use App\Models\Bank;
use App\Models\Currency;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->idr = Currency::create([
        'code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp',
        'rate_to_pivot' => '0.00006', 'decimal_places' => 0, 'is_active' => true,
    ]);

    $user = User::factory()->create();

    $this->team = Team::create([
        'name' => 'Test Team',
        'owner_id' => $user->id,
        'base_currency_id' => $this->idr->id,
    ]);

    $this->bank = Bank::create([
        'team_id' => $this->team->id,
        'user_id' => $user->id,
        'currency_id' => $this->idr->id,
        'name' => 'Test Bank',
        'opening_balance' => '1000000',
        'opening_balance_date' => now(),
    ]);
});

it('starts at opening balance when there are no transactions', function () {
    expect($this->bank->balance())->toBe('1000000.0000');
});

it('adds income and transfer_in to the balance', function () {
    Transaction::create([
        'team_id' => $this->team->id, 'bank_id' => $this->bank->id, 'currency_id' => $this->idr->id,
        'type' => 'income', 'amount' => '500000', 'rate_to_base' => '1', 'base_amount' => '500000', 'date' => now(),
    ]);

    expect($this->bank->balance())->toBe('1500000.0000');
});

it('subtracts expense and transfer_out from the balance', function () {
    Transaction::create([
        'team_id' => $this->team->id, 'bank_id' => $this->bank->id, 'currency_id' => $this->idr->id,
        'type' => 'expense', 'amount' => '200000', 'rate_to_base' => '1', 'base_amount' => '200000', 'date' => now(),
    ]);

    expect($this->bank->balance())->toBe('800000.0000');
});

it('reports insufficient balance when amount exceeds balance', function () {
    expect($this->bank->hasSufficientBalance('2000000'))->toBeFalse();
});

it('reports sufficient balance when amount fits', function () {
    expect($this->bank->hasSufficientBalance('500000'))->toBeTrue();
});

it('treats an exact match as sufficient', function () {
    expect($this->bank->hasSufficientBalance('1000000'))->toBeTrue();
});
