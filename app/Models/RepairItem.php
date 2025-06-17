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
        'device_status',
        'device_notes',
        'device_images',
        'device_parts',
        'reject_reason',
        'reject_warehouse_id',
    ];

    protected $casts = [
        'device_images' => 'array',
        'device_parts' => 'array',
    ];

    /**
     * Relationships
     */
    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    /**
     * Get device status label
     */
    public function getDeviceStatusLabelAttribute()
    {
        $labels = [
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
            'selected' => 'green',
            'rejected' => 'red',
        ];

        return $colors[$this->device_status] ?? 'gray';
    }
}
