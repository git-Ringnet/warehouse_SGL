<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Xử lý lỗi khi file upload quá lớn
        $this->renderable(function (PostTooLargeException $e) {
            return redirect()->back()->with('error', 'File tải lên quá lớn. Kích thước tối đa cho phép là 40MB. Vui lòng chọn file nhỏ hơn hoặc liên hệ quản trị viên.');
        });
        
        // Xử lý lỗi xác thực cho API
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'AUTH_001',
                ], 401);
            }
        });
    }
    
    /**
     * Tùy chỉnh phản hồi khi không được xác thực
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Kiểm tra nếu là API request
        if ($request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'AUTH_001',
            ], 401);
        }
        
        return redirect()->guest(route('login'));
    }
}