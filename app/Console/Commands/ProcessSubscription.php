<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// #[Signature('app:process-subscription')]
// #[Description('Command description')]
class ProcessSubscription extends Command
{
protected $signature = 'subscriptions:process';

    protected $description = 'Generate transactions for due subscriptions';

    public function handle(): void
    {
        $dueSubscriptions = Subscription::where('status', 'active')
            ->where('next_date', '<=', today())
            ->get();

        foreach ($dueSubscriptions as $subscription) {
            try {
                while ($subscription->next_date <= today()) {
                    $this->generateOccurrence($subscription);
                    $subscription->next_date = $subscription->nextOccurrenceAfter($subscription->next_date);
                    $subscription->save();
                }
            } catch (\Throwable $e) {
                report($e);
                $this->error("Subscription #{$subscription->id} gagal: {$e->getMessage()}");
            }
        }
    }

    protected function generateOccurrence(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $rateToBase = $subscription->rate_to_base
                ?? bcdiv($subscription->currency->rate_to_pivot, $subscription->team->baseCurrency->rate_to_pivot, 12);

            $baseAmount = bcmul($subscription->amount, $rateToBase, 4);

            Transaction::create([
                'team_id' => $subscription->team_id,
                'user_id' => $subscription->user_id,
                'bank_id' => $subscription->bank_id,
                'category_id' => $subscription->category_id,
                'currency_id' => $subscription->currency_id,
                'type' => optional($subscription->category)->type ?? 'expense',
                'amount' => $subscription->amount,
                'rate_to_base' => $rateToBase,
                'base_amount' => $baseAmount,
                'date' => $subscription->next_date,
                'note' => $subscription->name,
                'subscription_id' => $subscription->id,
                'occurrence_date' => $subscription->next_date,
            ]);
        });
    }
}
