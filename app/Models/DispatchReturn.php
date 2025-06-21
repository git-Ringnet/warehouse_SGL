<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchReturn extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_code',
        'dispatch_item_id',
        'warehouse_id',
        'user_id',
        'return_date',
        'reason_type',
        'reason',
        'condition',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'return_date' => 'datetime',
    ];

    /**
     * Generate a unique return code.
     */
    public static function generateReturnCode(): string
    {
        $prefix = 'RT';
        $year = date('y');
        $month = date('m');
        
        // Get the last return code for this month
        $lastReturn = self::where('return_code', 'like', $prefix . $year . $month . '%')
            ->orderBy('return_code', 'desc')
            ->first();
        
        if ($lastReturn) {
            $lastNumber = (int) substr($lastReturn->return_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the dispatch item for this return.
     */
    public function dispatchItem(): BelongsTo
    {
        return $this->belongsTo(DispatchItem::class);
    }

    /**
     * Get the warehouse for this return.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who processed this return.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the condition label.
     */
    public function getConditionLabelAttribute(): string
    {
        return match($this->condition) {
            'good' => 'Hoạt động tốt',
            'damaged' => 'Hư hỏng nhẹ',
            'broken' => 'Hư hỏng nặng',
            default => 'Không xác định'
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Get the reason type label.
     */
    public function getReasonTypeLabelAttribute(): string
    {
        return match($this->reason_type) {
            'warranty' => 'Bảo hành',
            'return' => 'Trả về',
            'replacement' => 'Thay thế',
            default => 'Không xác định'
        };
    }
} 