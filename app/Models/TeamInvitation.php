<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
protected $fillable = ['team_id', 'email', 'token', 'invited_by', 'expires_at', 'opened_at', 'accepted_at'];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TeamInvitation $invite): void {
            $invite->token ??= Str::random(40);
            $invite->expires_at ??= now()->addHour();
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && ! $this->isExpired();
    }

    public function markOpened(): void
    {
        if (is_null($this->opened_at)) {
            $this->update(['opened_at' => now()]);
        }
    }
}
