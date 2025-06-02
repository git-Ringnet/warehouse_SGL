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
        'quantity',
        'serial',
        'note',
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
} 