<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'testing_id',
        'test_item_name',
        'result',
        'notes',
    ];

    /**
     * Get the testing that owns this detail.
     */
    public function testing()
    {
        return $this->belongsTo(Testing::class);
    }

    /**
     * Get the result text.
     */
    public function getResultTextAttribute()
    {
        $results = [
            'pass' => 'Đạt',
            'fail' => 'Không đạt',
            'pending' => 'Chưa có',
        ];

        return $results[$this->result] ?? 'Không xác định';
    }
} 