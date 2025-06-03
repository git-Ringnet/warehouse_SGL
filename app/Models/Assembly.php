<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assembly extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'date',
        'product_id',
        'warehouse_id',
        'assigned_to',
        'status',
        'notes'
    ];

    /**
     * Get the product for this assembly.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this assembly.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the assembly materials for this assembly.
     */
    public function materials()
    {
        return $this->hasMany(AssemblyMaterial::class);
    }
} 