<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use SomeoneFamous\FindBy\FindBy;
use SomeoneFamous\Wallets\Collections\TransactionCollection;
use SomeoneFamous\Wallets\Database\Factories\TransactionFactory;

class Transaction extends Model
{
    use FindBy;
    use HasFactory;

    const DESCRIPTION_MAX_LENGTH = 255;
    const STATUS_PENDING = 0;
    const STATUS_CLEARED = 1;

    public function newCollection(array $models = []): TransactionCollection
    {
        return new TransactionCollection($models);
    }

    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function counterWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counter_wallet_id');
    }

    public function getDisplayAmountAttribute(): string
    {
        return $this->wallet->currency->symbol . number_format($this->amount, $this->wallet->currency->decimals);
    }

    public function getDisplayBalanceAttribute(): string
    {
        return $this->wallet->currency->symbol . number_format($this->balance, $this->wallet->currency->decimals);
    }

    public function scopeCleared(Builder $query): Builder
    {
        return $query->whereStatus(static::STATUS_CLEARED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereStatus(static::STATUS_PENDING);
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('amount', '>=', 0);
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeFundsAvailable(Builder $query): Builder
    {
        return $query->cleared()->orWhere(function($query) {
            $query->pending()->debits();
        });
    }

    public function clear(): self
    {
        $this->status = static::STATUS_CLEARED;
        $this->save();

        return $this;
    }
}
