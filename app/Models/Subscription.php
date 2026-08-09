<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'category_id',
        'bank_id',
        'currency_id',
        'name',
        'amount',
        'rate_to_base',
        'receipt_template',
        'description',
        'frequency',
        'interval',
        'start_date',
        'next_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'rate_to_base' => 'decimal:12',
        'interval' => 'integer',
        'start_date' => 'date',
        'next_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function nextOccurrenceAfter(Carbon $date): Carbon
    {
        return match ($this->frequency) {
            'daily' => $date->copy()->addDays($this->interval),
            'weekly' => $date->copy()->addWeeks($this->interval),
            'monthly' => $date->copy()->addMonthsNoOverflow($this->interval),
            'yearly' => $date->copy()->addYearsNoOverflow($this->interval),
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription): void {
            $subscription->user_id ??= auth()->id();
            $subscription->currency_id ??= Bank::find($subscription->bank_id)?->currency_id;
            $subscription->next_date ??= $subscription->start_date;
        });
    }
}
