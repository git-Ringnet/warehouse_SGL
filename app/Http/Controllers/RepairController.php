<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warranty_code' => 'nullable|string|max:255',
            'repair_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
            'repair_date' => 'required|date',
            'technician_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'repair_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'selected_devices' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

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

            // Create repair record
            $repair = Repair::create([
                'repair_code' => Repair::generateRepairCode(),
                'warranty_code' => $request->warranty_code,
                'warranty_id' => $warranty ? $warranty->id : null,
                'repair_type' => $request->repair_type,
                'repair_date' => $request->repair_date,
                'technician_id' => $request->technician_id,
                'warehouse_id' => $request->warehouse_id,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => 'pending',
                'created_by' => Auth::id() ?? 1,
            ]);

            // Create repair items for selected devices
            if ($request->selected_devices) {
                foreach ($request->selected_devices as $deviceId) {
                    // Handle device images
                    $deviceImages = [];
                    if ($request->hasFile("device_images.{$deviceId}")) {
                        foreach ($request->file("device_images.{$deviceId}") as $image) {
                            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $path = $image->storeAs('repairs/devices', $filename, 'public');
                            $deviceImages[] = $path;
                        }
                    }

                    // Get device parts info
                    $deviceParts = [];
                    if ($request->has('damaged_parts')) {
                        foreach ($request->damaged_parts as $partId) {
                            $deviceParts[] = [
                                'part_id' => $partId,
                                'is_damaged' => true,
                                'replaced' => false,
                            ];
                        }
                    }

                    RepairItem::create([
                        'repair_id' => $repair->id,
                        'device_code' => $request->input("device_code.{$deviceId}", ''),
                        'device_name' => $request->input("device_name.{$deviceId}", ''),
                        'device_serial' => $request->input("device_serial.{$deviceId}", ''),
                        'device_status' => 'selected',
                        'device_notes' => $request->input("device_notes.{$deviceId}", ''),
                        'device_images' => $deviceImages,
                        'device_parts' => $deviceParts,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('repairs.show', $repair->id)
                ->with('success', 'Phiếu sửa chữa đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Repair $repair)
    {
        $repair->load(['warranty', 'repairItems', 'technician', 'createdBy', 'warehouse']);
        return view('warranties.repair_detail', compact('repair'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repair $repair)
    {
        $repair->load(['warranty', 'repairItems']);
        return view('warranties.repair_edit', compact('repair'));
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
            'warehouse_id' => 'required|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads for repair photos
            $repairPhotos = $repair->repair_photos ?? [];
            if ($request->hasFile('repair_photos')) {
                foreach ($request->file('repair_photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('repairs/photos', $filename, 'public');
                    $repairPhotos[] = $path;
                }
            }

            // Update repair record
            $repair->update([
                'repair_type' => $request->repair_type,
                'repair_date' => $request->repair_date,
                'technician_id' => $request->technician_id,
                'warehouse_id' => $request->warehouse_id,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('repairs.show', $repair->id)
                ->with('success', 'Phiếu sửa chữa đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
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

    /**
     * Search warranty by code (API endpoint)
     */
    public function searchWarranty(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');
        
        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành'
            ]);
        }

        $warranty = Warranty::where('warranty_code', $warrantyCode)
            ->with(['dispatchItem.good', 'dispatchItem.material', 'dispatchItem.product'])
            ->first();

        if (!$warranty) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin bảo hành'
            ]);
        }

        // Get device information
        $devices = [];
        if ($warranty->dispatchItem) {
            $item = $warranty->dispatchItem->good ?? $warranty->dispatchItem->material ?? $warranty->dispatchItem->product;
            
            if ($item) {
                $devices[] = [
                    'id' => $warranty->id,
                    'code' => $item->code ?? 'N/A',
                    'name' => $item->name ?? 'N/A',
                    'serial' => $warranty->serial_number ?? 'N/A',
                    'status' => 'active',
                    'parts' => [] // This would need to be expanded based on your parts system
                ];
            }
        }

        return response()->json([
            'success' => true,
            'warranty' => [
                'warranty_code' => $warranty->warranty_code,
                'customer_name' => $warranty->customer_name,
                'project_name' => $warranty->project_name,
                'devices' => $devices,
                'repair_history' => [] // This would need to be expanded to show previous repairs
            ]
        ]);
    }
}
