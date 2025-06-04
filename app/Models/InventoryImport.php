<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'import_code',
        'import_date',
        'order_code',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_date' => 'date',
        'supplier_id' => 'integer',
        'warehouse_id' => 'integer',
    ];

    /**
     * Get the supplier that owns this inventory import.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse that owns this inventory import.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the materials for this inventory import.
     */
    public function materials()
    {
        return $this->hasMany(InventoryImportMaterial::class);
    }
} 