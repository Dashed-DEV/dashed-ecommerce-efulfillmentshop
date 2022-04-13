<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Qubiqx\QcommerceEcommerceCore\Events\Orders\OrderMarkedAsPaidEvent;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Listeners\MarkOrderAsPushableListener;

class QcommerceEcommerceEfulfillmentshopEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderMarkedAsPaidEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
