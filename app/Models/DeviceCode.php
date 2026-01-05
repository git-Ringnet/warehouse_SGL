<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCode extends Model
{
    protected $fillable = [
        'dispatch_id',
        'product_id',
        'item_type',
        'item_id',
        'old_serial',
        'serial_main',
        'serial_components',
        'serial_components_map', // Keyed object for robust reading
        'serial_sim',
        'access_code',
        'iot_id',
        'mac_4g',
        'note',
        'type'
    ];

    protected $casts = [
        'serial_components' => 'array',
        'serial_components_map' => 'array'
    ];

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }
}