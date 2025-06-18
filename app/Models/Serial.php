<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_number',
        'product_id',
        'type',
        'status',
        'warehouse_id',
        'notes'
    ];

    /**
     * Get the product that owns the serial.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the material that owns the serial.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'product_id');
    }

    /**
     * Get the good that owns the serial.
     */
    public function good()
    {
        return $this->belongsTo(Good::class, 'product_id');
    }

    /**
     * Get the warehouse that owns the serial.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the related item based on type.
     */
    public function getRelatedItemAttribute()
    {
        switch ($this->type) {
            case 'material':
                return $this->material;
            case 'product':
                return $this->product;
            case 'good':
                return $this->good;
            default:
                return null;
        }
    }
} 