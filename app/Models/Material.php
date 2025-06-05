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
        'serial',
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
        'inventory_warehouses' => 'array',
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
} 