<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
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
        'address',
        'manager',
        'phone',
        'email',
        'description',
        'status',
        'is_hidden',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_hidden' => 'boolean',
    ];
    
    /**
     * Get the warehouse materials for the warehouse.
     */
    public function warehouseMaterials(): HasMany
    {
        return $this->hasMany(WarehouseMaterial::class, 'warehouse_id');
    }

    /**
     * Get the employee that manages the warehouse.
     */
    public function managerEmployee()
    {
        return $this->belongsTo(Employee::class, 'manager', 'id');
    }
} 