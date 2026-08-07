<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    protected static function booted(): void
{
    static::creating(function (Team $team): void {
        $team->slug = static::uniqueSlug($team->slug ?: $team->name);
    });
}


    protected static function uniqueSlug(string $value): string
{
    $base = Str::slug($value);
    $slug = $base;
    $suffix = 2;

    while (static::where('slug', $slug)->exists()) {
        $slug = "{$base}-{$suffix}";
        $suffix++;
    }

    return $slug;
}
}
