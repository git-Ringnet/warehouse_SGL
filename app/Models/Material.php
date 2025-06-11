<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function images()
    {
        return $this->hasMany(MaterialImage::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
    /**
     * Get suppliers from supplier_ids field
     */
    public function suppliers()
    {
        if (is_array($this->supplier_ids) && !empty($this->supplier_ids)) {
            return Supplier::whereIn('id', $this->supplier_ids)->get();
        }
        return collect([]);
    }   
} 