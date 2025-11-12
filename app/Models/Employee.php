<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'role',
        'status',
        'avatar',
        'role_id',
        'department',
        'scope_value',
        'scope_type',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Không sử dụng casts cho password
    ];

    /**
     * Thiết lập mật khẩu có mã hóa
     */
    public function setPasswordAttribute($value)
    {
        // Chỉ mã hóa nếu mật khẩu chưa được mã hóa
        if ($value && substr($value, 0, 4) !== '$2y$') {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Quan hệ với bảng roles
     */
    public function roleGroup()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Quan hệ với bảng user_logs
     */
    public function logs()
    {
        return $this->hasMany(UserLog::class, 'user_id');
    }

    /**
     * Lấy danh sách dự án mà nhân viên phụ trách
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Lấy danh sách phiếu cho thuê mà nhân viên phụ trách
     */
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Kiểm tra nhân viên có quyền nào đó không
     */
    public function hasPermission($permissionName)
    {
        // Nếu role là admin, luôn có quyền
        if ($this->role === 'admin') {
            return true;
        }

        // Kiểm tra quyền từ nhóm quyền
        if ($this->role_id && $this->roleGroup) {
            // Kiểm tra xem nhóm quyền có đang kích hoạt không
            if (!$this->roleGroup->is_active) {
                return false;
            }
            return $this->roleGroup->hasPermission($permissionName);
        }

        return false;
    }

    /**
     * Kiểm tra nhân viên có thuộc phạm vi nào đó không
     */
    public function inScope($scopeType, $scopeValue)
    {
        // Admin không giới hạn phạm vi
        if ($this->role === 'admin') {
            return true;
        }

        // Nếu nhân viên không có phạm vi cụ thể
        if (empty($this->scope_type) || empty($this->scope_value)) {
            return false;
        }

        // Kiểm tra phạm vi
        return $this->scope_type === $scopeType && $this->scope_value === $scopeValue;
    }

    /**
     * Phương thức để khóa/mở tài khoản nhân viên
     */
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        $this->save();
        return $this->is_active;
    }

    /**
     * Lấy danh sách kho mà nhân viên quản lý
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'manager')->whereNull('deleted_at');
    }
} 