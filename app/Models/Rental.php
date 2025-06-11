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
     * Kiểm tra xem phiếu cho thuê có quá hạn không.
     */
    public function isOverdue()
    {
        return now()->gt($this->due_date);
    }

    /**
     * Tính số ngày còn lại đến hạn trả.
     */
    public function daysRemaining()
    {
        return now()->diffInDays($this->due_date, false);
    }
} 