<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'bank_id',
        'currency_id',
        'category_id',
        'transfer_group_id',
        'rate_to_base',
        'base_amount',
        'date',
        'note',
        'amount',
        'image_path',
        'user_id',
        'type',
        'subscription_id',
        'lending_id',
        'occurrence_date'
    ];


    protected $casts = [
        'amount' => 'decimal:4',
        'rate_to_base' => 'decimal:12',
        'base_amount' => 'decimal:4',
        'date' => 'date',
        'occurrence_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function lending()
    {
        return $this->belongsTo(Lending::class);
    }

protected static bool $syncingTransferGroup = false;


public static bool $cascadingFromLending = false;

protected static function booted(): void
{
    static::creating(function (Transaction $transaction): void {
        $transaction->user_id ??= auth()->id();
        $transaction->currency_id ??= Bank::find($transaction->bank_id)?->currency_id;
    });



    static::deleting(function (Transaction $transaction) {
        if (filled($transaction->lending_id) && ! static::$cascadingFromLending) {
            return false;
        }

        static::syncTransferGroup($transaction, function (Transaction $transaction): void {
            if ($transaction->isForceDeleting()) {
                $transaction->transferSiblings()->withTrashed()->get()
                    ->each->forceDelete();

                return;
            }

            $transaction->transferSiblings()->get()->each->delete();
        });
    });

        static::deleted(function (Transaction $transaction): void {
        $transaction->lending?->refreshStatus();
    });

    static::restored(function (Transaction $transaction): void {
        $transaction->lending?->refreshStatus();
    });

    static::restoring(function (Transaction $transaction): void {
        static::syncTransferGroup($transaction, function (Transaction $transaction): void {
            $transaction->transferSiblings()->onlyTrashed()->get()->each->restore();
        });
    });
}

protected static function syncTransferGroup(Transaction $transaction, callable $callback): void
{
    if (static::$syncingTransferGroup || blank($transaction->transfer_group_id)) {
        return;
    }

    static::$syncingTransferGroup = true;

    try {
        $callback($transaction);
    } finally {
        static::$syncingTransferGroup = false;
    }
}

public function transferSiblings(): \Illuminate\Database\Eloquent\Builder
{
    return static::query()
        ->where('transfer_group_id', $this->transfer_group_id)
        ->whereKeyNot($this->getKey());
}

}
