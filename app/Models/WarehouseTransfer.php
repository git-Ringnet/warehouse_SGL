<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_code',
        'serial',
        'source_warehouse_id',
        'destination_warehouse_id',
        'material_id',
        'employee_id',
        'quantity',
        'transfer_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    /**
     * Lấy kho nguồn
     */
    public function source_warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    /**
     * Lấy kho đích
     */
    public function destination_warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    /**
     * Lấy nhân viên thực hiện
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Lấy vật tư chính
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Lấy danh sách vật tư chuyển
     */
    public function materials(): HasMany
    {
        return $this->hasMany(WarehouseTransferMaterial::class);
    }

    /**
     * Lấy trạng thái hiển thị
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xác nhận',
            'in_progress' => 'Đang chuyển',
            'completed' => 'Hoàn thành',
            'canceled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Lấy class CSS cho trạng thái
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'canceled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
