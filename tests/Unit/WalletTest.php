<?php

namespace SomeoneFamous\Wallets\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SomeoneFamous\Wallets\Models\Currency;
use SomeoneFamous\Wallets\Tests\TestCase;
use SomeoneFamous\Wallets\Models\Wallet;
use SomeoneFamous\Wallets\Tests\User;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_wallet_has_a_currency()
    {
        $wallet = Wallet::factory()->create(['currency_id' => 1]);
        $this->assertEquals(1, $wallet->currency_id);
    }

    /** @test */
    function a_wallet_has_an_owner_type()
    {
        $wallet = Wallet::factory()->create(['owner_type' => 'Fake\User']);
        $this->assertEquals('Fake\User', $wallet->owner_type);
    }

    /** @test */
    function a_wallet_belongs_to_an_owner()
    {
        $owner = User::factory()->create();

        $currency = Currency::factory()->create();

        $owner->wallets()->create([
            'name' => 'My first fake wallet',
            'currency_id'  => $currency->id,
        ]);

        $this->assertCount(1, Wallet::all());
        $this->assertCount(1, $owner->wallets);

        tap($owner->wallets()->first(), function ($wallet) use ($owner, $currency) {
            $this->assertEquals('My first fake wallet', $wallet->name);
            $this->assertEquals($currency->id, $wallet->currency_id);
            $this->assertTrue($wallet->owner->is($owner));
        });
    }
}
