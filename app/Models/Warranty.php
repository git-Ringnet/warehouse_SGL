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
            case 'project':
                // For project-wide warranty, return project info as pseudo-item
                return (object) [
                    'name' => 'Bảo hành dự án: ' . $this->project_name,
                    'code' => 'PROJECT-' . ($this->item_id ?: 'NA'),
                ];
            default:
                return null;
        }
    }

    /**
     * Get all items included in this warranty (for project-wide warranties)
     */
    public function getProjectItemsAttribute()
    {
        if ($this->item_type === 'project' && $this->item_id) {
            $items = [];
            
            // Get ALL dispatches for this project, not just the current one
            $projectDispatches = Dispatch::where('project_id', $this->item_id)->get();
            
            foreach ($projectDispatches as $dispatch) {
                // Only include contract items, not backup items
                $contractItems = $dispatch->items()->where('category', 'contract')->get();
                
                foreach ($contractItems as $dispatchItem) {
                    $itemDetails = null;
                    switch ($dispatchItem->item_type) {
                        case 'material':
                            $itemDetails = Material::find($dispatchItem->item_id);
                            break;
                        case 'product':
                            $itemDetails = Product::find($dispatchItem->item_id);
                            break;
                        case 'good':
                            $itemDetails = Good::find($dispatchItem->item_id);
                            break;
                    }
                    
                    if ($itemDetails) {
                        // Check if item already exists in array (from other dispatches)
                        $existingItemIndex = null;
                        foreach ($items as $index => $existingItem) {
                            if ($existingItem['code'] === $itemDetails->code && 
                                $existingItem['type'] === $dispatchItem->item_type) {
                                $existingItemIndex = $index;
                                break;
                            }
                        }
                        
                        if ($existingItemIndex !== null) {
                            // Item exists, add quantity and merge serial numbers
                            $items[$existingItemIndex]['quantity'] += $dispatchItem->quantity;
                            
                            if (!empty($dispatchItem->serial_numbers)) {
                                $existingSerials = $items[$existingItemIndex]['serial_numbers'] ?: [];
                                $newSerials = $dispatchItem->serial_numbers ?: [];
                                $items[$existingItemIndex]['serial_numbers'] = array_unique(array_merge($existingSerials, $newSerials));
                            }
                        } else {
                            // New item, add to array
                            $items[] = [
                                'code' => $itemDetails->code,
                                'name' => $itemDetails->name,
                                'quantity' => $dispatchItem->quantity,
                                'type' => $dispatchItem->item_type,
                                'serial_numbers' => $dispatchItem->serial_numbers,
                            ];
                        }
                    }
                }
            }
            
            return $items;
        }
        return [];
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
     * Get materials/components for each product in this warranty
     */
    public function getProductMaterialsAttribute()
    {
        if ($this->item_type === 'project' && $this->item_id) {
            $productMaterials = [];
            
            // Get ALL dispatches for this project
            $projectDispatches = Dispatch::where('project_id', $this->item_id)->get();
            
            foreach ($projectDispatches as $dispatch) {
                // Only include contract items
                $contractItems = $dispatch->items()->where('category', 'contract')->where('item_type', 'product')->get();
                
                foreach ($contractItems as $dispatchItem) {
                    $product = Product::find($dispatchItem->item_id);
                    if ($product) {
                        // Get assembly materials for this product using serial numbers
                        $serialNumbers = $dispatchItem->serial_numbers ?: [];
                        
                        foreach ($serialNumbers as $serialNumber) {
                            // Find assembly that created this serial number
                            $assemblyMaterial = AssemblyMaterial::whereHas('assembly', function($query) use ($serialNumber) {
                                $query->whereRaw("FIND_IN_SET(?, serials) > 0", [$serialNumber]);
                            })->where('target_product_id', $product->id)->with(['material', 'assembly'])->get();
                            
                            if ($assemblyMaterial->count() > 0) {
                                $materials = [];
                                foreach ($assemblyMaterial as $am) {
                                    if ($am->material) {
                                        $materials[] = [
                                            'code' => $am->material->code,
                                            'name' => $am->material->name,
                                            'quantity' => $am->quantity,
                                            'assembly_code' => $am->assembly->code ?? 'N/A',
                                            'serial' => $am->serial ?? 'N/A'
                                        ];
                                    }
                                }
                                
                                $productMaterials[] = [
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'serial_number' => $serialNumber,
                                    'materials' => $materials
                                ];
                            }
                        }
                        
                        // If no serial numbers, try to get general materials for this product
                        if (empty($serialNumbers)) {
                            $assemblyMaterials = AssemblyMaterial::where('target_product_id', $product->id)
                                ->with(['material', 'assembly'])->get();
                                
                            if ($assemblyMaterials->count() > 0) {
                                $materials = [];
                                foreach ($assemblyMaterials as $am) {
                                    if ($am->material) {
                                        $materials[] = [
                                            'code' => $am->material->code,
                                            'name' => $am->material->name,
                                            'quantity' => $am->quantity,
                                            'assembly_code' => $am->assembly->code ?? 'N/A',
                                            'serial' => $am->serial ?? 'N/A'
                                        ];
                                    }
                                }
                                
                                $productMaterials[] = [
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'serial_number' => 'N/A',
                                    'materials' => $materials
                                ];
                            }
                        }
                    }
                }
            }
            
            return $productMaterials;
        }
        return [];
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
