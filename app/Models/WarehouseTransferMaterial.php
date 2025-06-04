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
        'notes',
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
        return $this->belongsTo(Material::class);
    }
}
