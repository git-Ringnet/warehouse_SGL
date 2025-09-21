<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Project;

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
        return $this->belongsTo(Material::class, 'item_id')->where('item_type', 'material');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')->where('item_type', 'product');
    }

    public function good()
    {
        return $this->belongsTo(Good::class, 'item_id')->where('item_type', 'good');
    }

    /**
     * Get the polymorphic relationship for the item
     */
    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    /**
     * Get the item (material, product, or good)
     */
    public function getItemAttribute()
    {
        // Use the polymorphic relationship if available
        if ($this->item_type && $this->item_id) {
            switch ($this->item_type) {
                case 'material':
                    return Material::find($this->item_id);
                case 'product':
                    return Product::find($this->item_id);
                case 'good':
                    return Good::find($this->item_id);
                case 'project':
                    // For project-wide warranty, return project info as pseudo-item
                    $project = Project::find($this->item_id);
                    if ($project) {
                        return (object) [
                            'name' => 'Bảo hành dự án: ' . $project->project_name,
                            'code' => 'PROJECT-' . $project->id,
                        ];
                    }
                    return (object) [
                        'name' => 'Bảo hành dự án: ' . $this->project_name,
                        'code' => 'PROJECT-' . ($this->item_id ?: 'NA'),
                    ];
            }
        }
        return null;
    }

    /**
     * Get all items included in this warranty (for project-wide warranties)
     */
    public function getProjectItemsAttribute()
    {
        if ($this->item_type === 'project' && $this->item_id) {
            $items = [];

            // Determine dispatch type from the warranty's original dispatch
            $originalDispatch = $this->dispatch;
            $dispatchType = $originalDispatch ? $originalDispatch->dispatch_type : null;

            // Get ONLY APPROVED dispatches for this project with the same dispatch_type
            $projectDispatches = Dispatch::where('project_id', $this->item_id)
                ->whereIn('status', ['approved', 'completed']);
            
            // Only filter by dispatch_type if we can determine it from the original dispatch
            if ($dispatchType) {
                $projectDispatches = $projectDispatches->where('dispatch_type', $dispatchType);
            }
            
            $projectDispatches = $projectDispatches->get();

            foreach ($projectDispatches as $dispatch) {
                // Bao gồm tất cả item cần bảo hành: loại trừ 'backup'
                $eligibleItems = $dispatch->items()->where('category', '!=', 'backup')->get();

                foreach ($eligibleItems as $dispatchItem) {
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
                        // Thêm dispatch_item_id để phân biệt các sản phẩm từ phiếu xuất khác nhau
                        $items[] = [
                            'code' => $itemDetails->code,
                            'name' => $itemDetails->name,
                            'quantity' => $dispatchItem->quantity,
                            'type' => $dispatchItem->item_type,
                            'serial_numbers' => $dispatchItem->serial_numbers,
                            'dispatch_item_id' => $dispatchItem->id, // Thêm dispatch_item_id để phân biệt
                            'dispatch_id' => $dispatch->id, // Thêm dispatch_id để debug
                        ];
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
        // Active khi cột status = 'active' và ngày hết hạn vẫn còn trong tương lai
        return $this->status === 'active'
            && $this->warranty_end_date
            && \Carbon\Carbon::parse($this->warranty_end_date)->endOfDay()->isFuture();
    }

    /**
     * Get remaining warranty days
     */
    public function getRemainingTimeAttribute()
    {
        // Nếu chưa kích hoạt
        if (!$this->activated_at) {
            return null; // Sẽ hiển thị "Chưa kích hoạt"
        }

        // Tính ngày hết hạn từ ngày kích hoạt (sử dụng copy để không thay đổi giá trị gốc)
        $actualEndDate = $this->activated_at->copy()->addMonths($this->warranty_period_months ?? 12);
        
        // Nếu đã hết hạn (ngày hiện tại > ngày hết hạn)
        if (now() > $actualEndDate) {
            return 0;
        }

        // Tính số ngày còn lại (từ hôm nay đến ngày hết hạn)
        $remainingDays = now()->diffInDays($actualEndDate, false);
        
        // Làm tròn thành số nguyên
        return (int) ceil($remainingDays);
    }

    /**
     * Get remaining time with days, hours and minutes
     */
    // public function getRemainingTimeAttribute()
    // {
    //     // Nếu chưa kích hoạt
    //     if (!$this->activated_at) {
    //         return null; // Sẽ hiển thị "Chưa kích hoạt"
    //     }

    //     // Tính ngày hết hạn từ ngày kích hoạt
    //     $actualEndDate = $this->activated_at->copy()->addMonths($this->warranty_period_months ?? 12);
        
    //     // Nếu đã hết hạn
    //     if (now() > $actualEndDate) {
    //         return 0;
    //     }

    //     // Tính tổng số phút còn lại
    //     $totalMinutes = now()->diffInMinutes($actualEndDate, false);
        
    //     // Tính ngày, giờ và phút
    //     $days = floor($totalMinutes / (24 * 60));
    //     $hours = floor(($totalMinutes % (24 * 60)) / 60);
    //     $minutes = $totalMinutes % 60;
        
    //     // Format hiển thị
    //     $parts = [];
        
    //     if ($days > 0) {
    //         $parts[] = "{$days} ngày";
    //     }
        
    //     if ($hours > 0) {
    //         $parts[] = "{$hours} giờ";
    //     }
        
    //     if ($minutes > 0) {
    //         $parts[] = "{$minutes} phút";
    //     }
        
    //     // Nếu không có gì thì hiển thị "Dưới 1 phút"
    //     if (empty($parts)) {
    //         return "Dưới 1 phút";
    //     }
        
    //     return implode(' ', $parts);
    // }

    /**
     * Get warranty status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Còn hiệu lực',
            'expired' => 'Hết hạn',
        ];

        return $labels[$this->status] ?? 'Không xác định';
    }

    /**
     * Get warranty status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-red-100 text-red-800',
            'claimed' => 'bg-yellow-100 text-yellow-800',
            'void' => 'bg-gray-100 text-gray-800',
        ];

        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get materials/components for each product in this warranty
     *
     * Only include materials that come from assemblies whose project_id matches
     * the warranty's project (item_id).
     */
    public function getProductMaterialsAttribute()
    {
        if ($this->item_type === 'project' && $this->item_id) {
            $productMaterials = [];

            // Determine dispatch type from the warranty's original dispatch
            $originalDispatch = $this->dispatch;
            $dispatchType = $originalDispatch ? $originalDispatch->dispatch_type : null;

            // Get ONLY APPROVED dispatches for this project with the same dispatch_type
            $projectDispatches = Dispatch::where('project_id', $this->item_id)
                ->whereIn('status', ['approved', 'completed']);
            
            // Only filter by dispatch_type if we can determine it from the original dispatch
            if ($dispatchType) {
                $projectDispatches = $projectDispatches->where('dispatch_type', $dispatchType);
            }
            
            $projectDispatches = $projectDispatches->get();

            foreach ($projectDispatches as $dispatch) {
                // Only include contract items
                $contractItems = $dispatch->items()->where('category', 'contract')->where('item_type', 'product')->get();

                foreach ($contractItems as $dispatchItem) {
                    $product = Product::find($dispatchItem->item_id);
                    if ($product) {
                        // Get assembly materials for this product using serial numbers
                        $serialNumbers = $dispatchItem->serial_numbers ? array_unique($dispatchItem->serial_numbers) : [];

                        foreach ($serialNumbers as $serialNumber) {
                            // Find assembly that created this serial number
                            $assemblyMaterial = AssemblyMaterial::whereHas('assembly.products', function ($query) use ($serialNumber) {
                                $query->whereRaw("FIND_IN_SET(?, serials) > 0", [$serialNumber]);
                            })->where('target_product_id', $product->id)->with(['material', 'assembly'])->get();

                            if ($assemblyMaterial->count() > 0) {
                                $materials = [];
                                foreach ($assemblyMaterial as $am) {
                                    if ($am->material) {
                                        // Get latest material serial from replacement history
                                        $originalSerial = $am->serial ?? 'N/A';
                                        
                                        // Handle comma-separated serials
                                        if (strpos($originalSerial, ',') !== false) {
                                            $serialParts = array_map('trim', explode(',', $originalSerial));
                                            $updatedParts = [];
                                            
                                            foreach ($serialParts as $part) {
                                                $updatedParts[] = $this->getLatestMaterialSerial(
                                                    $product->code,
                                                    $am->material->code,
                                                    $part
                                                );
                                            }
                                            
                                            $latestSerial = implode(',', $updatedParts);
                                        } else {
                                            $latestSerial = $this->getLatestMaterialSerial(
                                                $product->code,
                                                $am->material->code,
                                                $originalSerial
                                            );
                                        }
                                        
                                        $materials[] = [
                                            'code' => $am->material->code,
                                            'name' => $am->material->name,
                                            'quantity' => $am->quantity,
                                            'assembly_code' => $am->assembly->code ?? 'N/A',
                                            'serial' => $latestSerial
                                        ];
                                    }
                                }

                                $productMaterials[] = [
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'serial_number' => $serialNumber,
                                    'materials' => collect($materials)->unique('code')->values()->all()
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
                                    'materials' => collect($materials)->unique('code')->values()->all()
                                ];
                            }
                        }
                    }
                }
            }

            // Deduplicate entries by product_code and serial_number
            $deduped = [];
            foreach ($productMaterials as $entry) {
                $key = $entry['product_code'] . '|' . $entry['serial_number'];
                if (!isset($deduped[$key])) {
                    $deduped[$key] = $entry;
                } else {
                    // Merge materials, avoiding duplicates by material code
                    $existingMaterials = collect($deduped[$key]['materials'])->keyBy('code');
                    foreach ($entry['materials'] as $mat) {
                        if (!$existingMaterials->has($mat['code'])) {
                            $existingMaterials->put($mat['code'], $mat);
                        }
                    }
                    $deduped[$key]['materials'] = $existingMaterials->values()->all();
                }
            }
            return array_values($deduped);
        }
        return [];
    }

    /**
     * Get all products in this warranty (for project-type warranties)
     */
    public function getWarrantyProductsAttribute()
    {
        // For project-type warranties, get all products from project dispatches
        if ($this->item_type === 'project' && $this->item_id) {
            $products = [];
            $groupedProducts = [];

            // Get all dispatches for this project
            $projectDispatches = Dispatch::where('project_id', $this->item_id)
                ->whereIn('status', ['approved', 'completed'])
                ->get();

            foreach ($projectDispatches as $dispatch) {
                // Include all eligible items (exclude backup)
                $eligibleItems = $dispatch->items()->where('category', '!=', 'backup')->get();

                foreach ($eligibleItems as $dispatchItem) {
                    if ($dispatchItem->item_type === 'product') {
                        $product = $dispatchItem->product;
                        if (!$product) continue;

                        // Group by product code
                        if (!isset($groupedProducts[$product->code])) {
                            $groupedProducts[$product->code] = [
                                'product_code' => $product->code,
                                'product_name' => $product->name,
                                'quantity' => 0,
                                'serial_numbers' => [],
                            ];
                        }

                        // Update quantity
                        $groupedProducts[$product->code]['quantity'] += $dispatchItem->quantity;

                        // Add serial numbers if available
                        if (!empty($dispatchItem->serial_numbers)) {
                            $groupedProducts[$product->code]['serial_numbers'] = array_merge(
                                $groupedProducts[$product->code]['serial_numbers'],
                                $dispatchItem->serial_numbers
                            );
                        }
                    } elseif ($dispatchItem->item_type === 'good') {
                        $good = $dispatchItem->good;
                        if (!$good) continue;

                        // Group by good code
                        if (!isset($groupedProducts[$good->code])) {
                            $groupedProducts[$good->code] = [
                                'product_code' => $good->code,
                                'product_name' => $good->name,
                                'quantity' => 0,
                                'serial_numbers' => [],
                                'type' => 'good'
                            ];
                        }

                        // Update quantity
                        $groupedProducts[$good->code]['quantity'] += $dispatchItem->quantity;

                        // Add serial numbers if available
                        if (!empty($dispatchItem->serial_numbers)) {
                            $groupedProducts[$good->code]['serial_numbers'] = array_merge(
                                $groupedProducts[$good->code]['serial_numbers'],
                                $dispatchItem->serial_numbers
                            );
                        }
                    }
                }
            }

            // Process grouped products
            foreach ($groupedProducts as $productCode => $productData) {
                // Loại bỏ serial trùng lặp và sắp xếp
                $productData['serial_numbers'] = array_unique($productData['serial_numbers']);
                sort($productData['serial_numbers']);
                
                // Tạo text hiển thị serial numbers
                if (!empty($productData['serial_numbers'])) {
                    $productData['serial_numbers_text'] = implode(', ', $productData['serial_numbers']);
                } else {
                    $productData['serial_numbers_text'] = 'Chưa có';
                }

                $products[] = $productData;
            }

            return $products;
        }

        // For non-project warranties, return single item
        if ($this->item_type !== 'project') {
            $item = $this->item;
            if ($item) {
                $data = [
                    'product_code' => $item->code,
                    'product_name' => $item->name,
                    'quantity' => 1,
                    'serial_numbers' => $this->serial_number ? [$this->serial_number] : [],
                    'serial_numbers_text' => $this->serial_number ?: 'Chưa có',
                ];
                
                // Add type field for goods to differentiate them from products
                if ($this->item_type === 'good') {
                    $data['type'] = 'good';
                }
                
                return [$data];
            }
        }

        return [];
    }

    /**
     * Get latest material serial from replacement history
     */
    private function getLatestMaterialSerial($deviceCode, $materialCode, $originalSerial)
    {
        // Find material replacement history for this warranty and material
        $latestReplacement = \App\Models\MaterialReplacementHistory::whereHas('repair', function ($query) {
            $query->where('warranty_code', $this->warranty_code);
        })
        ->where('device_code', $deviceCode)
        ->where('material_code', $materialCode)
        ->where(function ($query) use ($originalSerial) {
            $query->whereJsonContains('old_serials', $originalSerial)
                  ->orWhereRaw('JSON_SEARCH(old_serials, "one", ?) IS NOT NULL', [$originalSerial]);
        })
        ->orderBy('replaced_at', 'desc')
        ->first();

        if ($latestReplacement && !empty($latestReplacement->new_serials)) {
            // Find which new serial corresponds to the original serial
            $oldSerials = $latestReplacement->old_serials;
            $newSerials = $latestReplacement->new_serials;
            
            $index = array_search($originalSerial, $oldSerials);
            if ($index !== false && isset($newSerials[$index])) {
                return $newSerials[$index];
            }
        }

        return $originalSerial;
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

    /**
     * Get item name based on item type
     */
    public function getItemNameAttribute()
    {
        switch ($this->item_type) {
            case 'product':
                return $this->item?->name ?? 'Sản phẩm không xác định';
            case 'material':
                return $this->item?->name ?? 'Vật tư không xác định';
            case 'good':
                return $this->item?->name ?? 'Hàng hóa không xác định';
            case 'project':
                return $this->project_name ?? 'Dự án không xác định';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Get item code based on item type
     */
    public function getItemCodeAttribute()
    {
        switch ($this->item_type) {
            case 'product':
                return $this->item?->code ?? 'N/A';
            case 'material':
                return $this->item?->code ?? 'N/A';
            case 'good':
                return $this->item?->code ?? 'N/A';
            case 'project':
                return 'PROJECT-' . $this->item_id;
            default:
                return 'N/A';
        }
    }

    public function getStatusAttribute($value)
    {
        // Nếu warranty thuộc về một dự án, kiểm tra trạng thái bảo hành của dự án

        // Tự động cập nhật trạng thái dựa trên ngày hết hạn
        if ($this->warranty_end_date) {
            $isPast = Carbon::parse($this->warranty_end_date)->endOfDay()->isPast();
            if ($isPast) {
                if ($value !== 'expired') {
                    // Chuyển sang hết hạn
                    $this->setAttribute('status', 'expired');
                    $this->saveQuietly();
                }
                return 'expired';
            } else {
                if ($value !== 'active') {
                    // Kích hoạt lại nếu trước đó là expired
                    $this->setAttribute('status', 'active');
                    $this->saveQuietly();
                }
                return 'active';
            }
        }
        
        return $value;
    }
}

