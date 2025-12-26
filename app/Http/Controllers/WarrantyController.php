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
        $query = Warranty::with(['dispatch.project.customer', 'dispatchItem', 'creator']);

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
            ->paginate(10);

        return view('warranties.index', compact('warranties'));
    }

    /**
     * Display the specified warranty.
     */
    public function show(Warranty $warranty)
    {
        // Eager load tất cả relationships cần thiết để tránh N+1 query
        $warranty->load([
            'dispatch.project.customer',
            'dispatch.items' => function ($query) {
                $query->where('category', '!=', 'backup')
                    ->whereIn('item_type', ['product', 'good']);
            },
            'dispatch.items.product',
            'dispatch.items.good',
            'dispatchItem',
            'creator',
            'product',  // Cho single item warranty
            'material', // Cho single item warranty
            'good'      // Cho single item warranty
        ]);

        // Pre-load project items và product materials để tránh N+1 query trong view
        $projectItems = [];
        $productMaterials = [];

        if ($warranty->item_type === 'project' && $warranty->item_id) {
            // Load tất cả dispatches của project một lần
            // Loại trừ phiếu xuất vật tư từ lắp ráp, chỉ lấy phiếu thành phẩm từ kiểm thử
            $projectDispatches = \App\Models\Dispatch::where('project_id', $warranty->item_id)
                ->whereIn('status', ['approved', 'completed'])
                ->when($warranty->dispatch, function ($query) use ($warranty) {
                    if ($warranty->dispatch->dispatch_type) {
                        $query->where('dispatch_type', $warranty->dispatch->dispatch_type);
                    }
                })
                ->where(function ($q) {
                    // Phiếu xuất từ kiểm thử (chứa thành phẩm đã qua QA)
                    $q->where('dispatch_note', 'like', '%Sinh từ phiếu kiểm thử%')
                        // Hoặc phiếu xuất trực tiếp (không qua lắp ráp/kiểm thử)
                        ->orWhere(function ($subQ) {
                        $subQ->where('dispatch_note', 'not like', '%Sinh từ phiếu lắp ráp%')
                            ->where('dispatch_note', 'not like', '%Sinh từ phiếu kiểm thử%');
                    })
                        // Hoặc dispatch_note là null (phiếu xuất thủ công)
                        ->orWhereNull('dispatch_note');
                })
                ->with([
                    'items' => function ($query) {
                        $query->where('category', '!=', 'backup');
                    },
                    'items.product',
                    'items.good',
                    'items.material'
                ])
                ->get();

            // Build project items
            foreach ($projectDispatches as $dispatch) {
                foreach ($dispatch->items as $dispatchItem) {
                    $itemDetails = null;
                    switch ($dispatchItem->item_type) {
                        case 'material':
                            $itemDetails = $dispatchItem->material;
                            break;
                        case 'product':
                            $itemDetails = $dispatchItem->product;
                            break;
                        case 'good':
                            $itemDetails = $dispatchItem->good;
                            break;
                    }

                    if ($itemDetails) {
                        $projectItems[] = [
                            'code' => $itemDetails->code,
                            'name' => $itemDetails->name,
                            'quantity' => $dispatchItem->quantity,
                            'type' => $dispatchItem->item_type,
                            'serial_numbers' => $dispatchItem->serial_numbers,
                            'dispatch_item_id' => $dispatchItem->id,
                            'dispatch_id' => $dispatch->id,
                        ];
                    }
                }
            }

            // Build product materials với batch query
            $productItems = collect($projectItems)->where('type', 'product');
            if ($productItems->isNotEmpty()) {
                // Collect tất cả serial numbers
                $allSerials = [];
                foreach ($productItems as $item) {
                    if (!empty($item['serial_numbers'])) {
                        $serials = is_array($item['serial_numbers']) ? $item['serial_numbers'] : [$item['serial_numbers']];
                        $allSerials = array_merge($allSerials, $serials);
                    }
                }
                $allSerials = array_unique(array_filter($allSerials));

                // Batch load assembly products có chứa serial numbers cần tìm
                if (!empty($allSerials)) {
                    // Tìm tất cả AssemblyProduct có chứa serial trong danh sách
                    $serialConditions = [];
                    foreach ($allSerials as $serial) {
                        $serialConditions[] = "FIND_IN_SET('" . addslashes($serial) . "', serials) > 0";
                    }

                    $assemblyProducts = \App\Models\AssemblyProduct::whereRaw('(' . implode(' OR ', $serialConditions) . ')')
                        ->with(['assembly.materials.material'])
                        ->get();

                    // Group materials by serial
                    $materialsBySerial = [];
                    foreach ($assemblyProducts as $assemblyProduct) {
                        $serials = $assemblyProduct->serials ? explode(',', $assemblyProduct->serials) : [];
                        foreach ($serials as $serial) {
                            $serial = trim($serial);
                            if (in_array($serial, $allSerials) && $assemblyProduct->assembly) {
                                if (!isset($materialsBySerial[$serial])) {
                                    $materialsBySerial[$serial] = [
                                        'product' => $assemblyProduct,
                                        'materials' => []
                                    ];
                                }
                                // Lấy materials từ assembly
                                foreach ($assemblyProduct->assembly->materials as $am) {
                                    if ($am->material) {
                                        $materialsBySerial[$serial]['materials'][$am->material->code] = [
                                            'code' => $am->material->code,
                                            'name' => $am->material->name,
                                            'quantity' => $am->quantity,
                                            'assembly_code' => $assemblyProduct->assembly->code ?? 'N/A',
                                            'serial' => $am->serial ?? 'N/A'
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    // Build product materials array
                    foreach ($productItems as $item) {
                        $serials = is_array($item['serial_numbers']) ? $item['serial_numbers'] : [$item['serial_numbers']];
                        foreach ($serials as $serial) {
                            if (!empty($serial) && isset($materialsBySerial[$serial])) {
                                $productMaterials[] = [
                                    'product_code' => $item['code'],
                                    'product_name' => $item['name'],
                                    'serial_number' => $serial,
                                    'materials' => array_values($materialsBySerial[$serial]['materials'])
                                ];
                            }
                        }
                    }
                }
            }
        }

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

        return view('warranties.show', compact('warranty', 'projectItems', 'productMaterials'));
    }

    /**
     * Check warranty status by code (public endpoint).
     */
    public function check($warrantyCode)
    {
        $warranty = Warranty::where('warranty_code', $warrantyCode)
            ->with(['dispatch.project.customer', 'dispatchItem', 'creator'])
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
            $warranty = Warranty::with([
                'dispatch.project.customer',
                'dispatch.items' => function ($query) {
                    $query->where('category', 'contract');
                },
                'dispatch.items.product',
                'dispatch.items.material',
                'dispatch.items.good'
            ])
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
                            'code' => $itemDetail->code ?? '',
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

    /**
     * API: Export warranty to PDF
     * Route: GET /api/warranties/{warranty_code}/export-pdf
     */
    public function exportPdf($warrantyCode)
    {
        try {
            // Tìm warranty theo code
            $warranty = Warranty::where('warranty_code', $warrantyCode)
                ->with(['dispatch.project', 'dispatch.rental', 'dispatch.items.product', 'dispatch.items.good', 'creator'])
                ->first();

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy dữ liệu bảo hành để xuất file.'
                ], 404);
            }

            // Load item relationship
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

            // Tạo tên file
            $fileName = 'Phieu_Bao_Hanh_' . $warranty->warranty_code . '.pdf';
            $filePath = 'exports/warranties/' . $fileName;

            // Tạo PDF
            $pdf = \PDF::loadView('exports.warranty_pdf', ['warranty' => $warranty]);

            // Lưu file vào storage/app/public
            \Storage::disk('public')->put($filePath, $pdf->output());

            // Lấy thông tin file
            $fileSize = \Storage::disk('public')->size($filePath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // Tạo URL download (expires sau 1 giờ)
            $downloadUrl = url('storage/' . $filePath);

            return response()->json([
                'success' => true,
                'message' => 'Tạo file PDF thành công',
                'data' => [
                    'file_name' => $fileName,
                    'file_type' => 'pdf',
                    'file_size' => $fileSizeMB . ' MB',
                    'download_url' => $downloadUrl,
                    'expires_in' => 3600 // 1 hour
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Export warranty PDF error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo file PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
