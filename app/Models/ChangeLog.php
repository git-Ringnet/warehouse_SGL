<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ChangeLog extends Model
{
    use HasFactory;

    protected $table = 'change_logs';

    protected $fillable = [
        'time_changed',
        'item_code',
        'item_name',
        'change_type',
        'document_code',
        'quantity',
        'description',
        'performed_by',
        'notes',
        'detailed_info'
    ];

    protected $casts = [
        'time_changed' => 'datetime',
        'detailed_info' => 'array'
    ];

    // Constants for change types
    const CHANGE_TYPES = [
        'lap_rap' => 'Lắp ráp',
        'xuat_kho' => 'Xuất kho',
        'sua_chua' => 'Sửa chữa',
        'thu_hoi' => 'Thu hồi',
        'nhap_kho' => 'Nhập kho',
        'chuyen_kho' => 'Chuyển kho'
    ];

    // Get change type label
    public function getChangeTypeLabel()
    {
        return self::CHANGE_TYPES[$this->change_type] ?? $this->change_type;
    }

    // Scope for filtering by change type
    public function scopeByChangeType($query, $changeType)
    {
        return $query->where('change_type', $changeType);
    }

    // Scope for filtering by date range
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        return $query->whereBetween('time_changed', [$start, $end]);
    }

    // Scope for searching by item
    public function scopeByItem($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('item_code', 'like', "%{$search}%")
              ->orWhere('item_name', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }
}
