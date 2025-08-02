<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRequest extends Model
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
        'implementer_id',
        'assembly_leader_id',
        'tester_id',
        'project_name',
        'customer_id',
        'project_id',
        'rental_id',
        'project_address',
        'approval_method',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'notes',
        'status',
    ];

    /**
     * Các thuộc tính cần chuyển đổi.
     *
     * @var array
     */
    protected $casts = [
        'request_date' => 'date',
    ];

    /**
     * Lấy nhân viên đề xuất.
     */
    public function proposer()
    {
        return $this->belongsTo(Employee::class, 'proposer_id');
    }

    /**
     * Lấy nhân viên thực hiện.
     */
    public function implementer()
    {
        return $this->belongsTo(Employee::class, 'implementer_id');
    }

    /**
     * Lấy người phụ trách lắp ráp.
     */
    public function assembly_leader()
    {
        return $this->belongsTo(Employee::class, 'assembly_leader_id');
    }

    /**
     * Lấy người tiếp nhận kiểm thử.
     */
    public function tester()
    {
        return $this->belongsTo(Employee::class, 'tester_id');
    }

    /**
     * Lấy khách hàng liên quan.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy danh sách các thiết bị và vật tư trong phiếu đề xuất.
     */
    public function items()
    {
        return $this->hasMany(ProjectRequestItem::class);
    }

    /**
     * Lấy danh sách thiết bị trong phiếu đề xuất.
     */
    public function equipments()
    {
        return $this->hasMany(ProjectRequestItem::class)->where('item_type', 'equipment');
    }

    /**
     * Lấy danh sách vật tư trong phiếu đề xuất.
     */
    public function materials()
    {
        return $this->hasMany(ProjectRequestItem::class)->where('item_type', 'material');
    }

    /**
     * Tạo mã phiếu đề xuất tự động
     */
    public static function generateRequestCode()
    {
        $latestRequest = self::orderBy('id', 'desc')->first();
        $nextId = $latestRequest ? $latestRequest->id + 1 : 1;
        return 'REQ-PRJ-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
} 