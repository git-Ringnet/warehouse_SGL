<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_number',
        'product_id',
        'status',
        'notes'
    ];

    /**
     * Get the product that owns the serial.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
} 