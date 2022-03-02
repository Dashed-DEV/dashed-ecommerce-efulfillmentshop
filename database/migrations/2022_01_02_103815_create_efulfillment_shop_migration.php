<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEfulfillmentShopMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qcommerce__order_efulfillmentshop', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('qcommerce__orders');
            $table->string('invoice_address_id')->nullable();
            $table->string('shipping_address_id')->nullable();
            $table->string('sale_id')->nullable();
            $table->json('track_and_trace')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('error')->nullable();
            $table->boolean('pushed')->default(0);

            $table->timestamps();
        });

        Schema::create('qcommerce__product_efulfillmentshop', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('qcommerce__products');
            $table->string('efulfillment_shop_id')->nullable();
            $table->string('error')->nullable();

            $table->timestamps();
        });

        $orders = \Qubiqx\QcommerceEcommerceCore\Models\Order::where('pushable_to_efulfillment_shop', 1)->get();
        $products = \Qubiqx\QcommerceEcommerceCore\Models\Product::whereNotNull('efulfillment_shop_id')->get();

        foreach ($orders as $order) {
            $eshopOrder = new \Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder();
            $eshopOrder->order_id = $order->id;
            $eshopOrder->invoice_address_id = $order->efulfillment_shop_invoice_address_id;
            $eshopOrder->shipping_address_id = $order->efulfillment_shop_shipping_address_id;
            $eshopOrder->sale_id = $order->efulfillment_shop_sale_id;
            $eshopOrder->track_and_trace = $order->efulfillment_shop_track_and_trace;
            $eshopOrder->fulfillment_status = $order->efulfillment_shop_fulfillment_status;
            $eshopOrder->error = $order->efulfillment_shop_error;
            $eshopOrder->pushed = $order->pushed_to_efulfillment_shop;
            $eshopOrder->save();
        }

        foreach ($products as $product) {
            $eshopProduct = new \Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopProduct();
            $eshopProduct->product_id = $product->id;
            $eshopProduct->efulfillment_shop_id = $product->efulfillment_shop_id;
            $eshopProduct->error = $product->efulfillment_shop_error;
            $eshopProduct->save();
        }

        Schema::table('qcommerce__orders', function (Blueprint $table) {
            $table->dropColumn('pushable_to_efulfillment_shop');
            $table->dropColumn('pushed_to_efulfillment_shop');
            $table->dropColumn('efulfillment_shop_error');
            $table->dropColumn('efulfillment_shop_invoice_address_id');
            $table->dropColumn('efulfillment_shop_shipping_address_id');
            $table->dropColumn('efulfillment_shop_sale_id');
            $table->dropColumn('efulfillment_shop_track_and_trace');
            $table->dropColumn('efulfillment_shop_fulfillment_status');
        });

        Schema::table('qcommerce__products', function (Blueprint $table) {
            $table->dropColumn('efulfillment_shop_id');
            $table->dropColumn('efulfillment_shop_error');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('efulfillment_shop_migration');
    }
}
