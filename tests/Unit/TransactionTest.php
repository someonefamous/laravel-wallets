<?php

namespace SomeoneFamous\Wallets\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SomeoneFamous\Wallets\Tests\TestCase;
use SomeoneFamous\Wallets\Models\Transaction;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_transaction_has_a_wallet()
    {
        $transaction = Transaction::factory()->create(['wallet_id' => 1]);
        $this->assertEquals(1, $transaction->wallet_id);
    }

    /** @test */
    function a_transaction_has_a_counter_wallet()
    {
        $transaction = Transaction::factory()->create(['counter_wallet_id' => 1]);
        $this->assertEquals(1, $transaction->counter_wallet_id);
    }

    /** @test */
    function a_transaction_has_a_description()
    {
        $transaction = Transaction::factory()->create(['description' => 'a test transaction']);
        $this->assertEquals('a test transaction', $transaction->description);
    }

    /** @test */
    function a_transaction_has_an_amount()
    {
        $transaction = Transaction::factory()->create(['amount' => 123.456]);
        $this->assertEquals(123.456, $transaction->amount);
    }
}
