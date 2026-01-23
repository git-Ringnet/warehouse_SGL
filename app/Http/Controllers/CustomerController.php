<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\UserLog;

class CustomerController extends Controller
{
    /**
     * Hiển thị danh sách khách hàng.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = Customer::query();

        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'name':
                        $query->where('name', 'like', "%{$search}%");
                        break;
                    case 'company_name':
                        $query->where('company_name', 'like', "%{$search}%");
                        break;
                    case 'phone':
                        $query->where('phone', 'like', "%{$search}%");
                        break;
                    case 'email':
                        $query->where('email', 'like', "%{$search}%");
                        break;
                    case 'address':
                        $query->where('address', 'like', "%{$search}%");
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }
        }

        $customers = $query->latest()->paginate(10);

        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $customers->appends([
            'search' => $search,
            'filter' => $filter
        ]);

        return view('customers.index', compact('customers', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo khách hàng mới.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Lưu khách hàng mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11|unique:customers,phone',
            'company_phone' => 'nullable|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Tên người đại diện không được để trống',
            'customer_name.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'customer_name.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'company_name.required' => 'Tên công ty không được để trống',
            'company_name.string' => 'Tên công ty phải là chuỗi ký tự',
            'company_name.max' => 'Tên công ty không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi khách hàng khác',
            'company_phone.numeric' => 'Số điện thoại công ty chỉ được nhập số',
            'company_phone.digits_between' => 'Số điện thoại công ty phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        $customer = Customer::create([
            'name' => $request->customer_name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'company_phone' => $request->company_phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        // Ghi nhật ký tạo mới khách hàng
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'customers',
                'Tạo mới khách hàng: ' . $customer->name . ' - ' . $customer->company_name,
                null,
                $customer->toArray()
            );
        }

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được thêm thành công.');
    }

    /**
     * Hiển thị chi tiết khách hàng.
     */
    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Lấy danh sách dự án liên quan đến khách hàng
        $projects = $customer->projects()->latest()->get();

        // Lấy danh sách phiếu cho thuê liên quan đến khách hàng
        $rentals = $customer->rentals()->latest()->get();

        // Ghi nhật ký xem chi tiết khách hàng
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'customers',
                'Xem chi tiết khách hàng: ' . $customer->name . ' - ' . $customer->company_name,
                null,
                ['id' => $customer->id, 'name' => $customer->name, 'company_name' => $customer->company_name]
            );
        }

        return view('customers.show', compact('customer', 'projects', 'rentals'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin khách hàng.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Cập nhật thông tin khách hàng trong database.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11|unique:customers,phone,' . $id,
            'company_phone' => 'nullable|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Tên người đại diện không được để trống',
            'customer_name.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'customer_name.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'company_name.required' => 'Tên công ty không được để trống',
            'company_name.string' => 'Tên công ty phải là chuỗi ký tự',
            'company_name.max' => 'Tên công ty không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi khách hàng khác',
            'company_phone.numeric' => 'Số điện thoại công ty chỉ được nhập số',
            'company_phone.digits_between' => 'Số điện thoại công ty phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        $customer = Customer::findOrFail($id);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $customer->toArray();

        $customer->update([
            'name' => $request->customer_name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'company_phone' => $request->company_phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        // Ghi nhật ký cập nhật thông tin khách hàng
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'customers',
                'Cập nhật thông tin khách hàng: ' . $customer->name . ' - ' . $customer->company_name,
                $oldData,
                $customer->toArray()
            );
        }

        // Gửi thông báo cho khách hàng
        $customerUsers = User::where('customer_id', $customer->id)->where('active', true)->get();
        foreach ($customerUsers as $user) {
            \App\Models\Notification::createNotification(
                'Cập nhật thông tin',
                'Thông tin khách hàng của bạn đã được cập nhật bởi quản trị viên.',
                'info',
                $user->id,
                'user',
                $user->id,
                route('profile'),
                null,
                'customer'
            );
        }

        return redirect()->route('customers.show', $id)
            ->with('success', 'Thông tin khách hàng đã được cập nhật thành công.');
    }

    /**
     * Kích hoạt tài khoản khách hàng
     */
    public function activateAccount(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Kiểm tra nếu đã có tài khoản
        if ($customer->has_account) {
            return redirect()->route('customers.index')
                ->with('error', 'Khách hàng đã có tài khoản.');
        }

        // Kiểm tra số điện thoại (bắt buộc để có thể đăng nhập)
        if (empty($customer->phone)) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Khách hàng chưa có số điện thoại. Vui lòng cập nhật số điện thoại trước khi kích hoạt tài khoản.');
        }

        // Xác định email để sử dụng
        // Nếu có email thật thì dùng, nếu không thì tạo email giả từ số điện thoại
        $emailToUse = $customer->email;
        $isGeneratedEmail = false;

        if (empty($emailToUse)) {
            // Tạo email giả từ số điện thoại
            $emailToUse = $customer->phone . '@customer.sgl.local';
            $isGeneratedEmail = true;
        }

        // Kiểm tra nếu email đã tồn tại trong hệ thống
        $existingUser = User::where('email', $emailToUse)->first();
        if ($existingUser) {
            if ($isGeneratedEmail) {
                return redirect()->route('customers.show', $id)
                    ->with('error', 'Số điện thoại này đã được sử dụng bởi một tài khoản khác.');
            } else {
                return redirect()->route('customers.show', $id)
                    ->with('error', 'Email này đã được sử dụng bởi một tài khoản khác. Vui lòng cập nhật email khác cho khách hàng trước khi kích hoạt.');
            }
        }

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $customer->toArray();

        // Tạo username từ công ty hoặc tên đại diện
        $baseUsername = Str::slug($customer->company_name) ?: Str::slug($customer->name);
        if (empty($baseUsername)) {
            $baseUsername = 'customer';
        }

        // Thêm số ngẫu nhiên để tránh trùng
        $username = $baseUsername . rand(1000, 9999);

        // Tạo mật khẩu ngẫu nhiên
        $password = Str::random(10);

        try {
            // Tạo tài khoản người dùng mới
            $user = User::create([
                'name' => $customer->name,
                'email' => $emailToUse,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'customer',
                'customer_id' => $customer->id,
                'active' => true
            ]);

            // Cập nhật trạng thái tài khoản và lưu thông tin đăng nhập
            $customer->update([
                'has_account' => true,
                'account_username' => $username,
                'account_password' => $password, // Lưu mật khẩu gốc (không phải đã hash)
                'is_locked' => false
            ]);

            // Ghi nhật ký kích hoạt tài khoản khách hàng
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'customer_account',
                    'Kích hoạt tài khoản cho khách hàng: ' . $customer->name . ' - ' . $customer->company_name,
                    $oldData,
                    [
                        'customer' => $customer->toArray(),
                        'user' => [
                            'id' => $user->id,
                            'username' => $username,
                            'email' => $emailToUse,
                            'is_generated_email' => $isGeneratedEmail
                        ]
                    ]
                );
            }

            // Gửi thông báo chào mừng
            \App\Models\Notification::createNotification(
                'Tài khoản đã được kích hoạt',
                'Chào mừng bạn đến với hệ thống. Tài khoản của bạn đã được kích hoạt thành công.',
                'success',
                $user->id,
                'user',
                $user->id,
                route('profile'),
                null,
                'customer'
            );

            return redirect()->route('customers.show', $id)
                ->with('success', "Tài khoản khách hàng đã được kích hoạt thành công!");
        } catch (\Exception $e) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể kích hoạt tài khoản: ' . $e->getMessage());
        }
    }

    /**
     * Xóa khách hàng khỏi database.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Kiểm tra xem khách hàng có tài khoản đang hoạt động không
        if ($customer->has_account && !$customer->is_locked) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì tài khoản đang hoạt động. Vui lòng khóa tài khoản trước khi xóa.');
        }

        // Kiểm tra xem khách hàng có dự án liên quan không
        if ($customer->projects()->count() > 0) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì có dự án liên quan. Vui lòng xóa dự án trước.');
        }

        // Kiểm tra xem khách hàng có phiếu cho thuê liên quan không
        if ($customer->rentals()->count() > 0) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì có phiếu cho thuê liên quan. Vui lòng xóa phiếu cho thuê trước.');
        }

        // Kiểm tra xem khách hàng có yêu cầu bảo trì liên quan không
        if (\App\Models\CustomerMaintenanceRequest::where('customer_id', $id)->count() > 0) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì có yêu cầu bảo trì liên quan. Vui lòng xóa yêu cầu bảo trì trước.');
        }

        // Lưu thông tin khách hàng trước khi xóa để ghi nhật ký
        $customerData = $customer->toArray();
        $customerName = $customer->name;
        $companyName = $customer->company_name;

        $customer->delete();

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'delete',
                'customers',
                'Xóa khách hàng: ' . $customerName . ' - ' . $companyName,
                $customerData,
                null
            );
        }

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được xóa thành công.');
    }

    /**
     * API - Lấy thông tin chi tiết của khách hàng.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInfo($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng'
            ], 404);
        }
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleLock(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Kiểm tra nếu chưa có tài khoản
        if (!$customer->has_account) {
            return redirect()->back()
                ->with('error', 'Khách hàng chưa có tài khoản nên không thể khóa/mở khóa.');
        }

        // Tìm tài khoản người dùng liên kết với khách hàng
        $user = User::where('customer_id', $customer->id)->first();

        if (!$user) {
            return redirect()->back()
                ->with('error', 'Không tìm thấy tài khoản người dùng liên kết với khách hàng này.');
        }

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = [
            'customer' => $customer->toArray(),
            'user' => $user->toArray()
        ];

        // Đảo trạng thái khóa tài khoản
        $isLocked = !($customer->is_locked ?? false);

        // Cập nhật trạng thái khóa trong bảng customers
        $customer->update([
            'is_locked' => $isLocked
        ]);

        // Cập nhật trạng thái kích hoạt trong bảng users
        $user->update([
            'active' => !$isLocked
        ]);

        $message = $isLocked
            ? 'Tài khoản khách hàng đã được khóa thành công.'
            : 'Tài khoản khách hàng đã được mở khóa thành công.';

        // Ghi nhật ký khóa/mở khóa tài khoản
        if (Auth::check()) {
            $action = $isLocked ? 'Khóa' : 'Mở khóa';
            UserLog::logActivity(
                Auth::id(),
                'update',
                'customer_account',
                $action . ' tài khoản khách hàng: ' . $customer->name . ' - ' . $customer->company_name,
                $oldData,
                [
                    'customer' => $customer->toArray(),
                    'user' => $user->toArray(),
                    'action' => $action
                ]
            );
        }

        // Nếu mở khóa, gửi thông báo
        if (!$isLocked) {
            \App\Models\Notification::createNotification(
                'Tài khoản đã được mở khóa',
                'Tài khoản của bạn đã được mở khóa. Bạn có thể truy cập lại vào hệ thống.',
                'success',
                $user->id,
                'user',
                $user->id,
                route('profile'),
                null,
                'customer'
            );
        }

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Export customers data as JSON with search filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        try {
            $search = $request->input('search');
            $filter = $request->input('filter');

            $query = Customer::select('id', 'name', 'company_name', 'phone', 'email', 'address', 'created_at');

            // Áp dụng bộ lọc tìm kiếm giống như trong phương thức index()
            if ($search) {
                if ($filter) {
                    // Tìm kiếm theo trường được chọn
                    switch ($filter) {
                        case 'name':
                            $query->where('name', 'like', "%{$search}%");
                            break;
                        case 'company_name':
                            $query->where('company_name', 'like', "%{$search}%");
                            break;
                        case 'phone':
                            $query->where('phone', 'like', "%{$search}%");
                            break;
                        case 'email':
                            $query->where('email', 'like', "%{$search}%");
                            break;
                        case 'address':
                            $query->where('address', 'like', "%{$search}%");
                            break;
                    }
                } else {
                    // Tìm kiếm tổng quát nếu không chọn bộ lọc
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('company_phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
                }
            }

            // Lấy dữ liệu đã được lọc
            $customers = $query->orderBy('id', 'asc')->get();

            // Format lại ngày tạo để hiển thị đẹp hơn
            $formattedCustomers = [];
            foreach ($customers as $customer) {
                $formattedCustomer = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'created_at' => $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : ''
                ];
                $formattedCustomers[] = $formattedCustomer;
            }

            // Trả về dữ liệu dưới dạng JSON
            return response()->json([
                'success' => true,
                'customers' => $formattedCustomers,
                'total_count' => $customers->count(),
                'search_applied' => !empty($search),
                'filter_applied' => $filter
            ]);
        } catch (\Exception $e) {
            // Log lỗi để debug
            \Illuminate\Support\Facades\Log::error('Export customers error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            // Trả về thông báo lỗi
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }
}
