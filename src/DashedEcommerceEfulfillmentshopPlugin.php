<?php

namespace Dashed\DashedEcommerceEfulfillmentshop;

use Dashed\DashedEcommerceEfulfillmentshop\Filament\Pages\Settings\EfulfillmentshopSettingsPage;
use Dashed\DashedEcommerceEfulfillmentshop\Filament\Widgets\EfulfillmentShopOrderStats;
use Filament\Contracts\Plugin;
use Filament\Panel;

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
