<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Lấy tất cả thông báo của người dùng hiện tại
     */
    public function index()
    {
        $user = Auth::guard('web')->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Handle AJAX requests
        if (request()->ajax()) {
            return view('notifications.partials.notification-list', compact('notifications'))->render();
        }
            
        return view('notifications.index', compact('notifications'));
    }
    
    /**
     * Lấy thông báo mới nhất cho người dùng hiện tại (AJAX)
     */
    public function getLatest()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
            
        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        $user = Auth::guard('web')->user();
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc'
        ]);
    }
    
    /**
     * Đánh dấu một thông báo cụ thể là đã đọc
     */
    public function markAsRead($id)
    {
        $user = Auth::guard('web')->user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$notification) {
            return response()->json(['error' => 'Không tìm thấy thông báo'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu thông báo là đã đọc'
        ]);
    }
    
    /**
     * Xóa một thông báo
     */
    public function destroy($id)
    {
        $user = Auth::guard('web')->user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$notification) {
            return response()->json(['error' => 'Không tìm thấy thông báo'], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thông báo'
        ]);
    }

    /**
     * Tạo thông báo test
     */
    public function createTestNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:success,warning,error,info'
        ]);

        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            $request->title,
            $request->message,
            $request->type,
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo test thành công',
            'notification' => $notification
        ]);
    }

    /**
     * Tạo thông báo phiếu lắp ráp test
     */
    public function createAssemblyTestNotification()
    {
        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            'Phiếu lắp ráp mới',
            'Bạn đã được phân công lắp ráp phiếu #ASM-TEST-001',
            'info',
            $user->id,
            'assembly',
            null,
            '#'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo phiếu lắp ráp test'
        ]);
    }

    /**
     * Tạo thông báo phiếu kiểm thử test
     */
    public function createTestingTestNotification()
    {
        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            'Phiếu kiểm thử mới',
            'Phiếu kiểm thử #QA-TEST-001 đã được tạo từ phiếu lắp ráp #ASM-TEST-001',
            'info',
            $user->id,
            'testing',
            null,
            '#'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo phiếu kiểm thử test'
        ]);
    }

    /**
     * Tạo thông báo phiếu xuất kho test
     */
    public function createDispatchTestNotification()
    {
        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            'Phiếu xuất kho mới',
            'Phiếu xuất kho #PX-TEST-001 đã được tạo từ phiếu lắp ráp #ASM-TEST-001',
            'info',
            $user->id,
            'dispatch',
            null,
            '#'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo phiếu xuất kho test'
        ]);
    }

    /**
     * Tạo thông báo dự án test
     */
    public function createProjectTestNotification()
    {
        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            'Dự án mới được tạo',
            'Dự án #PRJ-TEST-001 - Dự án test đã được tạo và phân công cho bạn.',
            'info',
            $user->id,
            'project',
            null,
            '#'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo dự án test'
        ]);
    }

    /**
     * Tạo thông báo dự án hết hạn bảo hành test
     */
    public function createProjectExpiryTestNotification()
    {
        $user = Auth::guard('web')->user();
        
        $notification = Notification::createNotification(
            'Dự án sắp hết hạn bảo hành',
            'Dự án #PRJ-TEST-001 sẽ hết hạn bảo hành trong 7 ngày.',
            'warning',
            $user->id,
            'project',
            null,
            '#'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo dự án hết hạn bảo hành test'
        ]);
    }

    /**
     * Hiển thị trang test thông báo
     */
    public function showTestPage()
    {
        return view('test-notification');
    }
}
