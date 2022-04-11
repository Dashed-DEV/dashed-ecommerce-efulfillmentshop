<?php

namespace Qubiqx\QcommerceEcommerceEfulfillmentshop\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Qubiqx\QcommerceEcommerceCore\Models\Order;

class EfulfillmentshopOrder extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__order_efulfillmentshop';

    protected $fillable = [
        'order_id',
        'invoice_address_id',
        'shipping_address_id',
        'sale_id',
        'track_and_trace',
        'fulfillment_status',
        'pushed',
        'error',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
      'track_and_trace' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
