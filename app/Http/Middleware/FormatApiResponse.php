<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class FormatApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Chỉ xử lý cho API routes
        if (!$request->is('api/*') && !str_starts_with($request->path(), 'api/')) {
            return $response;
        }
        
        // Kiểm tra nếu response là 401 và chưa có format đúng
        if ($response->getStatusCode() === 401) {
            $content = $response->getContent();
            
            Log::info('FormatApiResponse: 401 detected', [
                'path' => $request->path(),
                'content' => $content
            ]);
            
            // Nếu response chỉ có message đơn giản, format lại
            if ($content) {
                $data = json_decode($content, true);
                
                // Kiểm tra nếu chưa có success và error_code
                if (is_array($data) && !isset($data['success']) && !isset($data['error_code'])) {
                    Log::info('FormatApiResponse: Reformatting response');
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Unauthenticated.',
                        'error_code' => 'AUTH_001',
                    ], 401);
                }
            }
        }
        
        return $response;
    }
}
