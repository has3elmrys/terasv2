<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'type',
        'icon',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class,'team_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

     protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            $category->user_id ??= auth()->id();
        });
    }
}
