<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamagedMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_id',
        'device_code',
        'material_code',
        'material_name',
        'serial',
        'damage_description',
        'reported_by',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }
}
