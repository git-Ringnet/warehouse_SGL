<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyController extends Controller
{
    /**
     * Display a listing of warranties.
     */
    public function index(Request $request)
    {
        $query = Warranty::with(['dispatch', 'dispatchItem', 'creator']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('warranty_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('project_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('serial_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('warranty_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('warranty_end_date', '<=', $request->date_to);
        }

        $warranties = $query->orderBy('warranty_start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('warranties.index', compact('warranties'));
    }

    /**
     * Display the specified warranty.
     */
    public function show(Warranty $warranty)
    {
        $warranty->load(['dispatch', 'dispatchItem', 'creator']);

        // Ghi nhật ký xem chi tiết bảo hành
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'warranties',
                'Xem chi tiết bảo hành: ' . $warranty->warranty_code,
                null,
                $warranty->toArray()
            );
        }

        return view('warranties.show', compact('warranty'));
    }

    /**
     * Check warranty status by code (public endpoint).
     */
    public function check($warrantyCode)
    {
        $warranty = Warranty::where('warranty_code', $warrantyCode)
            ->with(['dispatch', 'dispatchItem', 'creator'])
            ->first();

        if (!$warranty) {
            return view('warranties.verify', [
                'warranty' => null,
                'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
            ]);
        }

        // Load the item relationship dynamically based on item_type
        switch ($warranty->item_type) {
            case 'product':
                $warranty->load(['product']);
                break;
            case 'material':
                $warranty->load(['material']);
                break;
            case 'good':
                $warranty->load(['good']);
                break;
        }

        return view('warranties.verify', compact('warranty'));
    }

    /**
     * API endpoint to check warranty status
     */
    public function apiCheck(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');
        
        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành'
            ]);
        }

        $warranty = Warranty::where('warranty_code', $warrantyCode)->first();

        if (!$warranty) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin bảo hành'
            ]);
        }

        return response()->json([
            'success' => true,
            'warranty' => [
                'warranty_code' => $warranty->warranty_code,
                'customer_name' => $warranty->customer_name,
                'project_name' => $warranty->project_name,
                'item_name' => $warranty->item->name ?? 'N/A',
                'item_code' => $warranty->item->code ?? 'N/A',
                'serial_number' => $warranty->serial_number,
                'purchase_date' => $warranty->purchase_date->format('d/m/Y'),
                'warranty_start_date' => $warranty->warranty_start_date->format('d/m/Y'),
                'warranty_end_date' => $warranty->warranty_end_date->format('d/m/Y'),
                'warranty_period_months' => $warranty->warranty_period_months,
                'status' => $warranty->status,
                'status_label' => $warranty->status_label,
                'status_color' => $warranty->status_color,
                'is_active' => $warranty->is_active,
                'remaining_days' => $warranty->remaining_days,
                'warranty_terms' => $warranty->warranty_terms,
            ]
        ]);
    }

    /**
     * Get warranties for a specific dispatch
     */
    public function getDispatchWarranties($dispatchId)
    {
        $warranties = Warranty::where('dispatch_id', $dispatchId)
            ->with(['dispatchItem'])
            ->get();

        return response()->json([
            'success' => true,
            'warranties' => $warranties
        ]);
    }
    
    /**
     * API endpoint to get warranty items
     */
    public function getWarrantyItems($warrantyId)
    {
        try {
            $warranty = Warranty::with(['dispatch.items' => function($query) {
                $query->where('category', 'contract');
            }, 'dispatch.items.product', 'dispatch.items.material', 'dispatch.items.good'])
            ->findOrFail($warrantyId);

            $items = [];
            
            if ($warranty->dispatch && $warranty->dispatch->items) {
                foreach ($warranty->dispatch->items as $item) {
                    // Lấy thông tin chi tiết dựa vào loại item
                    $itemDetail = null;
                    $itemName = '';
                    $itemType = '';
                    $serialNumber = '';

                    switch ($item->item_type) {
                        case 'product':
                            if ($item->product) {
                                $itemDetail = $item->product;
                                $itemName = $itemDetail->name;
                                $itemType = 'Thiết bị';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                        case 'material':
                            if ($item->material) {
                                $itemDetail = $item->material;
                                $itemName = $itemDetail->name;
                                $itemType = 'Vật tư';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                        case 'good':
                            if ($item->good) {
                                $itemDetail = $item->good;
                                $itemName = $itemDetail->name;
                                $itemType = 'Hàng hóa';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                    }

                    if ($itemDetail) {
                        $items[] = [
                            'id' => $item->id,
                            'name' => $itemName,
                            'type' => $itemType,
                            'serial_number' => $serialNumber
                        ];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thiết bị: ' . $e->getMessage()
            ], 500);
        }
    }
}
