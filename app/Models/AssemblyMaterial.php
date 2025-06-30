<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assembly_id',
        'material_id',
        'target_product_id',
        'quantity',
        'serial',
        'serial_id',
        'note',
        'product_unit',
    ];

    /**
     * Get the assembly that owns this assembly material.
     */
    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Get the material for this assembly material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the target product for this assembly material.
     */
    public function targetProduct()
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    /**
     * Get the serial for this assembly material.
     */
    public function serial()
    {
        return $this->belongsTo(Serial::class, 'serial_id');
    }
} 