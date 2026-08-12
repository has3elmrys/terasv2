<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'category_id',
        'amount_limit',
    ];

    protected $casts = [
        'amount_limit' => 'decimal:4',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Budget $budget): void {
            $budget->user_id ??= auth()->id();
        });
    }

    public function spent(): string
    {
        return Transaction::where('team_id', $this->team_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->get()
            ->reduce(fn (string $carry, Transaction $t) => bcadd($carry, $t->base_amount, 4), '0.0000');
    }

    public function percentUsed(): string
    {
        if (bccomp($this->amount_limit, '0', 4) === 0) {
            return '0';
        }

        return bcmul(bcdiv($this->spent(), $this->amount_limit, 6), '100', 2);
    }

    public function status(): string
    {
        $percent = $this->percentUsed();

        return match (true) {
            bccomp($percent, '100', 2) >= 0 => 'over',
            bccomp($percent, '80', 2) >= 0 => 'warning',
            default => 'ok',
        };
    }
}
