<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Qubiqx\QcommerceEcommerceCore\Models\OrderLog;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Mail\TrackandTraceMail;

class EfulfillmentShop
{
    public static function getApiUrl()
    {
        $sandbox = Customsetting::get('efulfillment_shop_sandbox');
        if ($sandbox) {
            return 'https://api-sandbox.e-fulfilmentshop.nl/v1';
        } else {
            return 'https://api.e-fulfilmentshop.nl/v1';
        }
    }

    public static function getLoginToken($siteId = null, $refresh = false)
    {
        if (!$siteId) {
            $siteId = Sites::getActive();
        }

        $email = Customsetting::get('efulfillment_shop_username', $siteId);
        $password = Customsetting::get('efulfillment_shop_password', $siteId);

        if ($refresh) {
            Customsetting::set('efulfillment_shop_token', '', $siteId);
            Customsetting::set('efulfillment_shop_token_refresh_before', '', $siteId);
        } else {
            $refreshBefore = Customsetting::get('efulfillment_shop_token_refresh_before', $siteId);
            if ($refreshBefore && Carbon::parse($refreshBefore) < now()) {
                Customsetting::set('efulfillment_shop_token', '', $siteId);
                Customsetting::set('efulfillment_shop_token_refresh_before', '', $siteId);
            }
        }

        $token = Customsetting::get('efulfillment_shop_token', $siteId);

        if (!$token && $email && $password) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->post(self::getApiUrl() . '/authentication_token', [
                'email' => $email,
                'password' => $password,
            ]);

            $response = json_decode($response->body(), true);

            if (isset($response['token']) && $response['token']) {
                Customsetting::set('efulfillment_shop_token', $response['token'], $siteId);
                Customsetting::set('efulfillment_shop_token_refresh_before', now()->addMinutes(50), $siteId);
            }
        }

        $token = Customsetting::get('efulfillment_shop_token', $siteId);
        return $token;
    }

    public static function isConnected($siteId = null)
    {
        if (!$siteId) {
            $siteId = Sites::getActive();
        }

        try {
            $loginToken = self::getLoginToken($siteId, true);
        } catch (\Exception $e) {
            $loginToken = null;
        }

        if ($loginToken) {
            return true;
        } else {
            return false;
        }
    }

    public static function pushProduct(Product $product)
    {
        if (self::isConnected()) {
            $token = self::getLoginToken();

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/products', [
                    'name' => $product->name,
                    'barcode' => $product->ean,
                    'channelId' => (int)Customsetting::get('efulfillment_shop_channel_id'),
                    'channelReference' => $product->name,
                ]);

            $response = json_decode($response->body(), true);
            if (isset($response['id'])) {
                $product->efulfillment_shop_id = $response['id'];
                $product->efulfillment_shop_error = null;
                $product->save();
            } else {
                $allProducts = self::getProducts();
                foreach ($allProducts as $allProduct) {
                    if ($allProduct['barcode'] == $product->ean) {
                        $product->efulfillment_shop_id = $allProduct['id'];
                        $product->efulfillment_shop_error = null;
                        $product->save();
                    }
                }

                if (!$product->efulfillment_shop_id) {
                    $product->efulfillment_shop_id = null;
                    $product->efulfillment_shop_error = $response['detail'];
                    $product->save();
                }
            }
        }
    }

    public static function pushOrder(Order $order)
    {
        if (self::isConnected($order->site_id)) {
            $token = self::getLoginToken();

            $hasProductWithoutFulfillmentId = false;
            foreach ($order->orderProductsWithProduct as $orderProduct) {
                if (!$orderProduct->product->efulfillment_shop_id) {
                    $hasProductWithoutFulfillmentId = true;
                }
            }

            if ($hasProductWithoutFulfillmentId) {
                $order->efulfillment_shop_error = 'Niet alle producten staan in Efulfillment shop';
                $order->save();
                return;
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/addresses', [
                    'name' => $order->name,
                    'company' => $order->company_name ?: '',
                    'street' => $order->street,
                    'houseNumber' => $order->house_nr,
                    'zip' => $order->zip_code,
                    'city' => $order->city,
                    'countryCode' => $order->countryIsoCode,
                    'email' => $order->email,
                    'phone' => $order->phone_number ?: '',
                ]);

            $response = json_decode($response->body(), true);

            $order->efulfillment_shop_invoice_address_id = $response['id'];
            $order->efulfillment_shop_shipping_address_id = $response['id'];

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/sales', [
                    'channelId' => (int)Customsetting::get('efulfillment_shop_channel_id'),
                    'channelReference' => $order->invoice_id,
                    'invoiceAddressId' => $order->efulfillment_shop_invoice_address_id,
                    'shippingAddressId' => $order->efulfillment_shop_shipping_address_id,
                ]);

            $response = json_decode($response->body(), true);
            $order->efulfillment_shop_sale_id = $response['id'];

            foreach ($order->orderProductsWithProduct as $orderProduct) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/ld+json',
                ])->withToken($token)
                    ->post(self::getApiUrl() . '/sale_lines', [
                        'description' => $orderProduct->product->name,
                        'quantity' => (int)$orderProduct->quantity,
                        'productId' => (int)$orderProduct->product->efulfillment_shop_id,
                        'saleId' => (int)$order->efulfillment_shop_sale_id,
                    ]);
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/sales/' . $order->efulfillment_shop_sale_id . '/confirm');

            $order->pushed_to_efulfillment_shop = 1;
            $order->save();

            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->user_id = Auth::user()->id ?? null;
            $orderLog->tag = 'order.pushed-to-efulfillmentshop';
            $orderLog->save();
        }
    }

    public static function updateSale(Order $order)
    {
        if (self::isConnected()) {
            $token = self::getLoginToken();
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->get(self::getApiUrl() . '/sales/' . $order->efulfillment_shop_sale_id);
            $response = json_decode($response->body(), true);

            $order->efulfillment_shop_fulfillment_status = $response['status'];

            if ($order->efulfillment_shop_fulfillment_status == 'ship') {
                $order->changeFulfillmentStatus('handled');
            } elseif ($order->efulfillment_shop_fulfillment_status == 'pick') {
                $order->changeFulfillmentStatus('in_treatment');
            } elseif ($order->efulfillment_shop_fulfillment_status == 'pack') {
                $order->changeFulfillmentStatus('packed');
            }

            if ($response['shipmentIds']) {
                $trackAndTraces = [];
                foreach ($response['shipmentIds'] as $shipmentId) {
                    $shipmentResponse = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/ld+json',
                    ])->withToken($token)
                        ->get(self::getApiUrl() . '/shipments/' . $shipmentId);
                    $shipmentResponse = json_decode($shipmentResponse->body(), true);

                    $carrierResponse = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/ld+json',
                    ])->withToken($token)
                        ->get(self::getApiUrl() . '/carriers/' . $shipmentResponse['carrierId']);
                    $carrierResponse = json_decode($carrierResponse->body(), true);

                    foreach ($shipmentResponse['trackingCodes'] as $trackingCode) {
                        $trackAndTraces[$trackingCode] = $carrierResponse['name'];
                    }

                    $trackAndTraces = json_encode($trackAndTraces);
                    if ($order->efulfillment_shop_track_and_trace != $trackAndTraces) {
                        $order->efulfillment_shop_track_and_trace = $trackAndTraces;
                        try {
                            Mail::to($order->email)->send(new TrackandTraceMail($order));

                            $orderLog = new OrderLog();
                            $orderLog->order_id = $order->id;
                            $orderLog->user_id = null;
                            $orderLog->tag = 'order.t&t.send';
                            $orderLog->save();
                        } catch (\Exception $e) {
                            $orderLog = new OrderLog();
                            $orderLog->order_id = $order->id;
                            $orderLog->user_id = null;
                            $orderLog->tag = 'order.t&t.not-send';
                            $orderLog->save();
                        }
                    }
                }
            }

            $order->save();
        }
    }

    public static function deleteProduct($productId)
    {
        if (self::isConnected()) {
            $token = self::getLoginToken();

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->delete(self::getApiUrl() . '/products/' . $productId);

            $response = json_decode($response->body(), true);
        }
    }

    public static function getProducts()
    {
        if (self::isConnected()) {
            $token = self::getLoginToken();

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->get(self::getApiUrl() . '/products');

            $response = json_decode($response->body(), true);
            return $response;
        }
    }
}
