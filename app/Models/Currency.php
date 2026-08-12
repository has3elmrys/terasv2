<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate_to_pivot',
        'decimal_places',
        'is_active',
        'rate_updated_at',
    ];

    protected $casts = [
        'rate_to_pivot' => 'decimal:12',
        'is_active' => 'boolean',
        'rate_updated_at' => 'datetime',
    ];

    public function exchangerates()
    {
        return $this->hasMany(Exchangerates::class);
    }

    /**
     * Rate buat convert 1 unit currency INI ke $to.
     * amount_in_$to = bcmul(amount_in_this, $this->rateBetween($to), scale)
     */
    public function rateBetween(Currency $to): string
    {
        return bcdiv($this->rate_to_pivot, $to->rate_to_pivot, 12);
    }
}
