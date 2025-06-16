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
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'date',
        'product_id',
        'warehouse_id',
        'target_warehouse_id',
        'assigned_to',
        'assigned_employee_id',
        'tester_id',
        'status',
        'notes',
        'quantity',
        'product_serials',
        'project_id',
        'purpose'
    ];

    /**
     * Get the product for this assembly.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source warehouse (where components are taken from).
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Get the target warehouse (where finished products are placed).
     */
    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    /**
     * Get the assembly materials for this assembly.
     */
    public function materials()
    {
        return $this->hasMany(AssemblyMaterial::class);
    }

    /**
     * Get the assigned employee for this assembly.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Get the tester employee for this assembly.
     */
    public function tester()
    {
        return $this->belongsTo(Employee::class, 'tester_id');
    }

    /**
     * Get the project for this assembly.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
} 