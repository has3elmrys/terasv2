<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

    protected static array $reservedSlugs = [
        'login',
        'logout',
        'register',
        'new',
        'profile',
        'admin',
        'up',
        'storage',
        'email-verification',
        'filament'
        ];
    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'base_currency_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

        protected static function booted(): void
    {
        static::creating(function (Team $team): void {
            $team->slug = static::uniqueSlug($team->slug ?: $team->name);
        });

        static::created(function (Team $team): void {
            $defaults = [
                ['name' => 'Salary', 'type' => 'income'],
                ['name' => 'Food', 'type' => 'expense'],
                ['name' => 'Transport', 'type' => 'expense'],
                ['name' => 'Entertainment', 'type' => 'expense'],
                ['name' => 'Shopping', 'type' => 'expense'],
                ['name' => 'Health', 'type' => 'expense'],
                ['name' => 'Education', 'type' => 'expense'],
                ['name' => 'Utilities', 'type' => 'expense'],
                ['name' => 'Travel', 'type' => 'expense'],
                ['name' => 'Subscription', 'type' => 'expense'],
            ];

            foreach ($defaults as $category) {
                $team->categories()->create([
                    'user_id' => $team->owner_id,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ]);
            }
        });
    }


        protected static function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'team';
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, static::$reservedSlugs, true) || static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }


}
