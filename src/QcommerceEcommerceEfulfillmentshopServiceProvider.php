<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop;

use Filament\PluginServiceProvider;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands\QcommerceEcommerceEfulfillmentshopCommand;

class QcommerceEcommerceEfulfillmentshopServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-efulfillmentshop';

    public function configurePackage(Package $package): void
    {
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
            ->name('qcommerce-ecommerce-efulfillmentshop');
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            EfulfillmentshopSettingsPage::class,
        ]);
    }
}
