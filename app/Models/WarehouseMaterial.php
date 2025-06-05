<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'warehouse_id',
        'material_id',
        'item_type',
        'quantity',
        'location',
        'serial_number'
    ];

    /**
     * Get the warehouse that owns the material.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the material (when item_type is 'material').
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
    
    /**
     * Get the product (when item_type is 'product').
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'material_id');
    }
    
    /**
     * Get the item, either material or product based on item_type.
     */
    public function item()
    {
        return $this->item_type === 'product' 
            ? $this->belongsTo(Product::class, 'material_id')
            : $this->belongsTo(Material::class, 'material_id');
    }
} 