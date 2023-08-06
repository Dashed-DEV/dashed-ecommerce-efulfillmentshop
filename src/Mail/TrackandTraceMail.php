<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Mail;

use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;
use Dashed\DashedTranslations\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        return $this->view('dashed-ecommerce-efulfillmentshop::emails.track-and-trace')->from(Customsetting::get('site_from_email'), Customsetting::get('company_name'))->subject(Translation::get('order-efulfillmentshop-track-and-trace-email-subject', 'efulfillmentshop', 'Your order #:orderId: has been updated', 'text', [
            'orderId' => $this->order->invoice_id,
        ]))->with([
            'order' => $this->order,
            'efulfillmentOrder' => $this->efulfillmentOrder,
        ]);
    }
}
