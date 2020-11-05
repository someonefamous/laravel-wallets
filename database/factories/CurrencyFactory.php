<?php

namespace SomeoneFamous\Wallets\Database\Factories;

use SomeoneFamous\Wallets\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

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
