<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop;

use Qubiqx\QcommerceEcommerceCore\Events\Orders\OrderMarkedAsPaidEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Listeners\MarkOrderAsPushableListener;

class QcommerceEcommerceEfulfillmentshopEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderMarkedAsPaidEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
