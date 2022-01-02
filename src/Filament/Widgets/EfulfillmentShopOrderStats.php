<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

class EfulfillmentShopOrderStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Aantal bestellingen naar E-fulfillment shop', EfulfillmentshopOrder::where('pushed', 1)->count()),
            Card::make('Aantal bestellingen in de wacht', EfulfillmentshopOrder::where('pushed', 0)->count()),
            Card::make('Aantal bestellingen gefaald', EfulfillmentshopOrder::where('pushed', 2)->count()),
        ];
    }
}
