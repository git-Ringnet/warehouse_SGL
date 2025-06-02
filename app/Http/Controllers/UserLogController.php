<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $userId = $request->input('user_id');
        $action = $request->input('action');
        $module = $request->input('module');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = UserLog::with('user');
        
        // Lọc theo người dùng
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        // Lọc theo hành động
        if ($action) {
            $query->where('action', $action);
        }
        
        // Lọc theo module
        if ($module) {
            $query->where('module', $module);
        }
        
        // Lọc theo thời gian
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter == 'description') {
                $query->where('description', 'like', "%{$search}%");
            } elseif ($filter == 'user') {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhere('module', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhereHas('user', function($subq) use ($search) {
                           $subq->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                      });
                });
            }
        }
        
        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate(20);
        
        // Lấy danh sách người dùng, hành động và module để filter
        $users = Employee::orderBy('name')->get(['id', 'name', 'username']);
        $actions = UserLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $modules = UserLog::select('module')->distinct()->orderBy('module')->pluck('module');
        
        return view('user-logs.index', compact(
            'logs', 
            'search', 
            'filter', 
            'userId', 
            'action', 
            'module', 
            'startDate', 
            'endDate', 
            'users', 
            'actions', 
            'modules'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $log = UserLog::with('user')->findOrFail($id);
        return view('user-logs.show', compact('log'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $userId = $request->input('user_id');
        $action = $request->input('action');
        $module = $request->input('module');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = UserLog::with('user');
        
        // Áp dụng các filter
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($action) {
            $query->where('action', $action);
        }
        
        if ($module) {
            $query->where('module', $module);
        }
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        // Tạo tên file với timestamp
        $filename = 'user_logs_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        // Tạo file CSV
        $handle = fopen('php://output', 'w');
        
        // Add BOM to fix UTF-8 in Excel
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Thêm header
        fputcsv($handle, [
            'ID', 
            'Người dùng', 
            'Hành động', 
            'Module', 
            'Mô tả', 
            'IP', 
            'Thời gian'
        ]);
        
        // Thêm dữ liệu
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user ? $log->user->name . ' (' . $log->user->username . ')' : 'Không xác định',
                $log->action,
                $log->module,
                $log->description,
                $log->ip_address,
                $log->created_at->format('d/m/Y H:i:s')
            ]);
        }
        
        fclose($handle);
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'export',
                'user-logs',
                'Xuất nhật ký người dùng (' . $logs->count() . ' bản ghi)',
                null,
                [
                    'filters' => $request->only(['user_id', 'action', 'module', 'start_date', 'end_date']),
                    'count' => $logs->count()
                ]
            );
        }
        
        return response()->stream(
            function() use ($handle) {
                // Đã đóng handle ở trên
            }, 
            200, 
            $headers
        );
    }
}
