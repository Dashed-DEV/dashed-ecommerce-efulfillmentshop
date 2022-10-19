<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceTranslations\Models\Translation;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

class TrackandTraceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(EfulfillmentshopOrder $efulfillmentOrder)
    {
        $this->efulfillmentOrder = $efulfillmentOrder;
        $this->order = $efulfillmentOrder->order;
    }

    public function build()
    {
        return $this->view('qcommerce-ecommerce-efulfillmentshop::emails.track-and-trace')->from(Customsetting::get('site_from_email'), Customsetting::get('company_name'))->subject(Translation::get('order-efulfillmentshop-track-and-trace-email-subject', 'efulfillmentshop', 'Your order #:orderId: has been updated', 'text', [
            'orderId' => $this->order->invoice_id,
        ]))->with([
            'order' => $this->order,
            'efulfillmentOrder' => $this->efulfillmentOrder,
        ]);
    }
}
