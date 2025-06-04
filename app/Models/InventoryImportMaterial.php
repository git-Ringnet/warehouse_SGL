<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryImportMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_import_id',
        'material_id',
        'quantity',
        'serial',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inventory_import_id' => 'integer',
        'material_id' => 'integer',
        'quantity' => 'integer',
    ];

    /**
     * Get the inventory import that owns this inventory import material.
     */
    public function inventoryImport()
    {
        return $this->belongsTo(InventoryImport::class);
    }

    /**
     * Get the material for this inventory import material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
} 