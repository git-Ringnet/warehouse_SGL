<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'material_id',
        'quantity',
        'notes'
    ];

    /**
     * Get the product that owns this material.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
} 