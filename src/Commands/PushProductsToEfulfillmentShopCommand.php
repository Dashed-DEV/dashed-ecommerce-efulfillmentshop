<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Commands;

use Dashed\DashedEcommerceCore\Models\Product;
use Dashed\DashedEcommerceEfulfillmentshop\Classes\EfulfillmentShop;
use Illuminate\Console\Command;

class PushProductsToEfulfillmentShopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efulfillmentshop:push-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push products to efulfillment shop';

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
            foreach (Product::publicShowable()->isNotBundle()->get() as $product) {
                EfulfillmentShop::pushProduct($product);
            }
        }
    }
}
