<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exchangerates extends Model
{
    protected $fillable = [
        'currency_id',
        'date',
        'rate_to_pivot',
        'source'
    ];

    protected $casts = [
        'date' => 'date',
        'rate_to_pivot' => 'decimal:12',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
