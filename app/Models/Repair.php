<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_code',
        'warranty_code',
        'warranty_id',
        'repair_type',
        'repair_date',
        'technician_id',
        'warehouse_id',
        'repair_description',
        'repair_notes',
        'repair_photos',
        'status',
        'created_by',
        'maintenance_request_id',
    ];

    protected $casts = [
        'repair_date' => 'date',
        'repair_photos' => 'array',
    ];

    /**
     * Generate unique repair code
     */
    public static function generateRepairCode()
    {
        $prefix = 'SC';
        $year = date('Y');
        $month = date('m');
        
        // Get the last repair code for this month
        $lastRepair = self::where('repair_code', 'like', $prefix . $year . $month . '%')
            ->orderBy('repair_code', 'desc')
            ->first();
        
        if ($lastRepair) {
            $lastNumber = (int) substr($lastRepair->repair_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function repairItems()
    {
        return $this->hasMany(RepairItem::class);
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function materialReplacements()
    {
        return $this->hasMany(MaterialReplacementHistory::class);
    }

    public function damagedMaterials()
    {
        return $this->hasMany(DamagedMaterial::class);
    }

    /**
     * Get repair type label
     */
    public function getRepairTypeLabelAttribute()
    {
        $labels = [
            'maintenance' => 'Bảo trì định kỳ',
            'repair' => 'Sửa chữa lỗi',
            'replacement' => 'Thay thế linh kiện',
            'upgrade' => 'Nâng cấp',
            'other' => 'Khác',
        ];

        return $labels[$this->repair_type] ?? 'Không xác định';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Chờ xử lý',
            'in_progress' => 'Đang xử lý',
            'completed' => 'Đã xử lý',
            'cancelled' => 'Đã hủy',
        ];

        return $labels[$this->status] ?? 'Không xác định';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get repair type color
     */
    public function getRepairTypeColorAttribute()
    {
        $colors = [
            'maintenance' => 'green',
            'repair' => 'yellow',
            'replacement' => 'blue',
            'upgrade' => 'purple',
            'other' => 'gray',
        ];

        return $colors[$this->repair_type] ?? 'gray';
    }

    /**
     * Get customer name from warranty or maintenance request
     */
    public function getCustomerNameAttribute()
    {
        // First try to get from warranty
        if ($this->warranty && $this->warranty->customer_name) {
            return $this->warranty->customer_name;
        }
        
        // Then try to get from maintenance request
        if ($this->maintenanceRequest && $this->maintenanceRequest->customer_name) {
            return $this->maintenanceRequest->customer_name;
        }
        
        // If maintenance request has customer relationship, try that
        if ($this->maintenanceRequest && $this->maintenanceRequest->customer) {
            return $this->maintenanceRequest->customer->company_name ?? $this->maintenanceRequest->customer->name;
        }
        
        return null;
    }
}
