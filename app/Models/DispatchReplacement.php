<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchReplacement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'replacement_code',
        'original_dispatch_item_id',
        'replacement_dispatch_item_id',
        'original_serial',
        'replacement_serial',
        'user_id',
        'replacement_date',
        'reason',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'replacement_date' => 'datetime',
    ];

    /**
     * Generate a unique replacement code.
     */
    public static function generateReplacementCode(): string
    {
        $prefix = 'RP';
        $year = date('y');
        $month = date('m');
        
        // Get the last replacement code for this month
        $lastReplacement = self::where('replacement_code', 'like', $prefix . $year . $month . '%')
            ->orderBy('replacement_code', 'desc')
            ->first();
        
        if ($lastReplacement) {
            $lastNumber = (int) substr($lastReplacement->replacement_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the original dispatch item for this replacement.
     */
    public function originalDispatchItem(): BelongsTo
    {
        return $this->belongsTo(DispatchItem::class, 'original_dispatch_item_id');
    }

    /**
     * Get the replacement dispatch item for this replacement.
     */
    public function replacementDispatchItem(): BelongsTo
    {
        return $this->belongsTo(DispatchItem::class, 'replacement_dispatch_item_id');
    }

    /**
     * Get the user who processed this replacement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
} 