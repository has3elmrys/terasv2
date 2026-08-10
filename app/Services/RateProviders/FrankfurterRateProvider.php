<?php

namespace App\Services\RateProviders;

use Illuminate\Support\Facades\Http;

class FrankfurterRateProvider implements RateProvider
{
    public function name(): string
    {
        return 'frankfurter';
    }

    public function fetch(array $codes): array
    {
        $response = Http::timeout(10)->get('https://api.frankfurter.app/latest', [
            'from' => 'USD',
            'to' => implode(',', $codes),
        ]);

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();
        $rates = [];

        foreach ($data['rates'] ?? [] as $code => $rateFromUsd) {
            if ($rateFromUsd > 0) {
                $rates[$code] = bcdiv('1', (string) $rateFromUsd, 12);
            }
        }

        $rates['_date'] = $data['date'] ?? today()->toDateString();

        return $rates;
    }
}
