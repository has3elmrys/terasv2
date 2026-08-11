<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\Lender;
use App\Models\Lending;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $idr = Currency::where('code', 'IDR')->firstOrFail();
        $usd = Currency::where('code', 'USD')->firstOrFail();

        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        $team = Team::create([
            'name' => 'Demo Household',
            'owner_id' => $demoUser->id,
            'base_currency_id' => $idr->id,
        ]);

        $team->users()->attach($demoUser);

        $categories = $team->categories()->pluck('id', 'name');

        $bankIdr = Bank::create([
            'team_id' => $team->id,
            'user_id' => $demoUser->id,
            'currency_id' => $idr->id,
            'name' => 'Demo Bank',
            'opening_balance' => '5000000',
            'opening_balance_date' => now()->subDays(60),
        ]);

        $bankUsd = Bank::create([
            'team_id' => $team->id,
            'user_id' => $demoUser->id,
            'currency_id' => $usd->id,
            'name' => 'Demo USD Wallet',
            'opening_balance' => '200',
            'opening_balance_date' => now()->subDays(60),
        ]);

        $rateIdrToBase = '1';
        $rateUsdToBase = bcdiv($usd->rate_to_pivot, $idr->rate_to_pivot, 12);

        $rows = [
            ['days' => 55, 'category' => 'Salary', 'type' => 'income', 'amount' => '8000000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 50, 'category' => 'Food', 'type' => 'expense', 'amount' => '150000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 48, 'category' => 'Transport', 'type' => 'expense', 'amount' => '50000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 45, 'category' => 'Shopping', 'type' => 'expense', 'amount' => '350000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 40, 'category' => 'Food', 'type' => 'expense', 'amount' => '120000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 35, 'category' => 'Utilities', 'type' => 'expense', 'amount' => '450000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 30, 'category' => 'Entertainment', 'type' => 'expense', 'amount' => '200000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 25, 'category' => 'Salary', 'type' => 'income', 'amount' => '8000000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 22, 'category' => 'Food', 'type' => 'expense', 'amount' => '180000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 18, 'category' => 'Health', 'type' => 'expense', 'amount' => '250000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 15, 'category' => 'Transport', 'type' => 'expense', 'amount' => '75000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 12, 'category' => 'Salary', 'type' => 'income', 'amount' => '150', 'bank' => $bankUsd, 'currency' => $usd, 'rate' => $rateUsdToBase],
            ['days' => 10, 'category' => 'Food', 'type' => 'expense', 'amount' => '95000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 7, 'category' => 'Travel', 'type' => 'expense', 'amount' => '600000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
            ['days' => 3, 'category' => 'Food', 'type' => 'expense', 'amount' => '110000', 'bank' => $bankIdr, 'currency' => $idr, 'rate' => $rateIdrToBase],
        ];

        foreach ($rows as $row) {
            Transaction::create([
                'team_id' => $team->id,
                'user_id' => $demoUser->id,
                'bank_id' => $row['bank']->id,
                'category_id' => $categories[$row['category']],
                'currency_id' => $row['currency']->id,
                'type' => $row['type'],
                'amount' => $row['amount'],
                'rate_to_base' => $row['rate'],
                'base_amount' => bcmul($row['amount'], $row['rate'], 4),
                'date' => now()->subDays($row['days']),
            ]);
        }

        Subscription::create([
            'team_id' => $team->id,
            'user_id' => $demoUser->id,
            'category_id' => $categories['Subscription'],
            'bank_id' => $bankIdr->id,
            'name' => 'Netflix',
            'amount' => '65000',
            'rate_to_base' => $rateIdrToBase,
            'description' => 'Streaming subscription',
            'frequency' => 'monthly',
            'interval' => 1,
            'start_date' => now()->subDays(45),
            'status' => 'active',
        ]);

        $lender = Lender::create([
            'team_id' => $team->id,
            'user_id' => $demoUser->id,
            'name' => 'Demo Friend',
            'phone' => '+1 555-0100-0000',
        ]);

        Lending::create([
            'team_id' => $team->id,
            'user_id' => $demoUser->id,
            'lender_id' => $lender->id,
            'bank_id' => $bankIdr->id,
            'direction' => 'lend',
            'amount' => '500000',
            'date' => now()->subDays(20),
            'due_date' => now()->addDays(10),
            'note' => 'Demo lending, not yet paid off',
        ]);
    }
}
