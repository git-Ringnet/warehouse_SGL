<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Lấy tất cả thông báo của người dùng hiện tại
     */
    public function index()
    {
        $user = request()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Nếu là customer, chỉ hiển thị các thông báo liên quan
        if ($user->role === 'customer') {
            $query->whereIn('related_type', [
                'project',
                'rental',
                'customer_maintenance_request',
                'maintenance_request',
                'customer',
                'account'
            ]);
        }

        $notifications = $query->paginate(10);

        // Handle AJAX requests
        if (request()->ajax()) {
            return view('notifications.partials.notification-list', compact('notifications'))->render();
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * API: Lấy danh sách thông báo (có phân trang)
     * Route: GET /api/notifications
     * Query params:
     *   - page: số trang (mặc định 1)
     *   - limit: số bản ghi mỗi trang (mặc định 10)
     *   - filter: lọc tình trạng (all, unread, read) - mặc định: all
     */
    public function apiIndex(Request $request)
    {
        try {
            // Lấy user từ token
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'AUTH_001'
                ], 401);
            }

            // Lấy params
            $page = max(1, (int) $request->get('page', 1));
            $limit = max(1, min(100, (int) $request->get('limit', 10))); // Giới hạn từ 1 đến 100
            $filter = $request->get('filter', 'all'); // all, unread, read

            // Query notifications của user
            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Nếu là customer, chỉ hiển thị các thông báo liên quan
            if ($user->role === 'customer') {
                $query->whereIn('related_type', [
                    'project',
                    'rental',
                    'customer_maintenance_request',
                    'maintenance_request',
                    'customer',
                    'account'
                ]);
            }

            // Áp dụng filter
            if ($filter === 'unread') {
                $query->where('is_read', false);
            } elseif ($filter === 'read') {
                $query->where('is_read', true);
            }

            // Phân trang
            $notifications = $query->paginate($limit, ['*'], 'page', $page);

            // Base query cho count
            $countQuery = Notification::where('user_id', $user->id);
            if ($user->role === 'customer') {
                $countQuery->whereIn('related_type', [
                    'project',
                    'rental',
                    'customer_maintenance_request',
                    'maintenance_request',
                    'customer',
                    'account'
                ]);
            }

            // Đếm số thông báo theo từng loại
            $allCount = (clone $countQuery)->count();
            $unreadCount = (clone $countQuery)
                ->where('is_read', false)
                ->count();
            $readCount = (clone $countQuery)
                ->where('is_read', true)
                ->count();

            // Format dữ liệu trả về
            $data = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->message ?? '',
                    'type' => $notification->type,
                    'color' => Notification::getColorByType($notification->type),
                    'data' => $notification->data ?? null,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => $notification->created_at ? $notification->created_at->format('d/m/Y H:i:s') : null,
                    'icon' => $notification->icon ?? '',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'total_items' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                ],
                'counts' => [
                    'all' => $allCount,
                    'unread' => $unreadCount,
                    'read' => $readCount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API get notifications error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Đánh dấu đã đọc thông báo (một hoặc tất cả)
     * Route: PUT /api/notifications/mark-read
     * Request Body:
     *   - notification_id: "all" hoặc ID cụ thể (integer)
     */
    public function apiMarkAsRead(Request $request)
    {
        try {
            // Lấy user từ token
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'AUTH_001'
                ], 401);
            }

            // Validation
            $request->validate([
                'notification_id' => 'required'
            ], [
                'notification_id.required' => 'Trường notification_id là bắt buộc.'
            ]);

            $notificationId = $request->notification_id;

            // Trường hợp đánh dấu tất cả
            if ($notificationId === 'all') {
                Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã đánh dấu đã đọc thành công.'
                ]);
            }

            // Trường hợp đánh dấu một thông báo cụ thể
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông báo.'
                ], 404);
            }

            // Đánh dấu đã đọc
            $notification->is_read = true;
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu đã đọc thành công.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API mark notification as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đánh dấu thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông báo mới nhất cho người dùng hiện tại (AJAX)
     */
    public function getLatest()
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userType = ($user instanceof \App\Models\User || (isset($user->role) && $user->role === 'customer')) ? 'customer' : 'employee';

        $query = Notification::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->orderBy('created_at', 'desc');

        $countQuery = Notification::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->where('is_read', false);

        // Filter for customer
        if ($userType === 'customer') {
            $allowedTypes = [
                'project',
                'rental',
                'customer_maintenance_request',
                'maintenance_request',
                'customer',
                'account',
                'user'
            ];
            $query->whereIn('related_type', $allowedTypes);
            $countQuery->whereIn('related_type', $allowedTypes);
        }

        $notifications = $query->take(5)->get();
        $unreadCount = $countQuery->count();

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
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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

        $user = request()->user();

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
        $user = request()->user();

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
        $user = request()->user();

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
        $user = request()->user();

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
        $user = request()->user();

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
        $user = request()->user();

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
