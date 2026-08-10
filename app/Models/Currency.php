<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable=[
        'code',
        'name',
        'symbol',
        'rate_to_pivot',
        'decimal_places',
        'is_active',
        'rate_updated_at'
    ];

    protected $cast =[
    'rate_to_pivot' => 'decimal:12',
    'is_active' =>'boolean',
    'rate_updated_at' => 'datetime',
    ];

    public function exchangerates()
    {
        return $this->hasMany(Exchangerates::class);
    }
}
