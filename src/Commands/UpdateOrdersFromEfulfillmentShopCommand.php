<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Commands;

use Illuminate\Console\Command;
use Dashed\DashedEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

class UpdateOrdersFromEfulfillmentShopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efulfillmentshop:update-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update orders from efulfillment shop';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (EfulfillmentShop::isConnected()) {
            dump(EfulfillmentshopOrder::with(['order'])->where('pushed', 1)->where('fulfillment_status', '!=', 'ship')->orWhereNull('fulfillment_status')->count());
            foreach (EfulfillmentshopOrder::with(['order'])->where('pushed', 1)->where('fulfillment_status', '!=', 'ship')->orWhereNull('fulfillment_status')->get() as $efulfillmentOrder) {
                EfulfillmentShop::updateSale($efulfillmentOrder);
                dump($efulfillmentOrder->id);
            }
        }
    }
}
