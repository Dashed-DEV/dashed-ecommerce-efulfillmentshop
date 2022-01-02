<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceCore\Models\Translation;
use Qubiqx\QcommerceEcommerceCore\Models\Order;

class TrackandTraceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->view('qcommerce-ecommerce-efulfillmentshop::emails.track-and-trace')->from(Customsetting::get('site_from_email'), Customsetting::get('company_name'))->subject(Translation::get('order-efulfillmentshop-track-and-trace-email-subject', 'efulfillmentshop', 'Your order #:orderId: has been updated', 'text', [
            'orderId' => $this->order->invoice_id,
        ]))->with([
            'order' => $this->order,
        ]);
    }
}
