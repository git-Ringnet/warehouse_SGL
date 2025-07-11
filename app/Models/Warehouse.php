<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

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
        'description',
        'status',
        'is_hidden',
        'deleted_at',
        'deleted_by',
        'delete_reason'
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
        return $this->belongsTo(Employee::class, 'manager');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
} 