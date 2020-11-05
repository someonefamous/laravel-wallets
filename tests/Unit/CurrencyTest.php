<?php

namespace SomeoneFamous\Wallets\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SomeoneFamous\Wallets\Tests\TestCase;
use SomeoneFamous\Wallets\Models\Currency;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_currency_has_a_name()
    {
        $currency = Currency::factory()->create(['name' => 'Some Name']);
        $this->assertEquals('Some Name', $currency->name);
    }

    /** @test */
    function a_currency_has_a_symbol()
    {
        $currency = Currency::factory()->create(['symbol' => '~']);
        $this->assertEquals('~', $currency->symbol);
    }

    /** @test */
    function a_currency_has_a_code()
    {
        $currency = Currency::factory()->create(['code' => 'XYZ']);
        $this->assertEquals('XYZ', $currency->code);
    }

    /** @test */
    function a_currency_has_a_decimals_setting()
    {
        $currency = Currency::factory()->create(['decimals' => 3]);
        $this->assertEquals(3, $currency->decimals);
    }
}
