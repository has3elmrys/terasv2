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
        'code',
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

    public static function booted()
    {
        static::creating(function (Bank $bank): void
        {
            $bank->user_id = auth()->id();
        });
    }
}
