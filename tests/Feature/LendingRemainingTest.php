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

    $this->user = User::factory()->create();

    $this->team = Team::create([
        'name' => 'Test Team',
        'owner_id' => $this->user->id,
        'base_currency_id' => $this->usd->id,
    ]);

    $this->cashBank = Bank::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'currency_id' => $this->usd->id,
        'name' => 'Cash',
        'opening_balance' => '1000',
        'opening_balance_date' => now(),
    ]);

    $this->lender = Lender::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'name' => 'Test Lender',
    ]);
});

function recordTestPayment(Lending $lending, string $amount): void
{
    $ghostType = $lending->direction === 'lend' ? 'receivable' : 'payable';
    $ghostBank = Bank::where('team_id', $lending->team_id)
        ->where('currency_id', $lending->currency_id)
        ->where('type', $ghostType)
        ->first();

    $legs = $lending->direction === 'lend'
        ? [['bank_id' => $ghostBank->id, 'type' => 'transfer_out'], ['bank_id' => $lending->bank_id, 'type' => 'transfer_in']]
        : [['bank_id' => $lending->bank_id, 'type' => 'transfer_out'], ['bank_id' => $ghostBank->id, 'type' => 'transfer_in']];

    foreach ($legs as $leg) {
        Transaction::create([
            'team_id' => $lending->team_id,
            'bank_id' => $leg['bank_id'],
            'currency_id' => $lending->currency_id,
            'type' => $leg['type'],
            'amount' => $amount,
            'rate_to_base' => '1',
            'base_amount' => $amount,
            'date' => now(),
            'lending_id' => $lending->id,
        ]);
    }
}

it('starts fully owed on a lend', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '50', 'date' => now(),
    ]);

    expect($lending->signedRemaining())->toBe('50.0000');
    expect($lending->remaining())->toBe('50.0000');
});

it('reduces signed remaining as a lend gets repaid', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '50', 'date' => now(),
    ]);

    recordTestPayment($lending, '20');

    expect($lending->signedRemaining())->toBe('30.0000');
});

it('goes negative when a lend is overpaid, but remaining() still shows a positive number', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'lend', 'amount' => '50', 'date' => now(),
    ]);

    recordTestPayment($lending, '70');

    expect($lending->signedRemaining())->toBe('-20.0000')
        ->and($lending->remaining())->toBe('20.0000');
});

it('starts fully owed on a borrow too, despite the ghost bank net being negative underneath', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'borrow', 'amount' => '50', 'date' => now(),
    ]);

    expect($lending->signedRemaining())->toBe('50.0000');
});

it('goes negative when a borrow is overpaid', function () {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => 'borrow', 'amount' => '50', 'date' => now(),
    ]);

    recordTestPayment($lending, '70');

    expect($lending->signedRemaining())->toBe('-20.0000');
});

it('reaches exactly zero when fully repaid, for both directions', function (string $direction) {
    $lending = Lending::create([
        'team_id' => $this->team->id, 'user_id' => $this->user->id, 'lender_id' => $this->lender->id,
        'bank_id' => $this->cashBank->id, 'direction' => $direction, 'amount' => '50', 'date' => now(),
    ]);

    recordTestPayment($lending, '50');

    expect($lending->signedRemaining())->toBe('0.0000');
})->with(['lend', 'borrow']);
