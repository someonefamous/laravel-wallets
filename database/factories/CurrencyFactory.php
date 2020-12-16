<?php

namespace SomeoneFamous\Wallets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SomeoneFamous\Wallets\Models\Currency;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'symbol' => '$',//$this->faker->currencySymbol,
            'code' => $this->faker->currencyCode,
            'decimals' => $this->faker->numberBetween(0, 8),
        ];
    }
}
