<?php

use App\Models\Subscription;
use Illuminate\Support\Carbon;

it('advances by the given interval per frequency', function (string $frequency, int $interval, string $from, string $expected) {
    $subscription = new Subscription(['frequency' => $frequency, 'interval' => $interval]);

    expect($subscription->nextOccurrenceAfter(Carbon::parse($from))->toDateString())->toBe($expected);
})->with([
    'daily x3' => ['daily', 3, '2026-08-01', '2026-08-04'],
    'weekly x2' => ['weekly', 2, '2026-08-01', '2026-08-15'],
    'monthly x1' => ['monthly', 1, '2026-08-01', '2026-09-01'],
    'yearly x1' => ['yearly', 1, '2026-08-01', '2027-08-01'],
]);

it('clamps month-end overflow instead of rolling into the next month (Jan 31 + 1 month)', function () {
    $subscription = new Subscription(['frequency' => 'monthly', 'interval' => 1]);

    // 2026 bukan tahun kabisat, Feb cuma 28 hari — HARUS clamp ke 28, bukan lompat ke 3 Maret
    expect($subscription->nextOccurrenceAfter(Carbon::parse('2026-01-31'))->toDateString())->toBe('2026-02-28');
});

it('clamps leap-day overflow the same way for yearly frequency (Feb 29 + 1 year)', function () {
    $subscription = new Subscription(['frequency' => 'yearly', 'interval' => 1]);

    expect($subscription->nextOccurrenceAfter(Carbon::parse('2024-02-29'))->toDateString())->toBe('2025-02-28');
});
