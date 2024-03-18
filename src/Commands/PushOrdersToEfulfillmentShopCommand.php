<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Commands;

use Illuminate\Console\Command;
use Dashed\DashedEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Dashed\DashedEcommerceEfulfillmentshop\Models\EfulfillmentshopOrder;

class PushOrdersToEfulfillmentShopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efulfillmentshop:push-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push orders to efulfillment shop';

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
            foreach (EfulfillmentshopOrder::where('pushed', '!=', 1)->get() as $efulfillmentOrder) {
                EfulfillmentShop::pushOrder($efulfillmentOrder);
            }
        }
    }
}
