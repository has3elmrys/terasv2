<?php

use App\Models\Bank;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

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
        'team_id' => $this->team->id, 'user_id' => $user->id, 'name' => 'Streaming', 'type' => 'expense',
    ]);
});

it('generates every missed occurrence in one run, not just the latest one', function () {
    $this->travelTo(Carbon::parse('2026-08-15'));

    $subscription = Subscription::create([
        'team_id' => $this->team->id, 'category_id' => $this->category->id, 'bank_id' => $this->bank->id,
        'currency_id' => $this->usd->id, 'name' => 'Netflix', 'amount' => '15',
        'frequency' => 'monthly', 'interval' => 1,
        'start_date' => '2026-05-15', 'next_date' => '2026-05-15', 'status' => 'active',
    ]);

    $this->artisan('subscriptions:process');

    // due: 15 Mei, 15 Jun, 15 Jul, 15 Agu (hari ini, travelTo) = 4 kali sekaligus dalam 1 run
    expect(Transaction::where('subscription_id', $subscription->id)->count())->toBe(4);

    $subscription->refresh();
    expect($subscription->next_date->toDateString())->toBe('2026-09-15');
});

it('is idempotent on the same day and stamps type/base_amount from the category', function () {
    $this->travelTo(Carbon::parse('2026-08-15'));

    $subscription = Subscription::create([
        'team_id' => $this->team->id, 'category_id' => $this->category->id, 'bank_id' => $this->bank->id,
        'currency_id' => $this->usd->id, 'name' => 'Netflix', 'amount' => '15',
        'frequency' => 'monthly', 'interval' => 1,
        'start_date' => '2026-08-15', 'next_date' => '2026-08-15', 'status' => 'active',
    ]);

    $this->artisan('subscriptions:process');
    $this->artisan('subscriptions:process'); // sengaja run 2x, gak boleh dobel

    expect(Transaction::where('subscription_id', $subscription->id)->count())->toBe(1);

    $transaction = Transaction::where('subscription_id', $subscription->id)->first();
    expect($transaction->type)->toBe('expense')
        ->and($transaction->base_amount)->toBe('15.0000')
        ->and($transaction->occurrence_date->toDateString())->toBe('2026-08-15');
});
