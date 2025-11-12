<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'role_id',
        'customer_id',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the customer associated with the user.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the role group associated with the user.
     * This relationship is for compatibility with permission checking system.
     */
    public function roleGroup()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has specific permission
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
}
