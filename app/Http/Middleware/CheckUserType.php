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
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (Auth::guard('web')->check()) {
            // Người dùng đã đăng nhập với guard web (nhân viên)
            Session::put('user_type', 'employee');
        } elseif (Auth::guard('customer')->check()) {
            // Người dùng đã đăng nhập với guard customer (khách hàng)
            Session::put('user_type', 'customer');
        }
        
        return $next($request);
    }
} 