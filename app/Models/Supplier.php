<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'representative'
    ];

    /**
     * Relationship với vật tư (many-to-many)
     */
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'material_supplier');
    }
    
    /**
     * Relationship với hàng hóa (many-to-many)
     */
    public function goods()
    {
        return $this->belongsToMany(Good::class, 'good_supplier');
    }
}
