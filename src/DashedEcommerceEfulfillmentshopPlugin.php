<?php

namespace Dashed\DashedEcommerceEfulfillmentshop;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedEcommerceEfulfillmentshop\Filament\Widgets\EfulfillmentShopOrderStats;
use Dashed\DashedEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;

class DashedEcommerceEfulfillmentshopPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-ecommerce-efulfillmentshop';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->widgets([
                EfulfillmentShopOrderStats::class,
            ])
            ->pages([
                EfulfillmentshopSettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
