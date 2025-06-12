<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Good extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'supplier_id',
        'serial',
        'notes',
        'image_path',
        'inventory_warehouses',
        'status',
        'is_hidden',
        'supplier_ids'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'supplier_id' => 'integer',
        'inventory_warehouses' => 'array',
        'is_hidden' => 'boolean',
        'supplier_ids' => 'array',
    ];
    
    /**
     * Get the images for this good.
     */
    public function images()
    {
        return $this->hasMany(GoodImage::class);
    }

    /**
     * Get the single supplier for this good (legacy relationship).
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
    /**
     * Get all suppliers for this good based on supplier_ids.
     * This defines a many-to-many relationship using a custom pivot.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'good_supplier')
            ->withPivot('id')
            ->withTimestamps();
    }
    
    /**
     * Get supplier_ids from the suppliers relationship.
     * This ensures backward compatibility with code that uses supplier_ids.
     *
     * @return array
     */
    public function getSupplierIdsAttribute()
    {
        if (array_key_exists('supplier_ids', $this->attributes) && !empty($this->attributes['supplier_ids'])) {
            return $this->attributes['supplier_ids'];
        }
        
        return $this->suppliers()->pluck('suppliers.id')->toArray();
    }

    /**
     * Get warehouse materials for this good.
     */
    public function warehouseMaterials(): HasMany
    {
        return $this->hasMany(WarehouseMaterial::class, 'material_id')
            ->where('item_type', 'good');
    }
    
    /**
     * Get the total inventory quantity across all warehouses.
     *
     * @return int
     */
    public function getInventoryQuantity(): int
    {
        return $this->warehouseMaterials()
            ->sum('quantity');
    }
    
    /**
     * Get inventory quantities by warehouse.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInventoryByWarehouse()
    {
        return $this->warehouseMaterials()
            ->select('warehouse_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('warehouse_id')
            ->with('warehouse')
            ->get();
    }
    
    /**
     * Check if the good has any inventory.
     * 
     * @return bool
     */
    public function hasInventory(): bool
    {
        return $this->getInventoryQuantity() > 0;
    }
} 