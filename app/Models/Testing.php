<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_code',
        'test_type',
        'tester_id',
        'assigned_to',
        'receiver_id',
        'test_date',
        'status',
        'notes',
        'conclusion',
        'pass_quantity',
        'fail_quantity',
        'fail_reasons',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'is_inventory_updated',
        'success_warehouse_id',
        'fail_warehouse_id',
        'assembly_id',
    ];

    protected $casts = [
        'test_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'is_inventory_updated' => 'boolean',
    ];

    /**
     * Get the tester for this test (người tạo phiếu).
     */
    public function tester()
    {
        return $this->belongsTo(Employee::class, 'tester_id');
    }

    /**
     * Get the assigned employee for this test (người phụ trách).
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Get the receiver employee for this test (người tiếp nhận).
     */
    public function receiverEmployee()
    {
        return $this->belongsTo(Employee::class, 'receiver_id');
    }

    /**
     * Get the employee who approved this test.
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the employee that received this testing.
     */
    public function receiver()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    /**
     * Get the success warehouse for this testing.
     */
    public function successWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'success_warehouse_id');
    }

    /**
     * Get the fail warehouse for this testing.
     */
    public function failWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'fail_warehouse_id');
    }

    /**
     * Get the items for this test.
     */
    public function items()
    {
        return $this->hasMany(TestingItem::class);
    }

    /**
     * Get the testing details for this test.
     */
    public function details()
    {
        return $this->hasMany(TestingDetail::class);
    }

    /**
     * Get the assembly related to this testing.
     */
    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Get the pass rate as a percentage.
     */
    public function getPassRateAttribute()
    {
        $total = $this->pass_quantity + $this->fail_quantity;
        if ($total > 0) {
            return round(($this->pass_quantity / $total) * 100);
        }
        return 0;
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $statuses[$this->status] ?? 'Không xác định';
    }

    /**
     * Get the test type text.
     */
    public function getTestTypeTextAttribute()
    {
        $types = [
            'material' => 'Vật tư/Hàng hóa',
            'finished_product' => 'Thiết bị thành phẩm',
        ];

        return $types[$this->test_type] ?? 'Không xác định';
    }
} 