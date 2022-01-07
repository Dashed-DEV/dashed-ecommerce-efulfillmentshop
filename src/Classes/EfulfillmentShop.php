<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceCore\Models\OrderLog;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Mail\TrackandTraceMail;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopProduct;

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
        if (!self::isConnected()) {
            return;
        }

        $efulfillmentshopProduct = EfulfillmentshopProduct::where('product_id', $product->id)->first();
        if (!$efulfillmentshopProduct) {
            $efulfillmentshopProduct = new EfulfillmentshopProduct();
            $efulfillmentshopProduct->save();
        }

        if ($efulfillmentshopProduct->pushed) {
            return;
        }

        $token = self::getLoginToken();

        try {
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
                $efulfillmentshopProduct->efulfillment_shop_id = $response['id'];
                $efulfillmentshopProduct->error = null;
                $efulfillmentshopProduct->save();
            } else {
                $allProducts = self::getProducts();
                foreach ($allProducts as $allProduct) {
                    if ($allProduct['barcode'] == $product->ean) {
                        $efulfillmentshopProduct->efulfillment_shop_id = $allProduct['id'];
                        $efulfillmentshopProduct->error = null;
                        $efulfillmentshopProduct->save();
                    }
                }

                if (!$efulfillmentshopProduct->efulfillment_shop_id) {
                    $efulfillmentshopProduct->efulfillment_shop_id = null;
                    $efulfillmentshopProduct->error = $response['detail'];
                    $efulfillmentshopProduct->save();
                }
            }
        } catch (\Exception $exception) {
            $efulfillmentshopProduct->efulfillment_shop_id = null;
            $efulfillmentshopProduct->error = $exception->getMessage();
            $efulfillmentshopProduct->save();
        }
    }

    public static function pushOrder(EfulfillmentshopOrder $efulfillmentOrder)
    {
        if (self::isConnected($efulfillmentOrder->order->site_id)) {
            $token = self::getLoginToken();

            $hasProductWithoutFulfillmentId = false;
            foreach ($efulfillmentOrder->order->orderProductsWithProduct as $orderProduct) {
                if (!$orderProduct->product->efulfillmentShopProduct || !$orderProduct->product->efulfillmentShopProduct->efulfillment_shop_id) {
                    $hasProductWithoutFulfillmentId = true;
                }
            }

            if ($hasProductWithoutFulfillmentId) {
                $efulfillmentOrder->error = 'Niet alle producten staan in Efulfillment shop';
                $efulfillmentOrder->save();

                return;
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/addresses', [
                    'name' => $efulfillmentOrder->order->name,
                    'company' => $efulfillmentOrder->order->company_name ?: '',
                    'street' => $efulfillmentOrder->order->street,
                    'houseNumber' => $efulfillmentOrder->order->house_nr,
                    'zip' => $efulfillmentOrder->order->zip_code,
                    'city' => $efulfillmentOrder->order->city,
                    'countryCode' => $efulfillmentOrder->order->countryIsoCode,
                    'email' => $efulfillmentOrder->order->email,
                    'phone' => $efulfillmentOrder->order->phone_number ?: '',
                ]);

            $response = json_decode($response->body(), true);

            $efulfillmentOrder->invoice_address_id = $response['id'];
            $efulfillmentOrder->shipping_address_id = $response['id'];

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/sales', [
                    'channelId' => (int)Customsetting::get('efulfillment_shop_channel_id'),
                    'channelReference' => $efulfillmentOrder->order->invoice_id,
                    'invoiceAddressId' => $efulfillmentOrder->invoice_address_id,
                    'shippingAddressId' => $efulfillmentOrder->shipping_address_id,
                ]);

            $response = json_decode($response->body(), true);
            $efulfillmentOrder->sale_id = $response['id'];

            foreach ($efulfillmentOrder->order->orderProductsWithProduct as $orderProduct) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/ld+json',
                ])->withToken($token)
                    ->post(self::getApiUrl() . '/sale_lines', [
                        'description' => $orderProduct->name,
                        'quantity' => (int)$orderProduct->quantity,
                        'productId' => (int)$orderProduct->product->efulfillmentShopProduct->efulfillment_shop_id,
                        'saleId' => (int)$efulfillmentOrder->sale_id,
                    ]);
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->post(self::getApiUrl() . '/sales/' . $efulfillmentOrder->sale_id . '/confirm');

            $efulfillmentOrder->pushed = 1;
            $efulfillmentOrder->save();

            $orderLog = new OrderLog();
            $orderLog->order_id = $efulfillmentOrder->order->id;
            $orderLog->user_id = Auth::user()->id ?? null;
            $orderLog->tag = 'order.pushed-to-efulfillmentshop';
            $orderLog->save();
        }
    }

    public static function updateSale(EfulfillmentshopOrder $efulfillmentOrder)
    {
        if (self::isConnected()) {
            $token = self::getLoginToken();
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/ld+json',
            ])->withToken($token)
                ->get(self::getApiUrl() . '/sales/' . $efulfillmentOrder->sale_id);
            $response = json_decode($response->body(), true);

            $efulfillmentOrder->fulfillment_status = $response['status'];

            if ($efulfillmentOrder->fulfillment_status == 'ship') {
                $efulfillmentOrder->order->changeFulfillmentStatus('handled');
            } elseif ($efulfillmentOrder->fulfillment_status == 'pick') {
                $efulfillmentOrder->order->changeFulfillmentStatus('in_treatment');
            } elseif ($efulfillmentOrder->fulfillment_status == 'pack') {
                $efulfillmentOrder->order->changeFulfillmentStatus('packed');
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

                    if ($efulfillmentOrder->track_and_trace != $trackAndTraces) {
                        $efulfillmentOrder->track_and_trace = $trackAndTraces;

                        $orderLog = new OrderLog();
                        $orderLog->order_id = $efulfillmentOrder->order->id;
                        $orderLog->user_id = null;

                        try {
                            Mail::to($efulfillmentOrder->order->email)->send(new TrackandTraceMail($efulfillmentOrder));
                            $orderLog->tag = 'order.t&t.send';
                        } catch (\Exception $e) {
                            $orderLog->tag = 'order.t&t.not-send';
                        }
                        $orderLog->save();
                    }
                }
            }

            $efulfillmentOrder->save();
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
