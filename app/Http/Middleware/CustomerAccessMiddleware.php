<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('customer')->check()) {
            $routeName = $request->route()->getName();
            
            // Danh sách các route được phép truy cập
            $allowedRoutes = [
                'customer.dashboard',
                'requests.customer-maintenance.create',
                'requests.customer-maintenance.store',
                'requests.customer-maintenance.show',
                'profile',
                'profile.update_password',
                'logout'
            ];

            // Kiểm tra xem route hiện tại có được phép không
            $isAllowed = false;
            foreach ($allowedRoutes as $route) {
                if (Str::is($route, $routeName)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return redirect('/customer/dashboard')
                    ->with('error', 'Bạn không có quyền truy cập trang này.');
            }
        }

        return $next($request);
    }
} 