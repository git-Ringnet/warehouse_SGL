<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryImportMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_import_id',
        'material_id',
        'warehouse_id',
        'quantity',
        'serial',
        'serial_numbers',
        'notes',
        'item_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inventory_import_id' => 'integer',
        'material_id' => 'integer',
        'warehouse_id' => 'integer',
        'quantity' => 'integer',
        'serial_numbers' => 'json',
    ];

    /**
     * Get the inventory import that owns this inventory import material.
     */
    public function inventoryImport()
    {
        return $this->belongsTo(InventoryImport::class);
    }

    /**
     * Get the material for this inventory import material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the product for this inventory import material.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'material_id');
    }

    /**
     * Get the good for this inventory import material.
     */
    public function good()
    {
        return $this->belongsTo(Good::class, 'material_id');
    }
    
    /**
     * Get the warehouse for this inventory import material.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
} 