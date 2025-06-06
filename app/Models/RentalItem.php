<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalItem extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'rental_id',
        'item_type',
        'item_id',
        'quantity',
        'price',
        'notes',
        'serial_numbers',
    ];

    /**
     * Các thuộc tính nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'serial_numbers' => 'array',
    ];

    /**
     * Lấy phiếu cho thuê của thiết bị này.
     */
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Lấy thông tin thiết bị dựa vào item_type.
     */
    public function item()
    {
        if ($this->item_type === 'product') {
            return $this->belongsTo(Product::class, 'item_id');
        } else if ($this->item_type === 'material') {
            return $this->belongsTo(Material::class, 'item_id');
        }
        return null;
    }
} 