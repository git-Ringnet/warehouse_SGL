<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assembly_id',
        'product_id',
        'quantity',
        'serials',
        'product_unit'
    ];

    /**
     * Get the assembly that owns this product.
     */
    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product_unit attribute as array.
     */
    public function getProductUnitAttribute($value)
    {
        if (is_string($value) && str_starts_with($value, '[')) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Set the product_unit attribute.
     */
    public function setProductUnitAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['product_unit'] = json_encode($value);
        } else {
            $this->attributes['product_unit'] = $value;
        }
    }
} 