<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use SomeoneFamous\FindBy\Traits\FindBy;
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

    protected static function newFactory(): WalletFactory
    {
        return WalletFactory::new();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionsFundsAvailable()
    {
        return $this->hasMany(Transaction::class)->fundsAvailable();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function owner(): MorphTo
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

    public function spendTo($recipient, $amount, ?string $description = null): bool
    {
        if (!in_array(HasWallets::class, class_uses_recursive($recipient))) {

            $this->errors[] = 'Invalid recipient type: Recipient type cannot have wallets.';

            return false;
        }

        $amount = abs($amount);

        DB::beginTransaction();

        try {
            if (!$receivingWallet = $recipient->wallets()->whereCurrencyId($this->currency_id)->first()) {

                $receivingWallet = new self;
                $receivingWallet->currency()->associate($this->currency);

                $recipient->wallets()->save($receivingWallet);
            }

            $success = $this->spendToWallet($receivingWallet, $amount, $description);

        } catch (\Exception $exception) {

            $this->errors[] = $exception->getMessage();

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

        DB::beginTransaction();

        try {

            $this->transactions()->create([
                'description'       => $description,
                'amount'            => -$amount,
                'status'            => $status,
                'counter_wallet_id' => $receivingWallet->id
            ]);

            $receivingWallet->transactions()->create([
                'description'       => $description,
                'amount'            => $amount,
                'status'            => $status,
                'counter_wallet_id' => $this->id
            ]);

            $success = $this->is_system_wallet || ($this->available_balance + $this->overdraft >= 0);

        } catch (\Exception $exception) {

            $this->errors[] = $exception->getMessage();
            
            $success = false;
        }

        $success ? DB::commit() : DB::rollback();

        return $success;
    }
}
