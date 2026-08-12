<?php

use App\Models\Bank;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

function makeExpense(Team $team, Bank $bank, Category $category, string $amount, string $date): void
{
    Transaction::create([
        'team_id' => $team->id, 'bank_id' => $bank->id, 'currency_id' => $bank->currency_id,
        'category_id' => $category->id, 'type' => 'expense',
        'amount' => $amount, 'rate_to_base' => '1', 'base_amount' => $amount, 'date' => $date,
    ]);
}

beforeEach(function () {
    $this->usd = Currency::create([
        'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
        'rate_to_pivot' => '1', 'decimal_places' => 2, 'is_active' => true,
    ]);

    $user = User::factory()->create();

    $this->team = Team::create([
        'name' => 'Test Team', 'owner_id' => $user->id, 'base_currency_id' => $this->usd->id,
    ]);

    $this->bank = Bank::create([
        'team_id' => $this->team->id, 'user_id' => $user->id, 'currency_id' => $this->usd->id,
        'name' => 'Cash', 'opening_balance' => '10000', 'opening_balance_date' => now(),
    ]);

    $this->category = Category::create([
        'team_id' => $this->team->id, 'user_id' => $user->id, 'name' => 'Test Category', 'type' => 'expense',
    ]);

    $this->budget = Budget::create([
        'team_id' => $this->team->id, 'category_id' => $this->category->id, 'amount_limit' => '200',
    ]);
});

it('only counts expenses from this category within the current calendar month', function () {
    $this->travelTo(Carbon::parse('2026-08-15'));

    makeExpense($this->team, $this->bank, $this->category, '50', '2026-08-01'); // bulan ini
    makeExpense($this->team, $this->bank, $this->category, '30', '2026-08-14'); // bulan ini
    makeExpense($this->team, $this->bank, $this->category, '999', '2026-07-31'); // bulan lalu, gak keitung
    makeExpense($this->team, $this->bank, $this->category, '999', '2026-09-01'); // bulan depan, gak keitung

    expect($this->budget->spent())->toBe('80.0000');
});

it('computes percent used and buckets the status (ok / warning / over)', function () {
    $this->travelTo(Carbon::parse('2026-08-15'));

    makeExpense($this->team, $this->bank, $this->category, '90', '2026-08-01'); // 90/200 = 45%
    expect($this->budget->percentUsed())->toBe('45.00')
        ->and($this->budget->status())->toBe('ok');

    makeExpense($this->team, $this->bank, $this->category, '70', '2026-08-02'); // total 160/200 = 80%
    expect($this->budget->status())->toBe('warning');

    makeExpense($this->team, $this->bank, $this->category, '50', '2026-08-03'); // total 210/200 = 105%
    expect($this->budget->status())->toBe('over');
});

it('ignores income and transfer rows for the same category/month', function () {
    $this->travelTo(Carbon::parse('2026-08-15'));

    Transaction::create([
        'team_id' => $this->team->id, 'bank_id' => $this->bank->id, 'currency_id' => $this->usd->id,
        'category_id' => $this->category->id, 'type' => 'income',
        'amount' => '500', 'rate_to_base' => '1', 'base_amount' => '500', 'date' => '2026-08-05',
    ]);

    expect($this->budget->spent())->toBe('0.0000');
});
