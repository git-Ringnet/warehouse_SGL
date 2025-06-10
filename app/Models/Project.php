<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Lấy các nhóm quyền có quyền với dự án này
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'project_role')
                    ->withTimestamps();
    }
} 