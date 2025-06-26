<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\Material;
use App\Models\WarehouseMaterial;
use App\Models\AssemblyMaterial;
use App\Models\MaterialReplacementHistory;
use App\Models\DispatchItem;
use App\Models\DamagedMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Models\Dispatch;
use App\Models\Serial;
use App\Models\Assembly;
use App\Models\User;
use App\Helpers\ChangeLogHelper;

class RepairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Repair::with(['warranty', 'repairItems', 'technician', 'createdBy', 'warehouse']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('repair_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('warranty_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('technician', function ($techQuery) use ($searchTerm) {
                        $techQuery->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('warranty', function ($warrantyQuery) use ($searchTerm) {
                        $warrantyQuery->where('customer_name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply repair type filter
        if ($request->filled('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        // Apply warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('repair_date', '<=', $request->date_to);
        }

        $repairs = $query->orderBy('repair_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('warranties.repair_list', compact('repairs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warranties.repair');
    }

    /**
     * API: Search warranty by code or serial number
     */
    public function searchWarranty(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');

        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành hoặc serial number'
            ]);
        }

        try {
            // Tìm bảo hành theo mã bảo hành hoặc serial number
            $warranty = Warranty::where('warranty_code', $warrantyCode)
                ->orWhereRaw('FIND_IN_SET(?, REPLACE(serial_number, " ", ""))', [$warrantyCode])
                ->orWhere('serial_number', 'LIKE', "%{$warrantyCode}%")
                ->with(['dispatch', 'dispatch.project'])
                ->first();

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
                ]);
            }

            // Kiểm tra trạng thái bảo hành
            if ($warranty->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bảo hành không còn hiệu lực. Trạng thái: ' . $warranty->status_label
                ]);
            }

            // Lấy danh sách thiết bị trong bảo hành
            $devices = [];
            $warrantyProducts = $warranty->warrantyProducts ?? [];

            foreach ($warrantyProducts as $product) {
                // Với logic mới, product đã được gom nhóm theo mã
                $mainSerial = '';
                if (!empty($product['serial_numbers']) && is_array($product['serial_numbers'])) {
                    $mainSerial = $product['serial_numbers'][0]; // Lấy serial đầu tiên để tạo ID
                } elseif (!empty($product['serial_numbers_text']) && $product['serial_numbers_text'] !== 'Chưa có') {
                    $parts = explode(',', $product['serial_numbers_text']);
                    $mainSerial = trim($parts[0]); // Lấy serial đầu tiên
                }

                $devices[] = [
                    'id' => $product['product_code'] . '_' . $mainSerial . '_' . microtime(true) . '_' . uniqid(), // Tạo ID unique với microtime và uniqid
                    'code' => $product['product_code'],
                    'name' => $product['product_name'],
                    'quantity' => $product['quantity'],
                    'serial' => $mainSerial, // Serial đầu tiên để tương thích ngược
                    'serial_numbers' => $product['serial_numbers'] ?? [],
                    'serial_numbers_text' => $product['serial_numbers_text'] ?? 'Chưa có',
                    'status' => 'active'
                ];
            }

            // Lấy lịch sử sửa chữa
            $repairHistory = [];
            $existingRepairs = Repair::where('warranty_code', $warranty->warranty_code)
                ->with(['technician', 'warehouse'])
                ->orderBy('repair_date', 'desc')
                ->get();

            foreach ($existingRepairs as $repair) {
                $repairHistory[] = [
                    'date' => $repair->repair_date->format('d/m/Y'),
                    'type' => $this->getRepairTypeLabel($repair->repair_type),
                    'description' => $repair->repair_description,
                    'technician' => $repair->technician->name ?? 'N/A'
                ];
            }

            return response()->json([
                'success' => true,
                'warranty' => [
                    'warranty_code' => $warranty->warranty_code,
                    'customer_name' => $warranty->customer_name,
                    'project_name' => $warranty->project_name,
                    'warranty_start_date' => $warranty->warranty_start_date->format('d/m/Y'),
                    'warranty_end_date' => $warranty->warranty_end_date->format('d/m/Y'),
                    'devices' => $devices,
                    'repair_history' => $repairHistory
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching warranty: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm bảo hành'
            ]);
        }
    }

    /**
     * API: Get device materials for repair
     */
    public function getDeviceMaterials(Request $request)
    {
        $deviceId = $request->get('device_id');
        $warrantyCode = $request->get('warranty_code');

        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin device_id'
            ]);
        }

        try {
            // Parse device code from device_id (format: CODE_timestamp_random)
            $deviceCode = explode('_', $deviceId)[0] ?? $deviceId;
            $deviceSerial = '';

            // Extract device serial from device_id if available
            $deviceIdParts = explode('_', $deviceId);
            if (count($deviceIdParts) >= 2) {
                $deviceSerial = $deviceIdParts[1];
            }

            // Tìm product theo code
            $product = Product::where('code', $deviceCode)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin sản phẩm'
                ]);
            }

            // Lấy danh sách vật tư từ assembly_materials (nguồn chính xác nhất)
            $materials = $this->getDeviceMaterialsFromAssembly($product);

            // Cập nhật serial từ replacement history nếu có warranty_code
            if ($warrantyCode && !empty($materials)) {
                $materials = $this->updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode);
            }

            return response()->json([
                'success' => true,
                'materials' => $materials
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting device materials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách vật tư'
            ]);
        }
    }

    /**
     * Get device materials from warranty and replacement history
     */
    private function getDeviceMaterialsFromWarranty($deviceCode, $deviceSerial, $warrantyCode = null)
    {
        $materials = [];

        if (!$warrantyCode) {
            return $materials;
        }

        try {
            // Find warranty
            $warranty = Warranty::where('warranty_code', $warrantyCode)->first();
            if (!$warranty) {
                return $materials;
            }

            // Get product materials from warranty
            $productMaterials = $warranty->product_materials ?? [];

            foreach ($productMaterials as $productMaterial) {
                // Find matching product and serial
                if ($productMaterial['product_code'] === $deviceCode) {
                    // Check if device serial matches (if specified)
                    if (!$deviceSerial || $productMaterial['serial_number'] === $deviceSerial || $productMaterial['serial_number'] === 'N/A') {

                        // Get materials for this product
                        foreach ($productMaterial['materials'] as $material) {
                            // Check for latest replacement
                            $currentSerial = $this->getLatestMaterialSerial(
                                $deviceCode,
                                $material['code'],
                                $material['serial'],
                                $warrantyCode
                            );

                            $materials[] = [
                                'id' => null, // Will be resolved by material code
                                'code' => $material['code'],
                                'name' => $material['name'],
                                'quantity' => $material['quantity'],
                                'serial' => $currentSerial,
                                'current_serials' => [$currentSerial],
                                'status' => 'active'
                            ];
                        }
                        break;
                    }
                }
            }

            Log::info("Found " . count($materials) . " materials from warranty for device {$deviceCode}");
            return $materials;
        } catch (\Exception $e) {
            Log::error('Error getting materials from warranty: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest material serial after replacements
     */
    private function getLatestMaterialSerial($deviceCode, $materialCode, $originalSerial, $warrantyCode)
    {
        // Find material replacement history for this device and material
        $latestReplacement = MaterialReplacementHistory::whereHas('repair', function ($query) use ($warrantyCode) {
            $query->where('warranty_code', $warrantyCode);
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
     * Get device materials from assembly (main source)
     */
    private function getDeviceMaterialsFromAssembly($product)
    {
        $materials = [];
        $assemblyMaterials = \App\Models\AssemblyMaterial::where('target_product_id', $product->id)
            ->with(['material', 'assembly'])
            ->get();

        foreach ($assemblyMaterials as $am) {
            if ($am->material) {
                $materials[] = [
                    'id' => $am->material->id,
                    'code' => $am->material->code,
                    'name' => $am->material->name,
                    'quantity' => $am->quantity,
                    'serial' => $am->serial ?? '',
                    'current_serials' => [$am->serial ?? ''],
                    'status' => 'active'
                ];
            }
        }

        Log::info("Found " . count($materials) . " materials from assembly for product {$product->code}");
        return $materials;
    }

    /**
     * Update materials serials from replacement history
     */
    private function updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode)
    {
        foreach ($materials as &$material) {
            $originalSerial = $material['serial'];

            // Handle comma-separated serials (e.g., "111,222" -> ["111", "222"])
            $originalSerials = array_map('trim', explode(',', $originalSerial));
            $updatedSerials = [];
            $hasChanges = false;

            foreach ($originalSerials as $singleSerial) {
                $currentSerial = $this->getLatestMaterialSerial(
                    $deviceCode,
                    $material['code'],
                    $singleSerial,
                    $warrantyCode
                );

                $updatedSerials[] = $currentSerial;

                if ($currentSerial !== $singleSerial) {
                    $hasChanges = true;
                    Log::info("Updated material {$material['code']} serial from {$singleSerial} to {$currentSerial}");
                }
            }

            // Update material serial if there were changes
            if ($hasChanges) {
                $material['serial'] = implode(',', $updatedSerials);
                $material['current_serials'] = $updatedSerials;
                Log::info("Final material {$material['code']} serial: {$material['serial']}");
            }
        }

        return $materials;
    }

    /**
     * API: Get available serials in warehouse
     */
    public function getAvailableSerials(Request $request)
    {
        $materialCode = $request->get('material_code');
        $warehouseId = $request->get('warehouse_id');

        if (!$materialCode || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin material_code hoặc warehouse_id'
            ]);
        }

        try {
            // Tìm material theo code
            $material = \App\Models\Material::where('code', $materialCode)->first();

            if (!$material) {
                Log::error("Material not found with code: {$materialCode}");
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vật tư'
                ]);
            }

            Log::info("Found material: {$material->name} (ID: {$material->id})");

            // Lấy danh sách serial có sẵn trong kho
            $serials = [];

            // Tìm trong bảng serials
            $serialRecords = \App\Models\Serial::where('warehouse_id', $warehouseId)
                ->where('type', 'material')
                ->where('status', 'active')
                ->where('product_id', $material->id) // product_id trong serials table chính là material_id khi type = 'material'
                ->get();

            Log::info("Found {$serialRecords->count()} serials for material {$materialCode} in warehouse {$warehouseId}");

            // Lấy danh sách serial đã được sử dụng trong thay thế
            $usedSerials = \App\Models\MaterialReplacementHistory::where('material_code', $materialCode)
                ->get()
                ->pluck('new_serials')
                ->flatten()
                ->unique()
                ->toArray();

            Log::info("Found used serials for material {$materialCode}: " . json_encode($usedSerials));

            foreach ($serialRecords as $serial) {
                // Chỉ thêm serial chưa được sử dụng
                if (!in_array($serial->serial_number, $usedSerials)) {
                    $serials[] = [
                        'serial' => $serial->serial_number,
                        'status' => 'available'
                    ];
                } else {
                    Log::info("Serial {$serial->serial_number} đã được sử dụng, bỏ qua");
                }
            }

            return response()->json([
                'success' => true,
                'serials' => $serials
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available serials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách serial'
            ]);
        }
    }

    /**
     * API: Replace material in repair
     */
    public function replaceMaterial(Request $request)
    {
        $request->validate([
            'materialCode' => 'required|string',
            'materialName' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'oldSerials' => 'required|array|min:1',
            'newSerials' => 'required|array|min:1',
            'sourceWarehouse' => 'required|integer',
            'targetWarehouse' => 'required|integer',
            'deviceCode' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Validate quantities match
            if (
                count($request->oldSerials) !== $request->quantity ||
                count($request->newSerials) !== $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng serial không khớp với số lượng thay thế'
                ]);
            }

            // Find material
            $material = \App\Models\Material::where('code', $request->materialCode)->first();
            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vật tư'
                ]);
            }

            // TODO: Implement actual material replacement logic
            // 1. Move old materials to source warehouse
            // 2. Move new materials from target warehouse
            // 3. Update assembly_materials table
            // 4. Create warehouse transfer records
            // 5. Log the replacement

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thay thế vật tư thành công',
                'updated_material' => [
                    'materialCode' => $request->materialCode,
                    'materialName' => $request->materialName,
                    'quantity' => $request->quantity,
                    'newSerials' => $request->newSerials
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error replacing material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thay thế vật tư: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get repair type label
     */
    private function getRepairTypeLabel($type)
    {
        $labels = [
            'maintenance' => 'Bảo trì',
            'repair' => 'Sửa chữa',
            'replacement' => 'Thay thế',
            'upgrade' => 'Nâng cấp',
            'other' => 'Khác'
        ];

        return $labels[$type] ?? 'Không xác định';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warranty_code' => 'nullable|string|max:255',
            'repair_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
            'repair_date' => 'required|date',
            'technician_id' => 'required|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'repair_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'selected_devices' => 'nullable|array',
        ]);

        // Custom validation: phải có ít nhất một thiết bị được chọn hoặc từ chối
        if (empty($request->selected_devices) && empty($request->rejected_devices)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['selected_devices' => 'Vui lòng chọn hoặc từ chối ít nhất một thiết bị.']);
        }

        try {
            DB::beginTransaction();

            // Debug: Log all files in request
            Log::info("🔍 All files in request:", $request->allFiles());
            Log::info("🔍 Request input keys:", array_keys($request->all()));

            // Find warranty if warranty_code is provided
            $warranty = null;
            if ($request->warranty_code) {
                $warranty = Warranty::where('warranty_code', $request->warranty_code)->first();
            }

            // Handle file uploads
            $repairPhotos = [];
            if ($request->hasFile('repair_photos')) {
                foreach ($request->file('repair_photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('repairs/photos', $filename, 'public');
                    $repairPhotos[] = $path;
                }
            }

            // Determine initial status based on whether there are rejections or replacements
            $hasRejections = $request->has('rejected_devices') && !empty($request->rejected_devices);
            $hasReplacements = $request->has('material_replacements') && !empty($request->material_replacements);
            $initialStatus = ($hasRejections || $hasReplacements) ? 'completed' : 'in_progress';

            // Create repair record
            $repair = Repair::create([
                'repair_code' => Repair::generateRepairCode(),
                'warranty_code' => $request->warranty_code,
                'warranty_id' => $warranty ? $warranty->id : null,
                'repair_type' => $request->repair_type,
                'repair_date' => $request->repair_date,
                'technician_id' => $request->technician_id,
                'warehouse_id' => $request->warehouse_id ?? 1,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $initialStatus,
                'created_by' => Auth::id() ?? 1,
            ]);

            // Process selected devices
            if ($request->selected_devices && !empty($request->selected_devices)) {
                Log::info('🔍 Raw selected_devices from request:', $request->selected_devices);
                Log::info('🔍 Unique selected_devices:', array_unique($request->selected_devices));

                // Remove duplicates to prevent duplicate repair items
                $uniqueDeviceIds = array_unique($request->selected_devices);

                foreach ($uniqueDeviceIds as $deviceId) {
                    // Debug logging
                    Log::info('Processing device ID: ' . $deviceId);

                    // Escape device ID to match frontend format
                    $deviceKey = str_replace(['.', '[', ']'], ['_DOT_', '_LB_', '_RB_'], $deviceId);
                    Log::info('Escaped device key: ' . $deviceKey);

                    // Check all possible input formats with escaped key
                    $deviceCode = $request->input("device_code.{$deviceKey}") ??
                        $request->input("device_code[{$deviceKey}]") ??
                        $request->input("device_code.{$deviceId}") ??
                        $request->input("device_code[{$deviceId}]") ?? '';
                    $deviceName = $request->input("device_name.{$deviceKey}") ??
                        $request->input("device_name[{$deviceKey}]") ??
                        $request->input("device_name.{$deviceId}") ??
                        $request->input("device_name[{$deviceId}]") ?? '';
                    $deviceSerial = $request->input("device_serial.{$deviceKey}") ??
                        $request->input("device_serial[{$deviceKey}]") ??
                        $request->input("device_serial.{$deviceId}") ??
                        $request->input("device_serial[{$deviceId}]") ?? '';
                    $deviceQuantity = $request->input("device_quantity.{$deviceKey}") ??
                        $request->input("device_quantity[{$deviceKey}]") ??
                        $request->input("device_quantity.{$deviceId}") ??
                        $request->input("device_quantity[{$deviceId}]") ?? 1;
                    $deviceNotes = $request->input("device_notes.{$deviceKey}") ??
                        $request->input("device_notes[{$deviceKey}]") ??
                        $request->input("device_notes.{$deviceId}") ??
                        $request->input("device_notes[{$deviceId}]") ?? '';

                    Log::info('Device data from request (dot notation):', [
                        'device_code' => $request->input("device_code.{$deviceId}", ''),
                        'device_name' => $request->input("device_name.{$deviceId}", ''),
                        'device_serial' => $request->input("device_serial.{$deviceId}", ''),
                        'device_quantity' => $request->input("device_quantity.{$deviceId}", 1),
                        'device_notes' => $request->input("device_notes.{$deviceId}", ''),
                    ]);

                    Log::info('Device data from request (bracket notation):', [
                        'device_code' => $request->input("device_code[{$deviceId}]", ''),
                        'device_name' => $request->input("device_name[{$deviceId}]", ''),
                        'device_serial' => $request->input("device_serial[{$deviceId}]", ''),
                        'device_quantity' => $request->input("device_quantity[{$deviceId}]", 1),
                        'device_notes' => $request->input("device_notes[{$deviceId}]", ''),
                    ]);

                    Log::info('Final device data to save:', [
                        'device_code' => $deviceCode,
                        'device_name' => $deviceName,
                        'device_serial' => $deviceSerial,
                        'device_quantity' => $deviceQuantity,
                        'device_notes' => $deviceNotes,
                    ]);

                    // Handle device images
                    $deviceImages = [];
                    Log::info("🔍 Checking for device images for device: {$deviceId}");

                    // Kiểm tra xem có files trong device_images array không (với escaped key)
                    $deviceImagesArray = $request->file('device_images', []);
                    $hasDeviceImages = (isset($deviceImagesArray[$deviceKey]) && !empty($deviceImagesArray[$deviceKey])) ||
                        (isset($deviceImagesArray[$deviceId]) && !empty($deviceImagesArray[$deviceId]));

                    // Ưu tiên escaped key, fallback về original key
                    $deviceImageFiles = $deviceImagesArray[$deviceKey] ?? $deviceImagesArray[$deviceId] ?? [];

                    Log::info("🔍 Device images array keys: " . json_encode(array_keys($deviceImagesArray)));
                    Log::info("🔍 Has device images for {$deviceId}: " . ($hasDeviceImages ? 'YES' : 'NO'));

                    if ($hasDeviceImages) {
                        $files = $deviceImageFiles;
                        Log::info("🔍 Found " . count($files) . " files for device {$deviceId}");

                        foreach ($files as $index => $image) {
                            if ($image->isValid()) {
                                Log::info("🔍 Processing image {$index}: " . $image->getClientOriginalName());
                                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                                $path = $image->storeAs('repairs/devices', $filename, 'public');
                                $deviceImages[] = $path;
                                Log::info("✅ Saved device image to: {$path}");
                            } else {
                                Log::warning("⚠️ Invalid image file for device {$deviceId} at index {$index}");
                            }
                        }
                    } else {
                        Log::info("❌ No device images found for device: {$deviceId}");
                    }

                    // Lấy thông tin device_parts nếu có
                    $deviceParts = [];
                    if ($request->has("device_parts.{$deviceId}")) {
                        $partsData = $request->input("device_parts.{$deviceId}");
                        if (is_string($partsData)) {
                            $deviceParts = json_decode($partsData, true) ?: [];
                        } elseif (is_array($partsData)) {
                            $deviceParts = $partsData;
                        }
                    }

                    Log::info('Final device_serial to save: "' . $deviceSerial . '"');

                    $repairItem = RepairItem::create([
                        'repair_id' => $repair->id,
                        'device_code' => $deviceCode,
                        'device_name' => $deviceName,
                        'device_serial' => $deviceSerial,
                        'device_quantity' => $deviceQuantity,
                        'device_status' => 'selected',
                        'device_notes' => $deviceNotes,
                        'device_images' => $deviceImages,
                        'device_parts' => $deviceParts,
                    ]);

                    Log::info('✅ Created RepairItem:', [
                        'id' => $repairItem->id,
                        'device_code' => $repairItem->device_code,
                        'device_name' => $repairItem->device_name,
                        'device_serial' => $repairItem->device_serial,
                        'device_quantity' => $repairItem->device_quantity,
                        'device_notes' => $repairItem->device_notes,
                        'device_images_count' => count($repairItem->device_images ?? []),
                    ]);
                }
            }

            // Process rejected devices
            if ($request->has('rejected_devices')) {
                $rejectedDevices = json_decode($request->rejected_devices, true);
                if (is_array($rejectedDevices)) {
                    foreach ($rejectedDevices as $rejectedDevice) {
                        $repairItem = RepairItem::create([
                            'repair_id' => $repair->id,
                            'device_code' => $rejectedDevice['code'] ?? '',
                            'device_name' => $rejectedDevice['name'] ?? '',
                            'device_serial' => '',
                            'device_quantity' => $rejectedDevice['quantity'] ?? 1,
                            'device_status' => 'rejected',
                            'device_notes' => $rejectedDevice['reason'] ?? '',
                            'rejected_reason' => $rejectedDevice['reason'] ?? '',
                            'rejected_warehouse_id' => $rejectedDevice['warehouse_id'] ?? null,
                            'rejected_at' => $rejectedDevice['rejected_at'] ?? now(),
                        ]);

                        // Lưu nhật ký thay đổi cho thành phẩm bị từ chối
                        try {
                            ChangeLogHelper::suaChua(
                                $rejectedDevice['code'] ?? '',
                                $rejectedDevice['name'] ?? '',
                                $rejectedDevice['quantity'] ?? 1,
                                $repair->repair_code,
                                'Thu hồi', // Mô tả cố định theo yêu cầu
                                [
                                    'repair_id' => $repair->id,
                                    'rejected_quantity' => $rejectedDevice['quantity'] ?? 1,
                                    'total_quantity' => $rejectedDevice['total_quantity'] ?? 1,
                                    'rejected_reason' => $rejectedDevice['reason'] ?? '',
                                    'rejected_warehouse_id' => $rejectedDevice['warehouse_id'] ?? null,
                                    'warranty_code' => $repair->warranty_code,
                                    'action_type' => 'product_rejection_on_create'
                                ],
                                $rejectedDevice['reason'] ?? ''
                            );

                            Log::info("Created change log for rejected product: {$rejectedDevice['code']} in repair {$repair->repair_code}");
                        } catch (\Exception $e) {
                            Log::error("Failed to create change log for rejected product: " . $e->getMessage());
                        }
                    }
                }
            }

            // Process material replacements (if any were made during the repair process)
            if ($request->has('material_replacements')) {
                $materialReplacements = json_decode($request->material_replacements, true);
                if (is_array($materialReplacements)) {
                    foreach ($materialReplacements as $replacement) {
                        // Log material replacement history
                        DB::table('material_replacement_history')->insert([
                            'repair_id' => $repair->id,
                            'device_code' => $replacement['device_code'],
                            'material_code' => $replacement['material_code'],
                            'material_name' => $replacement['material_name'],
                            'old_serials' => json_encode($replacement['old_serials']),
                            'new_serials' => json_encode($replacement['new_serials']),
                            'quantity' => $replacement['quantity'],
                            'source_warehouse_id' => $replacement['source_warehouse_id'],
                            'target_warehouse_id' => $replacement['target_warehouse_id'],
                            'notes' => $replacement['notes'] ?? '',
                            'replaced_by' => Auth::id() ?? 1,
                            'replaced_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update warehouse materials inventory
                        // Move old materials to source warehouse
                        foreach ($replacement['old_serials'] as $oldSerial) {
                            $this->updateWarehouseMaterial(
                                $replacement['material_code'],
                                $oldSerial,
                                $replacement['source_warehouse_id'],
                                'add'
                            );
                        }

                        // Remove new materials from target warehouse
                        foreach ($replacement['new_serials'] as $newSerial) {
                            $this->updateWarehouseMaterial(
                                $replacement['material_code'],
                                $newSerial,
                                $replacement['target_warehouse_id'],
                                'remove'
                            );
                        }

                        // Lưu nhật ký thay đổi cho thay thế vật tư
                        try {
                            ChangeLogHelper::suaChua(
                                $replacement['material_code'],
                                $replacement['material_name'],
                                $replacement['quantity'],
                                $repair->repair_code,
                                'Thay thế', // Mô tả cố định theo yêu cầu
                                [
                                    'repair_id' => $repair->id,
                                    'device_code' => $replacement['device_code'],
                                    'old_serials' => $replacement['old_serials'],
                                    'new_serials' => $replacement['new_serials'],
                                    'source_warehouse_id' => $replacement['source_warehouse_id'],
                                    'target_warehouse_id' => $replacement['target_warehouse_id'],
                                    'warranty_code' => $repair->warranty_code,
                                    'action_type' => 'material_replacement_on_create'
                                ],
                                $replacement['notes'] ?? ''
                            );

                            Log::info("Created change log for material replacement: {$replacement['material_code']} in repair {$repair->repair_code}");
                        } catch (\Exception $e) {
                            Log::error("Failed to create change log for material replacement: " . $e->getMessage());
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('repairs.index')
                ->with('success', 'Phiếu sửa chữa đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating repair: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Update warehouse material inventory
     */
    private function updateWarehouseMaterial($materialCode, $serial, $warehouseId, $action = 'add')
    {
        try {
            $material = Material::where('code', $materialCode)->first();
            if (!$material) {
                Log::warning("Material not found: {$materialCode}");
                return;
            }

            if ($action === 'add') {
                // Add material to warehouse
                WarehouseMaterial::updateOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'material_id' => $material->id,
                        'serial_number' => $serial,
                    ],
                    [
                        'quantity' => 1,
                        'status' => 'available',
                        'updated_at' => now(),
                    ]
                );
            } elseif ($action === 'remove') {
                // Remove material from warehouse
                WarehouseMaterial::where('warehouse_id', $warehouseId)
                    ->where('material_id', $material->id)
                    ->where('serial_number', $serial)
                    ->delete();
            }

            Log::info("Updated warehouse material: {$materialCode} - {$serial} - {$action}");
        } catch (\Exception $e) {
            Log::error('Error updating warehouse material: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Repair $repair)
    {
        $repair->load([
            'warranty',
            'repairItems.rejectedWarehouse',
            'technician',
            'createdBy',
            'warehouse',
            'materialReplacements.sourceWarehouse',
            'materialReplacements.targetWarehouse',
            'materialReplacements.replacedBy'
        ]);
        return view('warranties.repair_detail', compact('repair'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repair $repair)
    {
        $repair->load(['warranty', 'repairItems', 'materialReplacements', 'damagedMaterials']);

        // Determine which devices can be edited
        $editableDevices = $this->getEditableDevices($repair);

        return view('warranties.repair_edit', compact('repair', 'editableDevices'));
    }

    /**
     * Get devices that can be edited (not rejected or replaced)
     */
    private function getEditableDevices($repair)
    {
        $editableDevices = [];

        foreach ($repair->repairItems as $item) {
            $canEdit = true;

            // Check if device is rejected
            if ($item->device_status === 'rejected') {
                $canEdit = false;
            }

            // Check if device has material replacements
            if ($canEdit) {
                $hasReplacements = $repair->materialReplacements
                    ->where('device_code', $item->device_code)
                    ->count() > 0;

                if ($hasReplacements) {
                    $canEdit = false;
                }
            }

            $editableDevices[$item->device_code] = $canEdit;
        }

        return $editableDevices;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repair $repair)
    {
        $request->validate([
            'repair_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
            'repair_date' => 'required|date',
            'technician_id' => 'required|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'repair_items.*.device_status' => 'nullable|in:processing,selected,rejected',
            'repair_items.*.device_notes' => 'nullable|string',
            'material_replacements' => 'nullable|string',
            'damaged_materials' => 'nullable|string',
            'photos_to_delete' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle photo deletion first
            $repairPhotos = $repair->repair_photos ?? [];
            if ($request->has('photos_to_delete') && !empty($request->photos_to_delete)) {
                $photosToDelete = json_decode($request->photos_to_delete, true);
                if ($photosToDelete && is_array($photosToDelete)) {
                    foreach ($photosToDelete as $photoPath) {
                        // Remove from array
                        $repairPhotos = array_filter($repairPhotos, function ($photo) use ($photoPath) {
                            return $photo !== $photoPath;
                        });

                        // Delete physical file
                        if (Storage::disk('public')->exists($photoPath)) {
                            Storage::disk('public')->delete($photoPath);
                            Log::info("Deleted photo: {$photoPath}");
                        }
                    }
                    // Re-index array to remove gaps
                    $repairPhotos = array_values($repairPhotos);
                }
            }

            // Handle file uploads for repair photos
            if ($request->hasFile('repair_photos')) {
                foreach ($request->file('repair_photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('repairs/photos', $filename, 'public');
                    $repairPhotos[] = $path;
                }
            }

            // Determine status based on device actions
            $newStatus = $this->determineRepairStatus($repair, $request);

            // Update repair record
            $repair->update([
                'repair_type' => $request->repair_type,
                'repair_date' => $request->repair_date,
                'technician_id' => $request->technician_id,
                'warehouse_id' => $request->warehouse_id,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $newStatus,
            ]);

            // Update device items if provided
            if ($request->has('repair_items')) {
                $this->updateRepairItems($repair, $request->repair_items);
            }

            // Handle material replacements if provided
            if ($request->has('material_replacements') && !empty($request->material_replacements)) {
                $replacements = json_decode($request->material_replacements, true);
                if ($replacements && is_array($replacements)) {
                    $this->processMaterialReplacements($repair, $replacements);
                }
            }

            // Handle damaged materials if provided
            if ($request->has('damaged_materials') && !empty($request->damaged_materials)) {
                $damagedMaterials = json_decode($request->damaged_materials, true);
                if ($damagedMaterials && is_array($damagedMaterials)) {
                    $this->processDamagedMaterials($repair, $damagedMaterials);
                }
            }

            // Update warranty serial numbers if there were material replacements
            $this->updateWarrantySerials($repair);

            DB::commit();

            return redirect()->route('repairs.show', $repair->id)
                ->with('success', 'Phiếu sửa chữa đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating repair: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Determine repair status based on device actions
     */
    private function determineRepairStatus($repair, $request)
    {
        // If user explicitly sets status, use it
        if ($request->has('status') && $request->status !== 'auto') {
            return $request->status;
        }

        // Auto-determine status based on device rejections and material replacements
        $hasProcessedActions = false;

        // Check if any device has been rejected (not just selected)
        if ($request->has('repair_items')) {
            foreach ($request->repair_items as $item) {
                if (isset($item['device_status']) && $item['device_status'] === 'rejected') {
                    $hasProcessedActions = true;
                    break;
                }
            }
        }

        // Check if any material replacements were made
        if (!$hasProcessedActions && $request->has('material_replacements') && !empty($request->material_replacements)) {
            $hasProcessedActions = true;
        }

        // Check existing repair items for rejected devices
        if (!$hasProcessedActions) {
            $repair->load('repairItems');
            foreach ($repair->repairItems as $item) {
                if ($item->device_status === 'rejected') {
                    $hasProcessedActions = true;
                    break;
                }
            }
        }

        // Check existing material replacements
        if (!$hasProcessedActions) {
            $repair->load('materialReplacements');
            if ($repair->materialReplacements->count() > 0) {
                $hasProcessedActions = true;
            }
        }

        // If there are processed actions (rejection or replacement), mark as completed, otherwise in_progress
        return $hasProcessedActions ? 'completed' : 'in_progress';
    }

    /**
     * Update repair items (devices)
     */
    private function updateRepairItems($repair, $repairItems)
    {
        foreach ($repairItems as $itemData) {
            if (isset($itemData['id'])) {
                // Update existing item
                $repairItem = RepairItem::find($itemData['id']);
                if ($repairItem) {
                    $repairItem->update([
                        'device_status' => $itemData['device_status'] ?? $repairItem->device_status,
                        'device_notes' => $itemData['device_notes'] ?? $repairItem->device_notes,
                        'rejected_reason' => $itemData['rejected_reason'] ?? null,
                        'rejected_warehouse_id' => $itemData['rejected_warehouse_id'] ?? null,
                        'rejected_at' => ($itemData['device_status'] === 'rejected') ? now() : null,
                    ]);
                }
            }
        }
    }

    /**
     * Process material replacements and update serials
     */
    private function processMaterialReplacements($repair, $replacements)
    {
        foreach ($replacements as $replacement) {
            // Create replacement history record
            MaterialReplacementHistory::create([
                'repair_id' => $repair->id,
                'device_code' => $replacement['device_code'],
                'material_code' => $replacement['material_code'],
                'material_name' => $replacement['material_name'],
                'old_serials' => $replacement['old_serials'],
                'new_serials' => $replacement['new_serials'],
                'quantity' => $replacement['quantity'],
                'source_warehouse_id' => $replacement['source_warehouse_id'],
                'target_warehouse_id' => $replacement['target_warehouse_id'],
                'notes' => $replacement['notes'] ?? '',
                'replaced_by' => Auth::id(),
                'replaced_at' => now(),
            ]);

            // Update warehouse materials inventory
            // Move old materials to source warehouse
            foreach ($replacement['old_serials'] as $oldSerial) {
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $oldSerial,
                    $replacement['source_warehouse_id'],
                    'add'
                );
            }

            // Remove new materials from target warehouse
            foreach ($replacement['new_serials'] as $newSerial) {
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $newSerial,
                    $replacement['target_warehouse_id'],
                    'remove'
                );
            }

            // Lưu nhật ký thay đổi cho thay thế vật tư
            try {
                ChangeLogHelper::suaChua(
                    $replacement['material_code'],
                    $replacement['material_name'],
                    $replacement['quantity'],
                    $repair->repair_code,
                    'Thay thế', // Mô tả cố định theo yêu cầu
                    [
                        'repair_id' => $repair->id,
                        'device_code' => $replacement['device_code'],
                        'old_serials' => $replacement['old_serials'],
                        'new_serials' => $replacement['new_serials'],
                        'source_warehouse_id' => $replacement['source_warehouse_id'],
                        'target_warehouse_id' => $replacement['target_warehouse_id'],
                        'warranty_code' => $repair->warranty_code,
                        'action_type' => 'material_replacement'
                    ],
                    $replacement['notes'] ?? ''
                );

                Log::info("Created change log for material replacement: {$replacement['material_code']} in repair {$repair->repair_code}");
            } catch (\Exception $e) {
                Log::error("Failed to create change log for material replacement: " . $e->getMessage());
            }
        }
    }

    /**
     * Process damaged materials and save to database
     */
    private function processDamagedMaterials($repair, $damagedMaterials)
    {
        // Delete existing damaged materials for this repair
        \App\Models\DamagedMaterial::where('repair_id', $repair->id)->delete();

        // Create new damaged material records
        foreach ($damagedMaterials as $damaged) {
            \App\Models\DamagedMaterial::create([
                'repair_id' => $repair->id,
                'device_code' => $damaged['device_code'],
                'material_code' => $damaged['material_code'],
                'material_name' => $damaged['material_name'],
                'serial' => $damaged['serial'] ?? null,
                'damage_description' => $damaged['damage_description'] ?? '',
                'reported_by' => Auth::id(),
                'reported_at' => now(),
            ]);
        }
    }

    /**
     * Update warranty serial numbers after material replacements
     */
    private function updateWarrantySerials($repair)
    {
        if (!$repair->warranty) {
            return;
        }

        $warranty = $repair->warranty;
        $replacements = $repair->materialReplacements;

        if ($replacements->isEmpty()) {
            return;
        }

        // For project-type warranties, update dispatch items
        if ($warranty->item_type === 'project' && $warranty->item_id) {
            $this->updateProjectDispatchSerials($warranty, $replacements);
        } else {
            // For single-item warranties, update warranty serial_number field
            $this->updateSingleWarrantySerial($warranty, $replacements);
        }

        Log::info("Updated warranty serials for repair: {$repair->repair_code}");
    }

    /**
     * Update dispatch item serials for project warranties
     */
    private function updateProjectDispatchSerials($warranty, $replacements)
    {
        foreach ($replacements as $replacement) {
            // Find dispatch items for this product
            $dispatchItems = DispatchItem::whereHas('dispatch', function ($query) use ($warranty) {
                $query->where('project_id', $warranty->item_id);
            })
                ->where('item_type', 'product')
                ->whereHas('product', function ($query) use ($replacement) {
                    $query->where('code', $replacement->device_code);
                })
                ->where('category', 'contract')
                ->get();

            foreach ($dispatchItems as $dispatchItem) {
                $currentSerials = $dispatchItem->serial_numbers ?: [];

                // Replace old serials with new ones
                foreach ($replacement->old_serials as $index => $oldSerial) {
                    $serialIndex = array_search($oldSerial, $currentSerials);
                    if ($serialIndex !== false && isset($replacement->new_serials[$index])) {
                        $currentSerials[$serialIndex] = $replacement->new_serials[$index];
                    }
                }

                // Update dispatch item
                $dispatchItem->update([
                    'serial_numbers' => $currentSerials
                ]);

                Log::info("Updated dispatch item {$dispatchItem->id} serials from " . json_encode($replacement->old_serials) . " to " . json_encode($replacement->new_serials));
            }
        }
    }

    /**
     * Update single warranty serial number
     */
    private function updateSingleWarrantySerial($warranty, $replacements)
    {
        foreach ($replacements as $replacement) {
            // For single-item warranties, only update if the device code matches
            if ($warranty->item && $warranty->item->code === $replacement->device_code) {
                // If warranty has a single serial and it matches the old serial being replaced
                if (!empty($replacement->old_serials) && !empty($replacement->new_serials)) {
                    $oldSerial = $replacement->old_serials[0];
                    $newSerial = $replacement->new_serials[0];

                    if ($warranty->serial_number === $oldSerial) {
                        $warranty->update([
                            'serial_number' => $newSerial
                        ]);

                        Log::info("Updated warranty {$warranty->warranty_code} serial from {$oldSerial} to {$newSerial}");
                    }
                }
            }
        }
    }

    /**
     * API: Update device status in repair
     */
    public function updateDeviceStatus(Request $request)
    {
        $request->validate([
            'repair_id' => 'required|integer|exists:repairs,id',
            'device_id' => 'required|string',
            'status' => 'required|in:selected,rejected',
            'notes' => 'nullable|string',
            'rejected_reason' => 'nullable|string',
            'rejected_warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'rejected_quantity' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $repair = Repair::find($request->repair_id);

            // Find or create repair item for this device
            $repairItem = RepairItem::where('repair_id', $repair->id)
                ->where('device_code', $request->device_id)
                ->first();

            if (!$repairItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thiết bị trong phiếu sửa chữa'
                ]);
            }

            // Update device status
            $updateData = [
                'device_status' => $request->status,
                'device_notes' => $request->notes,
            ];

            if ($request->status === 'rejected') {
                $updateData['rejected_reason'] = $request->rejected_reason;
                $updateData['rejected_warehouse_id'] = $request->rejected_warehouse_id;
                $updateData['rejected_at'] = now();

                // Cập nhật số lượng từ chối nếu có
                if ($request->has('rejected_quantity')) {
                    $updateData['device_quantity'] = $request->rejected_quantity;
                }
            }

            $repairItem->update($updateData);

            // Lưu nhật ký thay đổi khi từ chối thành phẩm
            if ($request->status === 'rejected') {
                try {
                    $rejectedQuantity = $request->rejected_quantity ?? $repairItem->device_quantity;

                    ChangeLogHelper::suaChua(
                        $repairItem->device_code,
                        $repairItem->device_name,
                        $rejectedQuantity,
                        $repair->repair_code,
                        'Thu hồi', // Mô tả cố định theo yêu cầu
                        [
                            'repair_id' => $repair->id,
                            'device_serial' => $repairItem->device_serial,
                            'rejected_quantity' => $rejectedQuantity,
                            'rejected_reason' => $request->rejected_reason,
                            'rejected_warehouse_id' => $request->rejected_warehouse_id,
                            'warranty_code' => $repair->warranty_code,
                            'action_type' => 'product_rejection'
                        ],
                        $request->notes ?? $request->rejected_reason
                    );

                    Log::info("Created change log for product rejection: {$repairItem->device_code} in repair {$repair->repair_code}");
                } catch (\Exception $e) {
                    Log::error("Failed to create change log for product rejection: " . $e->getMessage());
                }
            }

            // Auto-update repair status based on device actions
            $this->autoUpdateRepairStatus($repair);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái thiết bị đã được cập nhật',
                'device_status' => $repairItem->device_status_label,
                'repair_status' => $repair->fresh()->status_label
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating device status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Auto-update repair status based on device rejections and material replacements
     */
    private function autoUpdateRepairStatus($repair)
    {
        $repair->load(['repairItems', 'materialReplacements']);

        $hasProcessedActions = false;

        // Check if any device has been rejected (not just selected)
        foreach ($repair->repairItems as $item) {
            if ($item->device_status === 'rejected') {
                $hasProcessedActions = true;
                break;
            }
        }

        // Check if any material replacements exist
        if (!$hasProcessedActions && $repair->materialReplacements->count() > 0) {
            $hasProcessedActions = true;
        }

        // Update repair status
        $newStatus = $hasProcessedActions ? 'completed' : 'in_progress';
        if ($repair->status !== $newStatus) {
            $repair->update(['status' => $newStatus]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repair $repair)
    {
        try {
            // Delete associated files
            if ($repair->repair_photos) {
                foreach ($repair->repair_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            // Delete device images
            foreach ($repair->repairItems as $item) {
                if ($item->device_images) {
                    foreach ($item->device_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $repair->delete();

            return redirect()->route('repairs.index')
                ->with('success', 'Phiếu sửa chữa đã được xóa thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
