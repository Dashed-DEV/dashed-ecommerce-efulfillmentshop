<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Tests;

use Dashed\DashedEcommerceEfulfillmentshop\DashedEcommerceEfulfillmentshopServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Dashed\\DashedEcommerceEfulfillmentshop\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            DashedEcommerceEfulfillmentshopServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_dashed-ecommerce-efulfillmentshop_table.php.stub';
        $migration->up();
        */
    }
}
