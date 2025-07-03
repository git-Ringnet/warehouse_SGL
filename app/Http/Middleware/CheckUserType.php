<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Employee;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $employee = Auth::guard('web')->user();
            Session::put('user_type', 'employee');
            
            // Kiểm tra quyền truy cập dashboard
            if ($request->is('dashboard*') && 
                !($employee->role === 'admin' || 
                  ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview')))) {
                return redirect('/');
            }
        } elseif (Auth::guard('customer')->check()) {
            Session::put('user_type', 'customer');
            
            // Chặn khách hàng truy cập trang dành cho nhân viên
            if ($request->is('dashboard*')) {
                return redirect('/customer/dashboard');
            }
        }
        
        return $next($request);
    }
} 