<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransferMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_transfer_id',
        'material_id',
        'quantity',
        'type',
        'serial_numbers',
        'notes',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'serial_numbers' => 'json',
    ];

    /**
     * Lấy phiếu chuyển kho
     */
    public function warehouseTransfer(): BelongsTo
    {
        return $this->belongsTo(WarehouseTransfer::class);
    }

    /**
     * Lấy vật tư
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Lấy thành phẩm
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'material_id');
    }

    /**
     * Lấy hàng hóa
     */
    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class, 'material_id');
    }

    /**
     * Lấy thông tin item dựa vào type
     */
    public function item()
    {
        return match($this->type) {
            'material' => $this->material,
            'product' => $this->product,
            'good' => $this->good,
            default => $this->material
        };
    }
}
