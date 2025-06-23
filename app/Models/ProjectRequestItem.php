<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRequestItem extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'project_request_id',
        'item_type',
        'name',
        'code',
        'unit',
        'quantity',
        'description',
        'product_id',
        'material_id',
        'item_id',
        'notes',
    ];

    /**
     * Lấy phiếu đề xuất chứa item này.
     */
    public function projectRequest()
    {
        return $this->belongsTo(ProjectRequest::class);
    }

    /**
     * Lấy sản phẩm liên quan (nếu có).
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lấy vật tư liên quan (nếu có).
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Lấy hàng hóa liên quan (nếu có).
     */
    public function good()
    {
        return $this->belongsTo(Good::class, 'item_id');
    }
    
    /**
     * Lấy thiết bị liên quan (nếu có).
     */
    public function equipment()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
    
    /**
     * Lấy vật tư liên quan (nếu có).
     */
    public function materialItem()
    {
        return $this->belongsTo(Material::class, 'item_id');
    }
} 