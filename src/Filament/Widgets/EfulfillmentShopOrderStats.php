<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Filament\Widgets;

use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;
use Filament\Widgets\StatsOverviewWidget;

class EfulfillmentShopOrderStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            StatsOverviewWidget\Stat::make('Aantal bestellingen naar E-fulfillment shop', EfulfillmentshopOrder::where('pushed', 1)->count()),
            StatsOverviewWidget\Stat::make('Aantal bestellingen in de wacht', EfulfillmentshopOrder::where('pushed', 0)->count()),
            StatsOverviewWidget\Stat::make('Aantal bestellingen gefaald', EfulfillmentshopOrder::where('pushed', 2)->count()),
        ];
    }
}
