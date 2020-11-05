<?php

namespace SomeoneFamous\Wallets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use SomeoneFamous\FindBy\FindBy;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use FindBy;
    use HasFactory;

    const MAX_DIGITS_LEFT_OF_DECIMAL = 12;
    const MAX_DECIMALS = 8;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'decimals',
    ];

    public $timestamps = false;

    protected static function newFactory()
    {
        return \SomeoneFamous\Wallets\Database\Factories\CurrencyFactory::new();
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function getSystemWalletAttribute()
    {
        return ($systemWallet = $this->wallets()->whereNull('user_id')->first())
            ? $systemWallet
            : Wallet::create([
                'currency_id' => $this->id
            ]);
    }

    public function displayAmount($amount): string
    {
        return $this->symbol . number_format($amount, $this->decimals);
    }

    public function getMinimumAmountAttribute(): string
    {
        return $this->decimals
            ? '0.' . str_pad('', $this->decimals - 1, '0') . '1'
            : '0';
    }

    public function getMaximumAmountAttribute(): string
    {
        $fraction_part = $this->decimals
            ? '.' . str_pad('', $this->decimals, '9')
            : '';

        return str_pad('', self::MAX_DIGITS_LEFT_OF_DECIMAL, '9') . $fraction_part;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . ($this->code ? $this->code . ' - ' : '') . $this->symbol . ')';
    }

    public static function getOptions(): array
    {
        $options = [];

        foreach (self::orderBy('name')->get() as $currency) {
            $options[] = [
                'id' => $currency->id,
                'name' => $currency->display_name,
                'symbol' => $currency->symbol,
                'decimals' => $currency->decimals
            ];
        }

        return $options;
    }
}
