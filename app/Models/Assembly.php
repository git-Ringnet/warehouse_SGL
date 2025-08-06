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
        'assigned_to',
        'assigned_employee_id',
        'tester_id',
        'status',
        'notes',
        'product_serials',
        'project_id',
        'purpose',
        'warehouse_id',
        'target_warehouse_id',
        'created_by'
    ];

    /**
     * Get the source warehouse for this assembly.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the target warehouse for this assembly.
     */
    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    /**
     * Get the product for this assembly (legacy - for backward compatibility).
     * For new assemblies, use products() relationship instead.
     */
    public function product()
    {
        // Return the first product from products relationship for backward compatibility
        return $this->hasOneThrough(Product::class, AssemblyProduct::class, 'assembly_id', 'id', 'id', 'product_id');
    }

    /**
     * Get all products for this assembly (many-to-many through assembly_products).
     */
    public function products()
    {
        return $this->hasMany(AssemblyProduct::class);
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

    /**
     * Get the testing records for this assembly.
     */
    public function testings()
    {
        return $this->hasMany(Testing::class);
    }

    /**
     * Get the creator (người tạo phiếu) for this assembly.
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Get the total quantity of all products in this assembly.
     * This is for backward compatibility since quantity column was removed.
     */
    public function getQuantityAttribute()
    {
        if ($this->products && $this->products->count() > 0) {
            return $this->products->sum('quantity');
        }
        return 1; // Default for legacy compatibility
    }

    /**
     * Get the first product ID for backward compatibility.
     * This is for backward compatibility since product_id column was removed.
     */
    public function getProductIdAttribute()
    {
        if ($this->products && $this->products->count() > 0) {
            return $this->products->first()->product_id;
        }
        return null;
    }
} 