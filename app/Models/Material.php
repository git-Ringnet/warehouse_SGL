<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
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
        'supplier_ids',
        'status',
        'is_hidden',
        'notes',
        'image_path',
        'inventory_warehouses'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'supplier_id' => 'integer',
        'supplier_ids' => 'array',
        'inventory_warehouses' => 'array',
        'is_hidden' => 'boolean',
    ];
    
    /**
     * Get the images for this material.
     */
    public function images(): HasMany
    {
        return $this->hasMany(MaterialImage::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    
    /**
     * Get suppliers relationship using material_supplier pivot table.
     * This is the proper Eloquent relationship for eager loading.
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'material_supplier')
            ->withPivot('id')
            ->withTimestamps();
    }
    
    /**
     * Get suppliers from supplier_ids field (legacy method)
     * Use this when you need to get suppliers based on the JSON field
     */
    public function getSuppliersFromIds()
    {
        if (is_array($this->supplier_ids) && !empty($this->supplier_ids)) {
            return Supplier::whereIn('id', $this->supplier_ids)->get();
        }
        return collect([]);
    }

    /**
     * Get warehouse materials relationship
     */
    public function warehouseMaterials(): HasMany
    {
        return $this->hasMany(WarehouseMaterial::class, 'material_id')
            ->where('item_type', 'material');
    }
} 