<?php

use App\Models\Currency;

it('returns exactly 1 when converting a currency to itself', function () {
    $usd = new Currency(['rate_to_pivot' => '1']);

    expect($usd->rateBetween($usd))->toBe('1.000000000000');
});

it('computes the real IDR-to-USD rate used across the app', function () {
    $idr = new Currency(['rate_to_pivot' => '0.00006']);
    $usd = new Currency(['rate_to_pivot' => '1']);

    expect($idr->rateBetween($usd))->toBe('0.000060000000');
});

it('is the reciprocal of the reverse conversion', function () {
    $usd = new Currency(['rate_to_pivot' => '1']);
    $twoPerUsd = new Currency(['rate_to_pivot' => '2']);

    expect($usd->rateBetween($twoPerUsd))->toBe('0.500000000000')
        ->and($twoPerUsd->rateBetween($usd))->toBe('2.000000000000');
});

it('feeds correctly into the base_amount formula (bcmul) used by transactions', function () {
    $idr = new Currency(['rate_to_pivot' => '0.00006']);
    $usd = new Currency(['rate_to_pivot' => '1']);

    $rateToBase = $idr->rateBetween($usd);
    $baseAmount = bcmul('500000', $rateToBase, 4);

    expect($baseAmount)->toBe('30.0000');
});
