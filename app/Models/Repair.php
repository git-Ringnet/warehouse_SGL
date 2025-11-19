<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Uses database lock to prevent race conditions
     */
    public static function generateRepairCode()
    {
        $prefix = 'SC';
        $year = date('Y');
        $month = date('m');
        
        // Check if we're already in a transaction
        $inTransaction = DB::transactionLevel() > 0;
        
        $generateCode = function () use ($prefix, $year, $month) {
            // Lock the table to prevent concurrent access
            // Get the last repair code for this month with lock
            $lastRepair = self::where('repair_code', 'like', $prefix . $year . $month . '%')
                ->lockForUpdate() // Lock rows to prevent concurrent reads
                ->orderBy('repair_code', 'desc')
                ->first();
            
            if ($lastRepair) {
                $lastNumber = (int) substr($lastRepair->repair_code, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        };
        
        // If already in transaction, don't create nested transaction
        if ($inTransaction) {
            return $generateCode();
        }
        
        // Otherwise, wrap in transaction for safety
        return DB::transaction($generateCode);
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
            'completed' => 'Hoàn thành',
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
        // Ưu tiên định dạng theo nguồn của bảo hành (chuẩn theo dự án/cho thuê)
        if ($this->warranty) {
            // Dự án: Mã dự án - Tên dự án (Tên người đại diện khách hàng)
            if ($this->warranty->item_type === 'project' && $this->warranty->item_id) {
                $project = \App\Models\Project::with('customer')->find($this->warranty->item_id);
                if ($project) {
                    $customerRep = $project->customer->name
                        ?? $project->customer->company_name
                        ?? 'N/A';
                    return sprintf('%s - %s (%s)', $project->project_code, $project->project_name, $customerRep);
                }
            }

            // Cho thuê: Mã phiếu - Tên phiếu (Tên người đại diện khách hàng)
            if ($this->warranty->item_type === 'rental' && $this->warranty->item_id) {
                $rental = \App\Models\Rental::with('customer')->find($this->warranty->item_id);
                if ($rental) {
                    $customerRep = $rental->customer->name
                        ?? $rental->customer->company_name
                        ?? 'N/A';
                    return sprintf('%s - %s (%s)', $rental->rental_code, $rental->rental_name, $customerRep);
                }
            }

            // Nếu bảo hành có sẵn tên khách hàng, dùng như fallback
            if ($this->warranty->customer_name) {
                return $this->warranty->customer_name;
            }
        }

        // Fallback: từ phiếu bảo trì (nếu có)
        if ($this->maintenanceRequest) {
            // Nếu có quan hệ customer => lấy tên đại diện; nếu không có thì dùng tên lưu sẵn
            if ($this->maintenanceRequest->customer) {
                return $this->maintenanceRequest->customer->name
                    ?? $this->maintenanceRequest->customer->company_name
                    ?? $this->maintenanceRequest->customer_name;
            }
            if ($this->maintenanceRequest->customer_name) {
                return $this->maintenanceRequest->customer_name;
            }
        }

        return null;
    }
}
