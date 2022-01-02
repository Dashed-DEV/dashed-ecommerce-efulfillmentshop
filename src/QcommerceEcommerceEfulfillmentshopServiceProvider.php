<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopProduct;
use Qubiqx\QcommerceEcommerceFulfillmentshop\Commands\PushOrdersToEfulfillmentShopCommand;
use Qubiqx\QcommerceEcommerceFulfillmentshop\Commands\PushProductsToEfulfillmentShopCommand;
use Qubiqx\QcommerceEcommerceFulfillmentshop\Commands\UpdateOrdersFromEfulfillmentShopCommand;
use Spatie\LaravelPackageTools\Package;

class QcommerceEcommerceEfulfillmentshopServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-efulfillmentshop';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command(PushProductsToEfulfillmentShopCommand::class)->everyFiveMinutes();
            $schedule->command(PushOrdersToEfulfillmentShopCommand::class)->everyFiveMinutes();
            $schedule->command(UpdateOrdersFromEfulfillmentShopCommand::class)->everyFiveMinutes();
        });

        Product::addDynamicRelation('efulfillmentShopProduct', function (Product $model) {
            return $model->hasOne(EfulfillmentshopProduct::class);
        });
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'efulfillmentshop' => [
                    'name' => 'E-fulfillment shop',
                    'description' => 'Koppel e-fulfillment shop aan je bestelling',
                    'icon' => 'archive',
                    'page' => EfulfillmentshopSettingsPage::class,
                ],
            ])
        );

        $package
            ->name('qcommerce-ecommerce-efulfillmentshop')
            ->hasViews()
            ->hasCommands([
                PushOrdersToEfulfillmentShopCommand::class,
                UpdateOrdersFromEfulfillmentShopCommand::class,
                PushProductsToEfulfillmentShopCommand::class,
            ]);
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            EfulfillmentshopSettingsPage::class,
        ]);
    }
}
