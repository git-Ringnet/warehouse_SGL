<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequestProduct extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'maintenance_request_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'unit',
        'description'
    ];

    /**
     * Lấy phiếu bảo trì liên quan.
     */
    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    /**
     * Lấy thông tin thành phẩm.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
} 