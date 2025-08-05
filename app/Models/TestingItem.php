<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'testing_id',
        'item_type',
        'material_id',
        'product_id',
        'good_id',
        'warehouse_id',
        'assembly_id',
        'serial_number',
        'supplier_id',
        'batch_number',
        'quantity',
        'result',
        'pass_quantity',
        'fail_quantity',
        'serial_results',
    ];

    /**
     * Get the testing that owns this item.
     */
    public function testing()
    {
        return $this->belongsTo(Testing::class);
    }

    /**
     * Get the material associated with this testing item.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the product associated with this testing item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the good associated with this testing item.
     */
    public function good()
    {
        return $this->belongsTo(Good::class);
    }

    /**
     * Get the warehouse associated with this testing item.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the assembly associated with this testing item.
     */
    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Get the supplier associated with this testing item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the item name based on its type.
     */
    public function getItemNameAttribute()
    {
        switch ($this->item_type) {
            case 'material':
                return $this->material ? $this->material->name : 'N/A';
            case 'product':
                return $this->product ? $this->product->name : 'N/A';
            case 'finished_product':
                return $this->good ? $this->good->name : 'N/A';
            default:
                return 'N/A';
        }
    }

    /**
     * Get the result text.
     */
    public function getResultTextAttribute()
    {
        $results = [
            'pass' => 'Đạt',
            'fail' => 'Không đạt',
            'pending' => 'Chưa có',
        ];

        return $results[$this->result] ?? 'Không xác định';
    }
} 