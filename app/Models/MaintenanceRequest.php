<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'request_code',
        'request_date',
        'proposer_id',
        'project_type',
        'project_id',
        'project_name',
        'customer_id',
        'warranty_id',
        'project_address',
        'maintenance_date',
        'maintenance_type',
        'maintenance_reason',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'notes',
        'status',
        'reject_reason',
    ];

    /**
     * Các thuộc tính cần chuyển đổi.
     *
     * @var array
     */
    protected $casts = [
        'request_date' => 'date',
        'maintenance_date' => 'date',
    ];

    /**
     * Lấy nhân viên đề xuất.
     */
    public function proposer()
    {
        return $this->belongsTo(Employee::class, 'proposer_id');
    }

    /**
     * Lấy khách hàng liên quan.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy danh sách thành phẩm cần bảo trì.
     */
    public function products()
    {
        return $this->hasMany(MaintenanceRequestProduct::class);
    }

    /**
     * Lấy danh sách nhân sự thực hiện.
     */
    public function staff()
    {
        return $this->belongsToMany(Employee::class, 'maintenance_request_staff');
    }

    /**
     * Lấy thông tin bảo hành liên quan.
     */
    public function warranty()
    {
        return $this->belongsTo(Warranty::class, 'warranty_id');
    }

    /**
     * Lấy danh sách phiếu sửa chữa được tạo từ phiếu bảo trì này.
     */
    public function repairs()
    {
        return $this->hasMany(Repair::class, 'maintenance_request_id');
    }

    /**
     * Tạo mã phiếu bảo trì tự động
     */
    public static function generateRequestCode()
    {
        $latestRequest = self::orderBy('id', 'desc')->first();
        $nextId = $latestRequest ? $latestRequest->id + 1 : 1;
        return 'CUS-MAINT-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
} 