<?php

namespace App\Services\RateProviders;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FrankfurterRateProvider implements RateProvider
{
    public function name(): string
    {
        return 'frankfurter';
    }

    public function fetch(array $codes): array
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(2, 1000, throw: false)
                ->get('https://api.frankfurter.dev/v1/latest', [
                    'from' => 'USD',
                    'to' => implode(',', $codes),
                ]);
        } catch (ConnectionException $e) {
            report($e);

            return [];
        }

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
