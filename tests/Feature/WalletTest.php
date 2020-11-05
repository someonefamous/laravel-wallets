<?php

namespace SomeoneFamous\Wallets\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SomeoneFamous\Wallets\Models\Currency;
use SomeoneFamous\Wallets\Tests\TestCase;
use SomeoneFamous\Wallets\Tests\User;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    // 'tests/Feature/CreatePostTest.php'
    /** @test */
    function authed_users_wallets_are_shown_via_the_index_route()
    {
        $user = User::factory()->create();

        $secondaryUser = User::factory()->create();

        $currency = Currency::factory()->create();

        for ($i = 1; $i < 4; $i++) {
            $user->wallets()->create([
                'name' => 'My wallet ' . $i,
                'currency_id'  => $currency->id,
            ]);

            $secondaryUser->wallets()->create([
                'name' => 'Other wallet ' . $i,
                'currency_id'  => $currency->id,
            ]);
        }

        $this->actingAs($user);

        $this->get(route('wallets.index'))
            ->assertSee('My wallet 1')
            ->assertSee('My wallet 2')
            ->assertSee('My wallet 3')
            ->assertDontSee('Other wallet 1')
            ->assertDontSee('Other wallet 2')
            ->assertDontSee('Other wallet 3');
    }
}
