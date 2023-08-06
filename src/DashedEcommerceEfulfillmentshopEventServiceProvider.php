<?php

namespace Dashed\DashedEcommerceEfulfillmentshop;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Dashed\DashedEcommerceCore\Events\Orders\OrderMarkedAsPaidEvent;
use Dashed\DashedEcommerceEfulfillmentshop\Listeners\MarkOrderAsPushableListener;

class DashedEcommerceEfulfillmentshopEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderMarkedAsPaidEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
