<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_id',
        'device_code',
        'device_name',
        'device_serial',
        'device_quantity',
        'device_status',
        'device_notes',
        'device_images',
        'device_parts',
        'rejected_reason',
        'rejected_warehouse_id',
        'rejected_at',
    ];

    protected $casts = [
        'device_images' => 'array',
        'device_parts' => 'array',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function rejectedWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'rejected_warehouse_id');
    }

    /**
     * Get device status label
     */
    public function getDeviceStatusLabelAttribute()
    {
        $labels = [
            'processing' => 'Đang xử lý',
            'selected' => 'Đã chọn',
            'rejected' => 'Đã từ chối',
        ];

        return $labels[$this->device_status] ?? 'Không xác định';
    }

    /**
     * Get device status color
     */
    public function getDeviceStatusColorAttribute()
    {
        $colors = [
            'processing' => 'yellow',
            'selected' => 'green',
            'rejected' => 'red',
        ];

        return $colors[$this->device_status] ?? 'gray';
    }
}
