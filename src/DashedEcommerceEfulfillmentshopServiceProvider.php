<?php

namespace Dashed\DashedEcommerceEfulfillmentshop;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Livewire;
use Dashed\DashedEcommerceCore\Models\Order;
use Dashed\DashedEcommerceCore\Models\Product;
use Dashed\DashedEcommerceEfulfillmentshop\Commands\PushOrdersToEfulfillmentShopCommand;
use Dashed\DashedEcommerceEfulfillmentshop\Commands\PushProductsToEfulfillmentShopCommand;
use Dashed\DashedEcommerceEfulfillmentshop\Commands\UpdateOrdersFromEfulfillmentShopCommand;
use Dashed\DashedEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;
use Dashed\DashedEcommerceEfulfillmentshop\Filament\Widgets\EfulfillmentShopOrderStats;
use Dashed\DashedEcommerceEfulfillmentshop\Livewire\Orders\ShowEfulfillmentShopOrder;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopProduct;
use Spatie\LaravelPackageTools\Package;

class DashedEcommerceEfulfillmentshopServiceProvider extends PluginServiceProvider
{
    public static string $name = 'dashed-ecommerce-efulfillmentshop';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command(PushProductsToEfulfillmentShopCommand::class)->everyFiveMinutes();
            $schedule->command(PushOrdersToEfulfillmentShopCommand::class)->everyFiveMinutes();
            $schedule->command(UpdateOrdersFromEfulfillmentShopCommand::class)->everyFiveMinutes();
        });

        Livewire::component('show-efulfillmentshop-order', ShowEfulfillmentShopOrder::class);

        Product::addDynamicRelation('efulfillmentShopProduct', function (Product $model) {
            return $model->hasOne(EfulfillmentshopProduct::class);
        });
        Order::addDynamicRelation('efulfillmentShopOrder', function (Order $model) {
            return $model->hasOne(EfulfillmentshopOrder::class);
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
                    'description' => 'Koppel e-fulfillment shop aan je bestellingen',
                    'icon' => 'archive',
                    'page' => EfulfillmentshopSettingsPage::class,
                ],
            ])
        );

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-efulfillmentshop-order' => [
                    'name' => 'show-efulfillmentshop-order',
                    'width' => 'sidebar',
                ],
            ])
        );

        $package
            ->name('dashed-ecommerce-efulfillmentshop')
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

    protected function getWidgets(): array
    {
        return array_merge(parent::getWidgets(), [
            EfulfillmentShopOrderStats::class,
        ]);
    }
}
