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
        'project_id',
        'rental_id',
        'item_source',
        'selected_item',
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
     * Lấy thông tin dự án
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Lấy thông tin đơn thuê
     */
    public function rental()
    {
        return $this->belongsTo(Rental::class);
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

    /**
     * Lấy thông tin item được chọn
     */
    public function getSelectedItemInfoAttribute()
    {
        if (!$this->selected_item) {
            return null;
        }

        // Limit to 3 parts so serial number can contain colons
        $parts = explode(':', $this->selected_item, 3);
        if (count($parts) < 2) {
            return null;
        }

        $type = $parts[0];
        $id = $parts[1];
        $serialNumber = $parts[2] ?? null; // Serial number nếu có

        $item = null;
        switch ($type) {
            case 'product':
                $item = Product::find($id);
                break;
            case 'good':
                $item = Good::find($id);
                break;
            default:
                return null;
        }

        if (!$item) {
            // Nếu item không tồn tại, tạo object tạm thời với thông tin cơ bản
            $tempItem = new \stdClass();
            $tempItem->name = "Item không tồn tại (ID: {$id})";
            $tempItem->code = "N/A";
            $tempItem->selected_serial = $serialNumber;
            $tempItem->is_deleted = true;
            return $tempItem;
        }

        if ($serialNumber) {
            // Tạo object tạm thời để thêm serial number
            $itemWithSerial = clone $item;
            $itemWithSerial->selected_serial = $serialNumber;
            return $itemWithSerial;
        }

        return $item;
    }
}
