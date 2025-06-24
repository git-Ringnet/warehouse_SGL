<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReplacementHistory extends Model
{
    use HasFactory;

    protected $table = 'material_replacement_history';

    protected $fillable = [
        'repair_id',
        'device_code',
        'material_code',
        'material_name',
        'old_serials',
        'new_serials',
        'quantity',
        'source_warehouse_id',
        'target_warehouse_id',
        'notes',
        'replaced_by',
        'replaced_at',
    ];

    protected $casts = [
        'old_serials' => 'array',
        'new_serials' => 'array',
        'replaced_at' => 'datetime',
    ];

    /**
     * Relationship with Repair
     */
    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    /**
     * Relationship with source warehouse
     */
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    /**
     * Relationship with target warehouse
     */
    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    /**
     * Relationship with user who performed replacement
     */
    public function replacedBy()
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }

    /**
     * Get material model
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }
}
