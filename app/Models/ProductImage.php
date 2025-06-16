<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order'
    ];

    /**
     * Get the product that owns this image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
