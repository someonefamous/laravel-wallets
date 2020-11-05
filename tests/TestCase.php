<?php

namespace SomeoneFamous\Wallets\Tests;

use SomeoneFamous\Wallets\WalletServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            WalletServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/../database/migrations/create_users_table.php.stub';
        include_once __DIR__ . '/../database/migrations/set_up_wallet_tables.php.stub';

        (new \CreateUsersTable())->up();
        (new \SetUpWalletTables())->up();
    }
}