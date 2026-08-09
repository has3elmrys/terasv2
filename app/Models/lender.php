<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lender extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'email',
        'phone',
        'note'
    ];


    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }

    protected static function booted()
    {
        static::creating(function (Lender $lender)
        {
            $lender->user_id ??= auth()->id();
        });
    }
}
