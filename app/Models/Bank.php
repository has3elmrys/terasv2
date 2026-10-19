<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'currency_id',
        'name',
        'logo',
        'opening_balance',
        'opening_balance_date',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'opening_balance_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class,'team_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function transactions()

    {
        return $this->hasMany(Transaction::class);
    }

    public static function booted()
    {
        static::creating(function (Bank $bank): void
        {
            $bank->user_id ??= auth()->id();

        });

        static::saved(function (Bank $bank): void
            {
                if (! $bank->wasRecentlyCreated
                    && ! $bank->wasChanged(['opening_balance', 'opening_balance_date', 'currency_id'])) {
                    return;
                }

                Transaction::withTrashed()
                    ->where('bank_id', $bank->id)
                    ->where('type', 'opening')
                    ->forceDelete();

                $opening = (string) ($bank->opening_balance ?? '0');

                if ($opening === '' || bccomp($opening, '0', 4) === 0) {
                    return;
                }

                $rateToBase = bcdiv(
                    $bank->currency->rate_to_pivot,
                    $bank->team->baseCurrency->rate_to_pivot,
                    12
                );

                Transaction::create([
                    'team_id'      => $bank->team_id,
                    'bank_id'      => $bank->id,
                    'currency_id'  => $bank->currency_id,
                    'category_id'  => null,
                    'type'         => 'opening',
                    'amount'       => $opening,
                    'rate_to_base' => $rateToBase,
                    'base_amount'  => bcmul($opening, $rateToBase, 4),
                    'date'         => $bank->opening_balance_date ?? $bank->created_at,
                ]);
            });
    }

    public function balance(): string
    {
        // $balance = $this->opening_balance;
        $balance='0';

        foreach ($this->transactions as $t)
            {
                $balance = in_array($t->type, ['income', 'transfer_in','opening'])
                ? bcadd($balance, $t->amount, 4)
                : bcsub($balance, $t->amount, 4);
            }

            return $balance;
    }

    public function hasSufficientBalance(string $amount): bool
    {
        return bccomp(bcsub($this->balance(), $amount, 4), '0', 4) !== -1;
    }
}
