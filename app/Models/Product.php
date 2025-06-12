<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
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
        'description',
        'status',
        'is_hidden',
        'inventory_warehouses'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_hidden' => 'boolean',
        'inventory_warehouses' => 'array',
    ];

    /**
     * Get warehouse materials for this product.
     */
    public function warehouseMaterials(): HasMany
    {
        return $this->hasMany(WarehouseMaterial::class, 'material_id')
            ->where('item_type', 'product');
    }
    
    /**
     * Get the total inventory quantity based on configured warehouses.
     *
     * @return int
     */
    public function getInventoryQuantity(): int
    {
        $query = $this->warehouseMaterials();
        
        // Apply warehouse filter if configured
        if (is_array($this->inventory_warehouses) && !in_array('all', $this->inventory_warehouses) && !empty($this->inventory_warehouses)) {
            $query->whereIn('warehouse_id', $this->inventory_warehouses);
        }
        
        return $query->sum('quantity');
    }
    
    /**
     * Get inventory quantities by warehouse.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInventoryByWarehouse()
    {
        $results = $this->warehouseMaterials()
            ->select('warehouse_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('warehouse_id')
            ->get();
            
        // Manually load warehouse data for each result
        $warehouseIds = $results->pluck('warehouse_id')->unique();
        $warehouses = Warehouse::whereIn('id', $warehouseIds)->get()->keyBy('id');
        
        // Add warehouse data to results
        foreach ($results as $result) {
            $result->warehouse = $warehouses->get($result->warehouse_id);
        }
        
        return $results;
    }
    
    /**
     * Check if the product has any inventory.
     * 
     * @return bool
     */
    public function hasInventory(): bool
    {
        return $this->getInventoryQuantity() > 0;
    }
    
    /**
     * Get the materials associated with this product.
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
            ->withPivot('quantity', 'notes')
            ->withTimestamps();
    }
    
    /**
     * Get the images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
} 