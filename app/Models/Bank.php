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
    }

    public function balance(): string
    {
        $balance = $this->opening_balance;

        foreach ($this->transactions as $t)
            {
                $balance = in_array($t->type, ['income', 'transfer_in'])
                ? bcadd($balance, $t->amount, 4)
                : bcsub($balance, $t->amount, 4);
            }

            return $balance;
    }
}
