<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu người dùng đã đăng nhập và có quyền admin
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            
            // Kiểm tra nếu user có role admin
            if ($user->role === 'admin') {
                return $next($request);
            }
        }
        
        // Nếu không phải admin, trả về lỗi 403
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập vào chức năng này. Chỉ admin mới có quyền quản lý nhóm phân quyền.'
            ], 403);
        }
        
        return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào chức năng này. Chỉ admin mới có quyền quản lý nhóm phân quyền.');
    }
} 