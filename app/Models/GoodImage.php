<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'good_id',
        'image_path',
        'sort_order'
    ];

    /**
     * Get the good that this image belongs to.
     */
    public function good()
    {
        return $this->belongsTo(Good::class);
    }
} 