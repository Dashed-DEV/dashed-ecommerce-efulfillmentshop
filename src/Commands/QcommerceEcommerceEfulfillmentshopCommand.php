<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Commands;

use Illuminate\Console\Command;

class QcommerceEcommerceEfulfillmentshopCommand extends Command
{
    public $signature = 'qcommerce-ecommerce-efulfillmentshop';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
