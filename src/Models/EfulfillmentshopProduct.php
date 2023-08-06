<?php

namespace Dashed\DashedEcommerceEfulfillmentshop\Models;

use Dashed\DashedEcommerceCore\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EfulfillmentshopProduct extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__product_efulfillmentshop';

    protected $fillable = [
        'product_id',
        'efulfillment_shop_id',
        'error',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
