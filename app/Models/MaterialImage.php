<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'material_id',
        'image_path',
        'sort_order'
    ];

    /**
     * Get the material that owns this image.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
} 