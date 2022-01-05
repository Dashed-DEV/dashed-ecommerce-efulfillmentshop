<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Livewire;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands\PushOrdersToEfulfillmentShopCommand;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands\PushProductsToEfulfillmentShopCommand;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands\UpdateOrdersFromEfulfillmentShopCommand;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Filament\Widgets\EfulfillmentShopOrderStats;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Livewire\Orders\ShowEfulfillmentShopOrder;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopProduct;
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
                    'width' => 'sidebar'
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

    protected function getWidgets(): array
    {
        return array_merge(parent::getWidgets(), [
            EfulfillmentShopOrderStats::class,
        ]);
    }
}
