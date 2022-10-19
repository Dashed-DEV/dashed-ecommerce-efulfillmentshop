<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factories\Factory;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\QcommerceEcommerceEfulfillmentshopServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Qubiqx\\QcommerceEcommerceEfulfillmentshop\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            QcommerceEcommerceEfulfillmentshopServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_qcommerce-ecommerce-efulfillmentshop_table.php.stub';
        $migration->up();
        */
    }
}
