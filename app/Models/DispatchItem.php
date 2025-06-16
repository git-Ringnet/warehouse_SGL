<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispatchItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dispatch_id',
        'item_type',
        'item_id',
        'quantity',
        'category',
        'serial_numbers',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'serial_numbers' => 'array',
        'quantity' => 'integer',
    ];

    /**
     * Get the dispatch that owns this item.
     */
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    /**
     * Get the material for this dispatch item (if item_type is material).
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'item_id');
    }

    /**
     * Get the product for this dispatch item (if item_type is product).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Get the good for this dispatch item (if item_type is good).
     */
    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class, 'item_id');
    }

    /**
     * Get the warranties for this dispatch item.
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    /**
     * Get the item details based on item_type.
     */
    public function getItemAttribute()
    {
        return match($this->item_type) {
            'material' => $this->material,
            'product' => $this->product,
            'good' => $this->good,
            default => null
        };
    }

    /**
     * Get the item name.
     */
    public function getItemNameAttribute(): string
    {
        $item = $this->item;
        return $item ? $item->name : 'Không xác định';
    }

    /**
     * Get the item code.
     */
    public function getItemCodeAttribute(): string
    {
        $item = $this->item;
        return $item ? $item->code : 'N/A';
    }

    /**
     * Get the item unit.
     */
    public function getItemUnitAttribute(): string
    {
        $item = $this->item;
        return $item ? $item->unit : 'N/A';
    }

    /**
     * Get the item type label.
     */
    public function getItemTypeLabelAttribute(): string
    {
        return match($this->item_type) {
            'material' => 'Vật tư',
            'product' => 'Thành phẩm',
            'good' => 'Hàng hóa',
            default => 'Không xác định'
        };
    }
} 