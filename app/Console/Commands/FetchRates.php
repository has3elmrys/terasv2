<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\ExchangeRates;
use App\Services\RateProviders\RateProvider;
use Illuminate\Console\Command;

class FetchRates extends Command
{
    protected $signature = 'rates:fetch';
    protected $description = 'Fetch daily exchange rates for active currencies';

    public function handle(RateProvider $provider): int
    {
        $currencies = Currency::where('is_active', true)->where('code', '!=', 'USD')->get();

        $rates = $provider->fetch($currencies->pluck('code')->all());

        if (empty($rates)) {
            $this->error("Fetch gagal ({$provider->name()}), gak ada rate diubah.");
            return self::FAILURE;
        }

        $date = $rates['_date'];

        foreach ($currencies as $currency) {
            if (! isset($rates[$currency->code])) {
                $this->warn("Rate {$currency->code} gak ada di response, dilewatin (angka lama dipertahanin).");
                continue;
            }

            ExchangeRates::updateOrCreate(
                ['currency_id' => $currency->id, 'date' => $date],
                ['rate_to_pivot' => $rates[$currency->code], 'source' => $provider->name()]
            );

            $currency->update([
                'rate_to_pivot' => $rates[$currency->code],
                'rate_updated_at' => now(),
            ]);
        }

        Currency::where('code', 'USD')->update(['rate_to_pivot' => 1, 'rate_updated_at' => now()]);

        $this->info("Rates diupdate dari {$provider->name()} buat tanggal {$date}.");
        return self::SUCCESS;
    }
}
