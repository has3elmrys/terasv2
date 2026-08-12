<?php

use App\Services\RateProviders\FrankfurterRateProvider;
use Illuminate\Support\Facades\Http;

it('inverts frankfurter rates (USD-per-unit becomes unit-per-USD) and keeps the quote date', function () {
    Http::fake([
        'https://api.frankfurter.app/*' => Http::response([
            'date' => '2026-08-11',
            'rates' => ['IDR' => 0.0001],
        ]),
    ]);

    $rates = (new FrankfurterRateProvider())->fetch(['IDR']);

    expect($rates['_date'])->toBe('2026-08-11')
        ->and($rates['IDR'])->toBe('10000.000000000000');
});

it('returns an empty array when the request fails, instead of a partial/wrong result', function () {
    Http::fake([
        'https://api.frankfurter.app/*' => Http::response([], 500),
    ]);

    $rates = (new FrankfurterRateProvider())->fetch(['IDR']);

    expect($rates)->toBe([]);
});

it('skips a rate that comes back zero instead of dividing by it', function () {
    Http::fake([
        'https://api.frankfurter.app/*' => Http::response([
            'date' => '2026-08-11',
            'rates' => ['IDR' => 0],
        ]),
    ]);

    $rates = (new FrankfurterRateProvider())->fetch(['IDR']);

    expect($rates)->not->toHaveKey('IDR');
});
