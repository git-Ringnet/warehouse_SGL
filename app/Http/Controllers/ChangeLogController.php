<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\ChangeLogsExport;

class ChangeLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ChangeLog::query();

        // Filter by change type
        if ($request->filled('change_type')) {
            $query->byChangeType($request->change_type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Search by item code/name
        if ($request->filled('search')) {
            $query->byItem($request->search);
        }

        // Order by time_changed descending
        $changeLogs = $query->orderBy('time_changed', 'desc')
            ->paginate(10);

        return view('changelog.index', compact('changeLogs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ChangeLog $changeLog)
    {
        return view('changelog.show', compact('changeLog'));
    }

    /**
     * Get detailed information for modal view
     */
    public function getDetails(ChangeLog $changeLog)
    {
        return response()->json([
            'change_log' => $changeLog,
            'change_type_label' => $changeLog->getChangeTypeLabel(),
            'detailed_info' => $changeLog->detailed_info
        ]);
    }

    /**
     * Helper method để tạo change log entry
     * Sử dụng: ChangeLogController::createLogEntry([...])
     */
    public static function createLogEntry($data)
    {
        // Lấy thông tin user hiện tại nếu không có performed_by
        if (!isset($data['performed_by'])) {
            if (Auth::guard('web')->check()) {
                $data['performed_by'] = Auth::guard('web')->user()->name;
            } elseif (Auth::guard('customer')->check()) {
                $data['performed_by'] = Auth::guard('customer')->user()->name;
            } else {
                $data['performed_by'] = 'Hệ thống';
            }
        }

        return ChangeLog::create([
            'time_changed' => $data['time_changed'] ?? now(),
            'item_code' => $data['item_code'],
            'item_name' => $data['item_name'],
            'change_type' => $data['change_type'],
            'document_code' => $data['document_code'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'description' => $data['description'] ?? null,
            'performed_by' => $data['performed_by'],
            'notes' => $data['notes'] ?? null,
            'detailed_info' => $data['detailed_info'] ?? null
        ]);
    }

    /**
     * Helper method cho Lắp ráp
     */
    public static function logAssembly($itemCode, $itemName, $quantity, $assemblyCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'lap_rap',
            'document_code' => $assemblyCode,
            'quantity' => $quantity,
            'description' => $description ?? "Sử dụng vật tư trong phiếu lắp ráp {$assemblyCode}",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method cho Xuất kho
     */
    public static function logExport($itemCode, $itemName, $quantity, $exportCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'xuat_kho',
            'document_code' => $exportCode,
            'quantity' => $quantity,
            'description' => $description ?? "",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method cho Nhập kho
     */
    public static function logImport($itemCode, $itemName, $quantity, $importCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'nhap_kho',
            'document_code' => $importCode,
            'quantity' => $quantity,
            'description' => $description ?? "Nhập kho theo phiếu {$importCode}",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method cho Sửa chữa
     */
    public static function logRepair($itemCode, $itemName, $quantity, $repairCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'sua_chua',
            'document_code' => $repairCode,
            'quantity' => $quantity,
            'description' => $description ?? "Sửa chữa/bảo trì theo phiếu {$repairCode}",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method cho Thu hồi
     */
    public static function logRecall($itemCode, $itemName, $quantity, $recallCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'thu_hoi',
            'document_code' => $recallCode,
            'quantity' => $quantity,
            'description' => $description ?? "Thu hồi sản phẩm theo phiếu {$recallCode}",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method cho Chuyển kho
     */
    public static function logTransfer($itemCode, $itemName, $quantity, $transferCode, $description = null, $detailedInfo = [], $notes = null)
    {
        return self::createLogEntry([
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'change_type' => 'chuyen_kho',
            'document_code' => $transferCode,
            'quantity' => $quantity,
            'description' => $description ?? "Chuyển kho theo phiếu {$transferCode}",
            'notes' => $notes,
            'detailed_info' => $detailedInfo
        ]);
    }

    /**
     * Helper method để log nhiều items cùng lúc
     */
    public static function logMultipleItems($items, $changeType, $documentCode, $description = null)
    {
        $logs = [];
        foreach ($items as $item) {
            $logs[] = self::createLogEntry([
                'item_code' => $item['code'],
                'item_name' => $item['name'],
                'change_type' => $changeType,
                'document_code' => $documentCode,
                'quantity' => $item['quantity'],
                'description' => $description,
                'detailed_info' => $item['details'] ?? null
            ]);
        }
        return $logs;
    }

    /**
     * Cập nhật changelog theo ID
     */
    public function update(Request $request, ChangeLog $changeLog)
    {
        $validated = $request->validate([
            'item_code' => 'sometimes|string|max:255',
            'item_name' => 'sometimes|string|max:255',
            'change_type' => 'sometimes|in:lap_rap,xuat_kho,sua_chua,thu_hoi,nhap_kho,chuyen_kho',
            'document_code' => 'sometimes|nullable|string|max:255',
            'quantity' => 'sometimes|integer',
            'description' => 'sometimes|nullable|string',
            'performed_by' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
            'detailed_info' => 'sometimes|nullable|array'
        ]);

        $changeLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Changelog đã được cập nhật thành công',
            'data' => $changeLog->fresh()
        ]);
    }

    /**
     * Helper method để cập nhật changelog entry
     */
    public static function updateLogEntry($id, $data)
    {
        $changeLog = ChangeLog::findOrFail($id);

        // Chỉ cập nhật các field được truyền vào
        $updateData = array_filter($data, function ($value) {
            return $value !== null;
        });

        $changeLog->update($updateData);

        return $changeLog->fresh();
    }

    /**
     * Cập nhật description cho changelog
     */
    public static function updateDescription($id, $description)
    {
        return self::updateLogEntry($id, ['description' => $description]);
    }

    /**
     * Cập nhật notes cho changelog
     */
    public static function updateNotes($id, $notes)
    {
        return self::updateLogEntry($id, ['notes' => $notes]);
    }

    /**
     * Cập nhật detailed_info cho changelog
     */
    public static function updateDetailedInfo($id, $detailedInfo)
    {
        return self::updateLogEntry($id, ['detailed_info' => $detailedInfo]);
    }

    /**
     * Cập nhật quantity cho changelog
     */
    public static function updateQuantity($id, $quantity)
    {
        return self::updateLogEntry($id, ['quantity' => $quantity]);
    }

    /**
     * Cập nhật person thực hiện cho changelog
     */
    public static function updatePerformedBy($id, $performedBy)
    {
        return self::updateLogEntry($id, ['performed_by' => $performedBy]);
    }

    /**
     * Thêm hoặc cập nhật thông tin trong detailed_info
     */
    public static function addDetailedInfo($id, $key, $value)
    {
        $changeLog = ChangeLog::findOrFail($id);
        $detailedInfo = $changeLog->detailed_info ?? [];
        $detailedInfo[$key] = $value;

        return self::updateDetailedInfo($id, $detailedInfo);
    }

    /**
     * Xóa một field trong detailed_info
     */
    public static function removeDetailedInfo($id, $key)
    {
        $changeLog = ChangeLog::findOrFail($id);
        $detailedInfo = $changeLog->detailed_info ?? [];

        if (isset($detailedInfo[$key])) {
            unset($detailedInfo[$key]);
            return self::updateDetailedInfo($id, $detailedInfo);
        }

        return $changeLog;
    }

    /**
     * Tìm changelog theo mã phiếu và cập nhật
     */
    public static function updateByDocumentCode($documentCode, $data)
    {
        $changeLogs = ChangeLog::where('document_code', $documentCode)->get();

        $updated = [];
        foreach ($changeLogs as $changeLog) {
            $changeLog->update($data);
            $updated[] = $changeLog->fresh();
        }

        return $updated;
    }

    /**
     * Tìm changelog theo item code và cập nhật
     */
    /**
     * Export filtered change logs to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $logs = $query->orderBy('time_changed', 'desc')->get();

        return Excel::download(new ChangeLogsExport($logs), 'change_logs_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Export filtered change logs to PDF
     */
    public function exportPDF(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $logs = $query->orderBy('time_changed', 'desc')->get();

        $pdf = PDF::loadView('changelog.pdf', compact('logs'))
                    ->setPaper('a4', 'landscape');
        return $pdf->download('change_logs_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Build query with current filters
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = ChangeLog::query();

        if ($request->filled('change_type')) {
            $query->byChangeType($request->change_type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $query->byItem($request->search);
        }

        return $query;
    }

    public static function updateByItemCode($itemCode, $data)
    {
        $changeLogs = ChangeLog::where('item_code', $itemCode)->get();

        $updated = [];
        foreach ($changeLogs as $changeLog) {
            $changeLog->update($data);
            $updated[] = $changeLog->fresh();
        }

        return $updated;
    }
}
