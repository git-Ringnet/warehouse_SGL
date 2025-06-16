<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_code',
        'dispatch_id',
        'dispatch_item_id',
        'item_type',
        'item_id',
        'serial_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'project_name',
        'purchase_date',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_period_months',
        'warranty_type',
        'status',
        'warranty_terms',
        'notes',
        'qr_code',
        'created_by',
        'activated_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'activated_at' => 'datetime',
    ];

    /**
     * Generate unique warranty code
     */
    public static function generateWarrantyCode()
    {
        $prefix = 'BH';
        $year = date('Y');
        $month = date('m');
        
        // Get the last warranty code for this month
        $lastWarranty = self::where('warranty_code', 'like', $prefix . $year . $month . '%')
            ->orderBy('warranty_code', 'desc')
            ->first();
        
        if ($lastWarranty) {
            $lastNumber = (int) substr($lastWarranty->warranty_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function dispatchItem()
    {
        return $this->belongsTo(DispatchItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function material()
    {
        return $this->morphTo('item', 'item_type', 'item_id')->where('item_type', 'material');
    }

    public function product()
    {
        return $this->morphTo('item', 'item_type', 'item_id')->where('item_type', 'product');
    }

    public function good()
    {
        return $this->morphTo('item', 'item_type', 'item_id')->where('item_type', 'good');
    }

    /**
     * Get the item (material, product, or good)
     */
    public function getItemAttribute()
    {
        switch ($this->item_type) {
            case 'material':
                return Material::find($this->item_id);
            case 'product':
                return Product::find($this->item_id);
            case 'good':
                return Good::find($this->item_id);
            default:
                return null;
        }
    }

    /**
     * Check if warranty is still active
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && $this->warranty_end_date >= now()->toDateString();
    }

    /**
     * Get remaining warranty days
     */
    public function getRemainingDaysAttribute()
    {
        if ($this->warranty_end_date < now()->toDateString()) {
            return 0;
        }
        
        return now()->diffInDays($this->warranty_end_date);
    }

    /**
     * Get warranty status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Còn hiệu lực',
            'expired' => 'Hết hạn',
            'claimed' => 'Đã sử dụng',
            'void' => 'Đã hủy',
        ];

        return $labels[$this->status] ?? 'Không xác định';
    }

    /**
     * Get warranty status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'active' => 'green',
            'expired' => 'red',
            'claimed' => 'yellow',
            'void' => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Generate QR code for warranty
     */
    public function generateQRCode()
    {
        // This will be the URL to check warranty status
        $url = url('/warranty/check/' . $this->warranty_code);
        $this->qr_code = $url;
        $this->save();
        
        return $url;
    }
}
