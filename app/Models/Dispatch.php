<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dispatch_code',
        'dispatch_date',
        'dispatch_type',
        'dispatch_detail',
        'project_id',
        'project_receiver',
        'warranty_period',
        'company_representative_id',
        'dispatch_note',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dispatch_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the project for this dispatch.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the rental for this dispatch.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class, 'project_id');
    }

    /**
     * Get the user who created this dispatch.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Get the employee who is the company representative.
     */
    public function companyRepresentative(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'company_representative_id');
    }

    /**
     * Get the employee who approved this dispatch.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the dispatch items for this dispatch.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DispatchItem::class);
    }

    /**
     * Get the warranties for this dispatch.
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }


    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get the dispatch type label.
     */
    public function getDispatchTypeLabelAttribute(): string
    {
        return match($this->dispatch_type) {
            'project' => 'Dự án',
            'rental' => 'Cho thuê',
            'warranty' => 'Bảo hành',
            default => 'Không xác định'
        };
    }

    /**
     * Get the dispatch detail label.
     */
    public function getDispatchDetailLabelAttribute(): string
    {
        return match($this->dispatch_detail) {
            'all' => 'Tất cả',
            'contract' => 'Xuất theo hợp đồng',
            'backup' => 'Xuất thiết bị dự phòng',
            default => 'Không xác định'
        };
    }

    /**
     * Generate the next dispatch code.
     */
    public static function generateDispatchCode(): string
    {
        $today = now()->format('Ymd');
        $prefix = "XK{$today}";
        
        $lastDispatch = static::where('dispatch_code', 'LIKE', "{$prefix}-%")
            ->orderBy('dispatch_code', 'desc')
            ->first();
        
        if ($lastDispatch) {
            $lastNumber = (int) substr($lastDispatch->dispatch_code, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total items count for this dispatch.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }
} 