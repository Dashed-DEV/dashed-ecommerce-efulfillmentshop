<?php

namespace Qubiqx\QcommerceEcommerceFulfillmentshop\Commands;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceEfulfillmentshop\Classes\EfulfillmentShop;

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
            foreach (Product::thisSite()->get() as $product) {
                EfulfillmentShop::pushProduct($product);
            }
        }
    }
}
