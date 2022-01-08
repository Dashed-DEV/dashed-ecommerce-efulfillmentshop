<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

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
            foreach (EfulfillmentshopOrder::with(['order'])->where('pushed', 1)->where('fulfillment_status', '!=', 'ship')->get() as $efulfillmentOrder) {
                dump($efulfillmentOrder->id);
                EfulfillmentShop::updateSale($efulfillmentOrder);
            }
        }
    }
}
