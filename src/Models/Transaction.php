<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function newCollection(array $models = [])
    {
        return new TransactionCollection($models);
    }

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function counterWallet()
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

    public function scopeCleared($query)
    {
        return $query->whereStatus(self::STATUS_CLEARED);
    }

    public function scopePending($query)
    {
        return $query->whereStatus(self::STATUS_PENDING);
    }

    public function scopeCredits($query)
    {
        return $query->where('amount', '>=', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeFundsAvailable($query)
    {
        return $query->cleared()->orWhere(function($query) {
            $query->pending()->debits();
        });
    }

    public function clear()
    {
        $this->status = self::STATUS_CLEARED;
        $this->save();
    }
}
