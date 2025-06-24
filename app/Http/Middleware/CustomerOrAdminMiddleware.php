<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerOrAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cho phép truy cập nếu là admin
        if (Auth::guard('web')->check()) {
            return $next($request);
        }
        
        // Cho phép truy cập nếu là khách hàng đã đăng nhập
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }
        
        // Không cho phép truy cập và chuyển hướng đến trang đăng nhập
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để truy cập trang này.');
    }
}
