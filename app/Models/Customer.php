<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'company_phone',
        'email',
        'address',
        'notes',
        'has_account',
        'is_locked',
        'account_username',
        'account_password'
    ];
}
