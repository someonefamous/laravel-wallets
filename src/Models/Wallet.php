<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SomeoneFamous\FindBy\FindBy;
use SomeoneFamous\Wallets\Database\Factories\WalletFactory;
use SomeoneFamous\Wallets\Traits\HasWallets;

class Wallet extends Model
{
    use FindBy;
    use HasFactory;

    const NAME_MAX_LENGTH = 255;

    protected $fillable = [
        'currency_id',
        'overdraft',
        'name',
        'owner_id',
        'owner_type',
    ];

    protected $appends = [
        'display_balance'
    ];

    private $errors = [];

    protected static function newFactory()
    {
        return WalletFactory::new();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionsFundsAvailable()
    {
        return $this->hasMany(Transaction::class)->fundsAvailable();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->currency->name;
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->transactionsFundsAvailable()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->transactions()->sum('amount');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, $this->currency->decimals);
    }

    public function getDisplayBalanceAttribute(): string
    {
        return ($this->balance < 0)
            ? '-' . $this->currency->symbol . substr($this->formatted_balance, 1)
            : $this->currency->symbol . $this->formatted_balance;
    }

    public function getFormattedOverdraftAttribute(): string
    {
        return number_format($this->overdraft, $this->currency->decimals);
    }

    public function getIsSystemWalletAttribute(): bool
    {
        return $this->owner_id === null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLastError(): string
    {
        return end($this->errors);
    }

    public function spendTo($recipient, $amount, $description = null): bool
    {
        if (!in_array(HasWallets::class, class_uses_recursive($recipient))) {

            $this->errors[] = 'Invalid recipient type: Recipient type cannot have wallets.';

            return false;
        }

        $amount = abs($amount);

        DB::beginTransaction();

        try {
            if (!$receivingWallet = $recipient->wallets()->whereCurrencyId($this->currency_id)->first()) {

                $receivingWallet = new Wallet();
                $receivingWallet->currency()->associate($this->currency);
                $receivingWallet->owner_id = $recipient->id;
                $receivingWallet->owner_type = get_class($recipient);
                $receivingWallet->save();
            }

            $success = $this->spendToWallet($receivingWallet, $amount, $description);

        } catch (\Exception $exception) {
            $success = false;
        }

        $success ? DB::commit() : DB::rollback();

        return $success;
    }

    private static function roundDown($decimal, int $precision)
    {
        $sign = $decimal > 0 ? 1 : -1;
        $base = pow(10, $precision);

        return floor(abs($decimal) * $base) / $base * $sign;
    }

    public function spendToWallet(
        self $receivingWallet,
        $amount,
        ?string $description,
        $status = Transaction::STATUS_CLEARED
    ): bool
    {
        if (!$receivingWallet->currency->is($this->currency)) {

            $this->errors[] = 'Sending wallet and receiving wallet must have matching currency.';

            return false;
        }

        if ($this->is($receivingWallet)) {

            $this->errors[] = 'Wallet cannot send to itself.';

            return false;
        }

        $amount = self::roundDown(abs($amount), $receivingWallet->currency->decimals);

        if ($amount == 0) {

            $this->errors[] = 'Sending amount must be at least ' . $this->currency->minimum_amount . '.';

            return false;
        }

        if (!$this->is_system_wallet && ($this->available_balance + $this->overdraft < $amount)) {

            $this->errors[] = 'Not enough funds available in sending wallet.';

            return false;
        }

        $debit              = new Transaction;
        $debit->description = $description;
        $debit->amount      = -$amount;
        $debit->status      = $status;
        $debit->counterWallet()->associate($receivingWallet);

        $credit              = new Transaction;
        $credit->description = $description;
        $credit->amount      = $amount;
        $credit->status      = $status;
        $credit->counterWallet()->associate($this);

        DB::beginTransaction();

        try {

            $this->transactions()->save($debit);
            $receivingWallet->transactions()->save($credit);

            $success = $this->is_system_wallet || ($this->available_balance + $this->overdraft >= 0);

        } catch (\Exception $exception) {
            $success = false;
        }

        $success ? DB::commit() : DB::rollback();

        return $success;
    }
}
