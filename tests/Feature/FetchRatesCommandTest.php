<?php

use App\Models\Currency;
use App\Models\Exchangerates;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Currency::create([
        'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
        'rate_to_pivot' => '1', 'decimal_places' => 2, 'is_active' => true,
    ]);

    $this->idr = Currency::create([
        'code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp',
        'rate_to_pivot' => '0.00006', 'decimal_places' => 0, 'is_active' => true,
    ]);
});

it('updates the currency rate and logs exchangerates history on a successful fetch', function () {
    Http::fake([
        'https://api.frankfurter.dev/*' => Http::response([
            'date' => '2026-08-11',
            'rates' => ['IDR' => 0.0001],
        ]),
    ]);

    $this->artisan('rates:fetch')->assertExitCode(0);

    $this->idr->refresh();
    expect($this->idr->rate_to_pivot)->toBe('10000.000000000000')
        ->and($this->idr->rate_updated_at)->not->toBeNull()
        ->and(Currency::where('code', 'USD')->first()->rate_to_pivot)->toBe('1.000000000000');

    $this->assertDatabaseHas('exchangerates', [
        'currency_id' => $this->idr->id,
        'source' => 'frankfurter',
    ]);

    $logged = Exchangerates::where('currency_id', $this->idr->id)->first();
    expect($logged->date->toDateString())->toBe('2026-08-11')
        ->and($logged->rate_to_pivot)->toBe('10000.000000000000');
});

it('NEVER falls back to rate 1 when the provider fails — old rate stays exactly where it was', function () {
    Http::fake([
        'https://api.frankfurter.dev/*' => Http::response([], 500),
    ]);

    $this->artisan('rates:fetch')->assertExitCode(1);

    $this->idr->refresh();
    expect($this->idr->rate_to_pivot)->toBe('0.000060000000');

    $this->assertDatabaseMissing('exchangerates', [
        'currency_id' => $this->idr->id,
    ]);
});

it('leaves the old rate alone when a currency is simply missing from the response', function () {
    Http::fake([
        'https://api.frankfurter.dev/*' => Http::response([
            'date' => '2026-08-11',
            'rates' => [],
        ]),
    ]);

    $this->artisan('rates:fetch')->assertExitCode(0);

    $this->idr->refresh();
    expect($this->idr->rate_to_pivot)->toBe('0.000060000000');
});
