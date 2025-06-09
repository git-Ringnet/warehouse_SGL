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
     * Relationship với vật tư
     */
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    
    /**
     * Relationship với hàng hóa
     */
    public function goods()
    {
        return $this->hasMany(Good::class);
    }
}
