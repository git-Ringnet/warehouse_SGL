<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'name', 
        'email', 
        'phone', 
        'address', 
        'hire_date',
        'notes',
        'role',
        'role_id',
        'scope_value',
        'scope_type',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Thiết lập mật khẩu có mã hóa
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
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
} 