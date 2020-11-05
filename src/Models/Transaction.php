<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use SomeoneFamous\FindBy\FindBy;
use SomeoneFamous\Wallets\Collections\TransactionCollection;
use SomeoneFamous\Wallets\Database\Factories\TransactionFactory;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use FindBy;
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_CLEARED = 1;
    const DESCRIPTION_MAX_LENGTH = 255;

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    public function getDisplayAmountAttribute(): string
    {
        return $this->wallet->currency->symbol . number_format($this->amount, $this->wallet->currency->decimals);
    }

    public function getDisplayBalanceAttribute(): string
    {
        return $this->wallet->currency->symbol . number_format($this->balance, $this->wallet->currency->decimals);
    }

    public function newCollection(array $models = [])
    {
        return new TransactionCollection($models);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function counterWallet()
    {
        return $this->belongsTo(Wallet::class, 'counter_wallet_id');
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

    public static function resourceOptions(): array
    {
        return [
            'priority' => 'high'
        ];
    }
}
