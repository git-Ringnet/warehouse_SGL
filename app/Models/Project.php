<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'project_code',
        'project_name',
        'customer_id',
        'employee_id',
        'start_date',
        'end_date',
        'warranty_period',
        'description',
    ];

    /**
     * Lấy khách hàng của dự án.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy nhân viên phụ trách dự án.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Lấy các nhóm quyền có quyền với dự án này
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'project_role')
                    ->withTimestamps();
    }

    /**
     * Lấy các phiếu xuất kho của dự án này
     */
    public function dispatches()
    {
        return $this->hasMany(Dispatch::class);
    }

    /**
     * Lấy warranty period formatted
     */
    public function getWarrantyPeriodFormattedAttribute()
    {
        return $this->warranty_period . ' tháng';
    }
    
    /**
     * Tính số ngày bảo hành còn lại
     */
    public function getRemainingWarrantyDaysAttribute()
    {
        // Lấy ngày bắt đầu từ start_date
        $startDate = Carbon::parse($this->start_date);
        
        // Đảm bảo warranty_period là số nguyên
        $warrantyPeriod = (int)$this->warranty_period;
        
        // Tính ngày kết thúc bảo hành bằng cách thêm số tháng bảo hành vào ngày bắt đầu
        $warrantyEndDate = $startDate->copy()->addMonths($warrantyPeriod);
        
        // Nếu ngày kết thúc bảo hành đã qua, trả về 0
        if ($warrantyEndDate->isPast()) {
            return 0;
        }
        
        // Tính số ngày còn lại từ hiện tại đến ngày kết thúc bảo hành
        $now = Carbon::now();
        
        // Trả về số ngày còn lại (làm tròn xuống thành số nguyên)
        return (int) $now->diffInDays($warrantyEndDate);
    }
    
    /**
     * Kiểm tra xem bảo hành có còn hiệu lực không
     */
    public function getHasValidWarrantyAttribute()
    {
        return $this->remaining_warranty_days > 0;
    }
    
    /**
     * Lấy ngày kết thúc bảo hành
     */
    public function getWarrantyEndDateAttribute()
    {
        // Lấy ngày bắt đầu từ start_date
        $startDate = Carbon::parse($this->start_date);
        
        // Đảm bảo warranty_period là số nguyên
        $warrantyPeriod = (int)$this->warranty_period;
        
        // Tính ngày kết thúc bảo hành bằng cách thêm số tháng bảo hành vào ngày bắt đầu
        return $startDate->copy()->addMonths($warrantyPeriod);
    }
} 