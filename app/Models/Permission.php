<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
    ];

    /**
     * Quan hệ nhiều-nhiều với bảng roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
