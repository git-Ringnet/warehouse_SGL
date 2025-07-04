<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Kiểm tra khách hàng trước
        if (Auth::guard('customer')->check()) {
            // Cho phép khách hàng truy cập các route customer-maintenance
            if (str_starts_with($permission, 'requests.customer-maintenance.')) {
                return $next($request);
            }
            
            // Nếu không phải route customer-maintenance, chuyển về dashboard
            return redirect()->route('customer.dashboard')
                ->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }

        // Kiểm tra nhân viên
        if (!Auth::guard('web')->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $employee = Auth::guard('web')->user();

        // Nếu là admin, cho phép truy cập tất cả
        if ($employee->role === 'admin') {
            return $next($request);
        }

        // Kiểm tra quyền thông qua nhóm quyền của nhân viên
        if ($employee->role_id) {
            $role = $employee->roleGroup;
            
            // Kiểm tra xem nhóm quyền có đang hoạt động không
            if (!$role || !$role->is_active) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'Nhóm quyền của bạn đã bị vô hiệu hóa'], 403);
                }
                return abort(403, 'Nhóm quyền của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
            }

            // Kiểm tra quyền cụ thể
            if ($role->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này'], 403);
        }

        return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
    }
} 