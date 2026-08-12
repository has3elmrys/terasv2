<?php

use App\Models\Bank;
use App\Models\Currency;
use App\Models\Lender;
use App\Models\Lending;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;

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

    $this->cashBank = Bank::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'currency_id' => $this->eur->id,
        'name' => 'Cash EUR', 'opening_balance' => '1000', 'opening_balance_date' => now(),
    ]);

    $this->lender = Lender::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'name' => 'Test Lender',
    ]);
});

it('writes a transfer_out/transfer_in pair, converted to base currency, when lending money out', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '100', 'date' => now(),
    ]);

    $transactions = Transaction::where('lending_id', $lending->id)->get();

    expect($transactions)->toHaveCount(2)
        ->and($transactions->pluck('transfer_group_id')->unique())->toHaveCount(1);

    $out = $transactions->firstWhere('type', 'transfer_out');
    $in = $transactions->firstWhere('type', 'transfer_in');

    // 1 EUR = 2 USD (fixture) -> 100 EUR jadi 200 di base_amount
    expect($out->bank_id)->toBe($this->cashBank->id)
        ->and($out->amount)->toBe('100.0000')
        ->and($out->rate_to_base)->toBe('2.000000000000')
        ->and($out->base_amount)->toBe('200.0000')
        ->and($in->amount)->toBe('100.0000')
        ->and($in->base_amount)->toBe('200.0000');

    $ghostBank = Bank::where('team_id', $this->team->id)->where('type', 'receivable')->first();
    expect($ghostBank)->not->toBeNull()
        ->and($ghostBank->name)->toBe('Owed to You (EUR)')
        ->and($in->bank_id)->toBe($ghostBank->id);
});

it('reverses the legs and uses a payable ghost bank when borrowing money', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'borrow', 'amount' => '50', 'date' => now(),
    ]);

    $transactions = Transaction::where('lending_id', $lending->id)->get();
    $out = $transactions->firstWhere('type', 'transfer_out');
    $in = $transactions->firstWhere('type', 'transfer_in');

    $ghostBank = Bank::where('team_id', $this->team->id)->where('type', 'payable')->first();

    expect($ghostBank->name)->toBe('You Owe (EUR)')
        ->and($out->bank_id)->toBe($ghostBank->id)
        ->and($in->bank_id)->toBe($this->cashBank->id)
        ->and($out->base_amount)->toBe('100.0000'); // 50 EUR x 2 = 100 USD
});

it('reuses the same ghost bank across multiple lendings in the same currency', function () {
    Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '10', 'date' => now(),
    ]);
    Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '20', 'date' => now(),
    ]);

    expect(Bank::where('team_id', $this->team->id)->where('type', 'receivable')->count())->toBe(1);
});
