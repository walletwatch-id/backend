<?php

namespace App\Models;

use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'paylater_id',
        'monthly_installment',
        'period',
        'first_installment_datetime',
        'transaction_datetime',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'first_installment_datetime' => 'datetime',
        'transaction_datetime' => 'datetime',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paylater(): BelongsTo
    {
        return $this->belongsTo(Paylater::class);
    }
}
