<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Listeners;

use Dashed\DashedEcommerceCore\Events\Orders\OrderMarkedAsPaidEvent;
use Dashed\DashedEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

class MarkOrderAsPushableListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(OrderMarkedAsPaidEvent $event)
    {
        if (EfulfillmentShop::isConnected($event->order->site_id)) {
            $eshopOrder = new EfulfillmentshopOrder();
            $eshopOrder->order_id = $event->order->id;
            $eshopOrder->save();
        }
    }
}
