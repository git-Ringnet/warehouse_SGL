<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'rental_code',
        'rental_name',
        'customer_id',
        'employee_id',
        'rental_date',
        'due_date',
        'notes',
    ];

    /**
     * Lấy khách hàng của phiếu cho thuê.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Lấy nhân viên phụ trách phiếu cho thuê.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Lấy các nhóm quyền có quyền với hợp đồng cho thuê này
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rental_role')
                    ->withTimestamps();
    }

    /**
     * Lấy các phiếu xuất kho của phiếu cho thuê này
     */
    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'project_id');
    }

    /**
     * Kiểm tra xem phiếu cho thuê có quá hạn không.
     */
    public function isOverdue()
    {
        $due = \Carbon\Carbon::parse($this->due_date)->endOfDay();
        return now()->gt($due);
    }

    /**
     * Tính số ngày còn lại đến hạn trả.
     */
    public function daysRemaining()
    {
        $now = now()->startOfDay();
        $due = \Carbon\Carbon::parse($this->due_date)->endOfDay();
        return (int) $now->diffInDays($due, false);
    }
    
    /**
     * Tính số ngày bảo hành còn lại (tương tự như projects)
     * Đối với rentals, bảo hành được tính từ due_date
     */
    public function getRemainingWarrantyDaysAttribute()
    {
        // Lấy ngày hết hạn từ due_date
        $dueDate = \Carbon\Carbon::parse($this->due_date);
        
        // Nếu ngày hết hạn đã qua, trả về 0
        if ($dueDate->isPast()) {
            return 0;
        }
        
        // Tính số ngày còn lại từ hiện tại đến ngày hết hạn
        $now = \Carbon\Carbon::now();
        
        // Trả về số ngày còn lại (làm tròn xuống thành số nguyên)
        return (int) $now->diffInDays($dueDate);
    }
    
    /**
     * Kiểm tra xem bảo hành có còn hiệu lực không
     */
    public function getHasValidWarrantyAttribute()
    {
        return $this->remaining_warranty_days > 0;
    }
} 