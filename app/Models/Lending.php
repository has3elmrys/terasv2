<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lending extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'lender_id',
        'bank_id',
        'currency_id',
        'direction',
        'amount',
        'date',
        'due_date',
        'status',
        'note',
        'image_path'
    ];

        protected $casts = [
        'amount' => 'decimal:4',
        'date' => 'date',
        'due_date' => 'date',
    ];


        public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lender()
    {
        return $this->belongsTo(Lender::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

public function netGhostBalance(): string
    {
        $ghostType = $this->direction === 'lend' ? 'receivable' : 'payable';

        $ghostBank = Bank::where('team_id', $this->team_id)
            ->where('currency_id', $this->currency_id)
            ->where('type', $ghostType)
            ->first();

        if (! $ghostBank) {
            return $this->amount;
        }

        $net = '0';

        foreach (Transaction::where('lending_id', $this->id)->where('bank_id', $ghostBank->id)->get() as $t) {
            $net = $t->type === 'transfer_in'
                ? bcadd($net, $t->amount, 4)
                : bcsub($net, $t->amount, 4);
        }

        return $net;
    }

    public function signedRemaining(): string
    {
        $net = $this->netGhostBalance();

        return $this->direction === 'lend' ? $net : bcmul($net, '-1', 4);
    }

    public function remaining(): string
    {
        $signed = $this->signedRemaining();

        return bccomp($signed, '0', 4) === -1 ? bcmul($signed, '-1', 4) : $signed;
    }

    protected static function booted(): void
    {

        static::creating(function (Lending $lending): void {
        $lending->user_id ??= auth()->id();
        $lending->currency_id = Bank::find($lending->bank_id)?->currency_id;
        });

        // static::creating(function (Lending $lending): void {
        //     $lending->user_id ??= auth()->id();
        // });

        static::created(function (Lending $lending): void {
            DB::transaction(function () use ($lending) {
                $currency = $lending->currency;
                $baseCurrency = $lending->team->baseCurrency;

                $rateToBase = bcdiv($currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);
                $baseAmount = bcmul($lending->amount, $rateToBase, 4);

                $ghostType = $lending->direction === 'lend' ? 'receivable' : 'payable';

                $ghostBank = Bank::where('team_id', $lending->team_id)
                    ->where('currency_id', $lending->currency_id)
                    ->where('type', $ghostType)
                    ->first();


                if (! $ghostBank) {
                    $ghostLabel = $ghostType === 'receivable' ? 'Owed to You' : 'You Owe';

                    $ghostBank = new Bank([
                        'team_id' => $lending->team_id,
                        'currency_id' => $lending->currency_id,
                        'name' => $ghostLabel.' ('.$currency->code.')',
                    ]);
                    $ghostBank->type = $ghostType;
                    $ghostBank->save();
                }

                $transferGroupId = (string) Str::uuid();

                $legs = $lending->direction === 'lend'
                    ? [
                        ['bank_id' => $lending->bank_id, 'type' => 'transfer_out'],
                        ['bank_id' => $ghostBank->id, 'type' => 'transfer_in'],
                    ]
                    : [
                        ['bank_id' => $ghostBank->id, 'type' => 'transfer_out'],
                        ['bank_id' => $lending->bank_id, 'type' => 'transfer_in'],
                    ];

                foreach ($legs as $leg) {
                    Transaction::create([
                        'team_id' => $lending->team_id,
                        'user_id' => $lending->user_id,
                        'bank_id' => $leg['bank_id'],
                        'currency_id' => $lending->currency_id,
                        'type' => $leg['type'],
                        'transfer_group_id' => $transferGroupId,
                        'amount' => $lending->amount,
                        'rate_to_base' => $rateToBase,
                        'base_amount' => $baseAmount,
                        'date' => $lending->date,
                        'lending_id' => $lending->id,
                    ]);
                }

            });
        });
                static::deleting(function (Lending $lending): void {
                    Transaction::$cascadingFromLending = true;

                    try {
                        DB::transaction(function () use ($lending) {
                            if ($lending->isForceDeleting()) {
                                $lending->transactions()->withTrashed()->get()->each->forceDelete();

                                return;
                            }

                            $lending->transactions()->get()->each->delete();
                        });
                    } finally {
                        Transaction::$cascadingFromLending = false;
                    }
                });

                static::restored(function (Lending $lending): void {
                    $lending->refreshStatus();
                });

                static::restoring(function (Lending $lending): void {
                    DB::transaction(function () use ($lending) {
                        $lending->transactions()->onlyTrashed()->get()->each->restore();
                    });
                });


    }

                public function refreshStatus(): void
            {
                if ($this->status === 'written_off') {
                    return;
                }

                $remaining = $this->signedRemaining();

                $this->status = match (true) {
                    bccomp($remaining, '0', 4) <= 0 => 'paid',
                    bccomp($remaining, (string) $this->amount, 4) >= 0 => 'active',
                    default => 'partially_paid',
                };

                $this->saveQuietly();
            }

}
