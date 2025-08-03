<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerMaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_code',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'project_name',
        'project_description',
        'request_date',
        'maintenance_reason',
        'maintenance_details',

        'estimated_cost',
        'priority',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'request_date' => 'date',

        'approved_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
    ];

    /**
     * Lấy thông tin khách hàng của phiếu yêu cầu
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy thông tin người duyệt
     */
    public function approvedByUser()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Scope lấy các phiếu yêu cầu theo trạng thái
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope lấy các phiếu yêu cầu của khách hàng
     */
    public function scopeForCustomer($query, $customerId)
    {
        if ($customerId) {
            return $query->where('customer_id', $customerId);
        }
        return $query;
    }
}
