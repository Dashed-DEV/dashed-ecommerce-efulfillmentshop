<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Livewire\Orders;

use Livewire\Component;

class ShowEfulfillmentShopOrder extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('dashed-ecommerce-efulfillmentshop::orders.components.show-efulfillmentshop-order');
    }

    public function submit()
    {
        if (! $this->order->efulfillmentShopOrder) {
            $this->emit('notify', [
                'status' => 'error',
                'message' => 'De bestelling mag niet naar E-fulfillment shop gepushed worden.',
            ]);
        } elseif ($this->order->efulfillmentShopOrder->pushed == 1) {
            $this->emit('notify', [
                'status' => 'error',
                'message' => 'De bestelling is al naar E-fulfillment shop gepushed.',
            ]);
        } elseif ($this->order->efulfillmentShopOrder->pushed == 0) {
            $this->emit('notify', [
                'status' => 'error',
                'message' => 'De bestelling wordt al naar E-fulfillment shop gepushed.',
            ]);
        }

        $this->order->efulfillmentShopOrder->pushed = 0;
        $this->order->efulfillmentShopOrder->save();

        $this->emit('refreshPage');
        $this->emit('notify', [
            'status' => 'success',
            'message' => 'De bestelling wordt binnen enkele minuten opnieuw naar E-fulfillment shop gepushed.',
        ]);
    }
}
