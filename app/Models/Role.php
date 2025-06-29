<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'scope',
        'is_active',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Quan hệ nhiều-nhiều với bảng permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Quan hệ một-nhiều với bảng employees
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Lấy các dự án được gán cho nhóm quyền này
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_role')
                    ->withTimestamps();
    }

    /**
     * Lấy các hợp đồng cho thuê được gán cho nhóm quyền này
     */
    public function rentals()
    {
        return $this->belongsToMany(Rental::class, 'rental_role')
                    ->withTimestamps();
    }

    /**
     * Kiểm tra xem role có quyền cụ thể không
     */
    public function hasPermission($permissionName)
    {
        return $this->is_active && $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Kiểm tra quyền trùng lặp với một role khác
     */
    public function hasDuplicatePermissionsWith($otherRoleId)
    {
        if (!$otherRoleId) {
            return [
                'has_duplicates' => false,
                'duplicate_permissions' => collect()
            ];
        }
        
        $otherRole = Role::find($otherRoleId);
        if (!$otherRole) {
            return [
                'has_duplicates' => false,
                'duplicate_permissions' => collect()
            ];
        }

        $thisPermissions = $this->permissions()->pluck('permissions.id')->toArray();
        $otherPermissions = $otherRole->permissions()->pluck('permissions.id')->toArray();
        
        // Tìm các quyền trùng lặp
        $duplicates = array_intersect($thisPermissions, $otherPermissions);
        
        return [
            'has_duplicates' => count($duplicates) > 0,
            'duplicate_permissions' => Permission::whereIn('id', $duplicates)->get()
        ];
    }
}
