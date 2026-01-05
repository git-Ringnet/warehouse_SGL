<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\UserLog;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        // Kiểm tra từng guard riêng biệt
        if (Auth::guard('web')->check()) {
            // Nếu là nhân viên, kiểm tra quyền trước khi chuyển hướng
            $employee = Auth::guard('web')->user();
            if (
                $employee->role === 'admin' ||
                ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview'))
            ) {
                return redirect('/dashboard');
            }
            return redirect('/'); // Chuyển về trang chủ nếu không có quyền
        }

        if (Auth::guard('customer')->check()) {
            // Khách hàng luôn được chuyển về trang chủ riêng
            return redirect('/customer/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Ghi log để debug
        Log::info('Đang thử đăng nhập với username: ' . $credentials['username']);

        // Thử đăng nhập với bảng employees (nhân viên)
        $employee = Employee::where('username', $credentials['username'])->first();
        if ($employee) {
            // Kiểm tra trạng thái tài khoản
            if (!$employee->is_active) {
                Log::warning('Tài khoản đã bị khóa: ' . $credentials['username']);
                return back()->withErrors([
                    'login_error' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ])->withInput($request->only('username'));
            }

            if (Hash::check($credentials['password'], $employee->password)) {
                Auth::guard('web')->login($employee);
                Log::info('Đăng nhập thành công với nhân viên: ' . $credentials['username']);

                // Lưu thông tin loại người dùng vào session
                Session::put('user_type', 'employee');

                // Ghi nhật ký đăng nhập
                UserLog::logActivity(
                    $employee->id,
                    'login',
                    'auth',
                    'Đăng nhập thành công (nhân viên)',
                    null,
                    ['username' => $employee->username, 'name' => $employee->name]
                );

                $request->session()->regenerate();

                // Kiểm tra quyền truy cập dashboard
                if (
                    $employee->role === 'admin' ||
                    ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview'))
                ) {
                    return redirect()->intended('/dashboard');
                }
                // Nếu nhân viên chưa có quyền, chuyển đến trang thông báo
                return view('errors.no-permission');
            }
        }

        // Nếu không thành công, thử đăng nhập với bảng users (khách hàng)
        // Hỗ trợ đăng nhập bằng username hoặc số điện thoại
        $user = null;
        $customer = null;
        $loginIdentifier = $credentials['username'];

        // Kiểm tra xem input có phải là số điện thoại không (chỉ chứa số và có 10-11 ký tự)
        $isPhoneNumber = preg_match('/^[0-9]{10,11}$/', $loginIdentifier);

        if ($isPhoneNumber) {
            // Tìm khách hàng theo số điện thoại
            $customer = Customer::where('phone', $loginIdentifier)->first();
            if ($customer && $customer->has_account) {
                // Tìm user liên kết với khách hàng này
                $user = User::where('customer_id', $customer->id)->where('role', 'customer')->first();
            }
        } else {
            // Tìm theo username
            $user = User::where('username', $loginIdentifier)->first();
            if ($user && $user->role === 'customer') {
                $customer = Customer::find($user->customer_id);
            }
        }

        if ($user && $user->role === 'customer') {
            // Kiểm tra trạng thái tài khoản khách hàng
            if ($customer && $customer->is_locked) {
                Log::warning('Tài khoản khách hàng đã bị khóa: ' . $loginIdentifier);
                return back()->withErrors([
                    'login_error' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ])->withInput($request->only('username'));
            }

            if (Hash::check($credentials['password'], $user->password)) {
                Auth::guard('customer')->login($user);
                Log::info('Đăng nhập thành công với khách hàng: ' . $loginIdentifier . ($isPhoneNumber ? ' (bằng số điện thoại)' : ''));

                // Lưu thông tin loại người dùng vào session
                Session::put('user_type', 'customer');

                // Ghi nhật ký đăng nhập - chỉ ghi cho employee
                if ($user->role !== 'customer') {
                    UserLog::logActivity(
                        $user->id,
                        'login',
                        'auth',
                        'Đăng nhập thành công (khách hàng)',
                        null,
                        [
                            'username' => $user->username,
                            'name' => $user->name,
                            'customer_name' => $customer ? $customer->name : null,
                            'customer_company' => $customer ? $customer->company_name : null
                        ]
                    );
                }

                $request->session()->regenerate();
                return redirect()->intended('/customer/dashboard');
            }
        }

        // Kiểm tra xem người dùng có tồn tại không
        if (!$employee && !($user && $user->role === 'customer')) {
            Log::warning('Không tìm thấy người dùng với username/phone: ' . $loginIdentifier);
            return back()->withErrors([
                'username' => 'Không tìm thấy tài khoản với tên đăng nhập hoặc số điện thoại này.',
            ])->withInput($request->only('username'));
        }

        Log::warning('Đăng nhập thất bại với username: ' . $credentials['username']);
        return back()->withErrors([
            'login_error' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ])->withInput($request->only('username'));
    }


    /**
     * Xử lý đăng xuất
     */
    public function logout(Request $request)
    {
        // Xác định guard đang sử dụng
        $userType = Session::get('user_type');

        // Ghi nhật ký đăng xuất trước khi logout - chỉ ghi cho employee
        if (Auth::check() && $userType !== 'customer') {
            $user = Auth::user();
            UserLog::logActivity(
                $user->id,
                'logout',
                'auth',
                'Đăng xuất thành công (' . ($userType === 'customer' ? 'khách hàng' : 'nhân viên') . ')',
                ['username' => $user->username, 'name' => $user->name],
                null
            );
        }

        if ($userType === 'customer') {
            Auth::guard('customer')->logout();
        } else {
            Auth::guard('web')->logout();
        }

        Session::forget('user_type');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Hiển thị trang profile
     */
    public function profile()
    {
        $userType = Session::get('user_type');

        // Kiểm tra loại người dùng để hiển thị view phù hợp
        if ($userType === 'employee') {
            $employee = Auth::guard('web')->user();
            if (!$employee) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập lại');
            }
            return view('auth.profile', ['employee' => $employee]);
        } else {
            // Đây là khách hàng
            $user = Auth::guard('customer')->user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập lại');
            }

            $customer = Customer::find($user->customer_id);
            if (!$customer) {
                Log::error('Không tìm thấy thông tin khách hàng cho user_id: ' . $user->id);
                return redirect()->route('dashboard')->with('error', 'Không tìm thấy thông tin khách hàng');
            }

            return view('auth.customer_profile', ['user' => $user, 'customer' => $customer]);
        }
    }

    /**
     * Cập nhật mật khẩu người dùng
     */
    public function updatePassword(Request $request)
    {
        $userType = Session::get('user_type');

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);

        try {
            // Cập nhật mật khẩu dựa trên loại người dùng
            if ($userType === 'employee') {
                $user = Auth::guard('web')->user();
                if (!$user) {
                    return redirect()->route('login')->with('error', 'Vui lòng đăng nhập lại');
                }

                // Kiểm tra mật khẩu hiện tại
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors([
                        'current_password' => 'Mật khẩu hiện tại không đúng',
                    ]);
                }

                DB::table('employees')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make($request->password)]);

                // Ghi nhật ký thay đổi mật khẩu
                UserLog::logActivity(
                    $user->id,
                    'update',
                    'auth',
                    'Thay đổi mật khẩu thành công (nhân viên)',
                    null,
                    ['username' => $user->username]
                );
            } else {
                $user = Auth::guard('customer')->user();
                if (!$user) {
                    return redirect()->route('login')->with('error', 'Vui lòng đăng nhập lại');
                }

                // Kiểm tra mật khẩu hiện tại
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors([
                        'current_password' => 'Mật khẩu hiện tại không đúng',
                    ]);
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make($request->password)]);

                // Cập nhật mật khẩu gốc trong bảng customers
                if ($user->role === 'customer' && $user->customer_id) {
                    DB::table('customers')
                        ->where('id', $user->customer_id)
                        ->update(['account_password' => $request->password]);
                }

                // Ghi nhật ký thay đổi mật khẩu
                UserLog::logActivity(
                    $user->id,
                    'update',
                    'auth',
                    'Thay đổi mật khẩu thành công (khách hàng)',
                    null,
                    ['username' => $user->username]
                );
            }

            return back()->with('success', 'Mật khẩu đã được cập nhật thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật mật khẩu: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật mật khẩu']);
        }
    }

    /**
     * API: Đăng nhập và trả về token
     */
    public function apiLogin(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ], [
                'username.required' => 'Vui lòng nhập tên đăng nhập.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
            ]);

            // Thử đăng nhập với nhân viên (Employee)
            $employee = Employee::where('username', $request->username)->first();

            if ($employee) {
                // Kiểm tra trạng thái tài khoản
                if (!$employee->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'
                    ], 403);
                }

                // Kiểm tra mật khẩu
                if (Hash::check($request->password, $employee->password)) {
                    // Tạo token
                    $token = $employee->createToken('api-token')->plainTextToken;

                    // Ghi nhật ký đăng nhập
                    UserLog::logActivity(
                        $employee->id,
                        'login',
                        'auth',
                        'Đăng nhập API thành công (nhân viên)',
                        null,
                        ['username' => $employee->username, 'name' => $employee->name]
                    );

                    return response()->json([
                        'success' => true,
                        'message' => 'Đăng nhập thành công',
                        'data' => [
                            'token' => $token,
                            'token_type' => 'Bearer',
                            'user' => [
                                'id' => $employee->id,
                                'username' => $employee->username,
                                'name' => $employee->name,
                                'email' => $employee->email,
                                'role' => $employee->role,
                                'type' => 'employee'
                            ]
                        ]
                    ]);
                }
            }

            // Thử đăng nhập với khách hàng (User)
            // Hỗ trợ đăng nhập bằng username hoặc số điện thoại
            $user = null;
            $customer = null;
            $loginIdentifier = $request->username;

            // Kiểm tra xem input có phải là số điện thoại không (chỉ chứa số và có 10-11 ký tự)
            $isPhoneNumber = preg_match('/^[0-9]{10,11}$/', $loginIdentifier);

            if ($isPhoneNumber) {
                // Tìm khách hàng theo số điện thoại
                $customer = Customer::where('phone', $loginIdentifier)->first();
                if ($customer && $customer->has_account) {
                    // Tìm user liên kết với khách hàng này
                    $user = User::where('customer_id', $customer->id)->where('role', 'customer')->first();
                }
            } else {
                // Tìm theo username
                $user = User::where('username', $loginIdentifier)->first();
                if ($user && $user->role === 'customer') {
                    $customer = Customer::find($user->customer_id);
                }
            }

            if ($user && $user->role === 'customer') {
                // Kiểm tra trạng thái tài khoản khách hàng
                if ($customer && $customer->is_locked) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'
                    ], 403);
                }

                // Kiểm tra mật khẩu
                if (Hash::check($request->password, $user->password)) {
                    // Tạo token
                    $token = $user->createToken('api-token')->plainTextToken;

                    return response()->json([
                        'success' => true,
                        'message' => 'Đăng nhập thành công' . ($isPhoneNumber ? ' (bằng số điện thoại)' : ''),
                        'data' => [
                            'token' => $token,
                            'token_type' => 'Bearer',
                            'user' => [
                                'id' => $user->id,
                                'username' => $user->username,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->role,
                                'customer_id' => $user->customer_id,
                                'type' => 'customer',
                                'phone' => $customer ? $customer->phone : null
                            ]
                        ]
                    ]);
                }
            }

            // Đăng nhập thất bại
            return response()->json([
                'success' => false,
                'message' => 'Tên đăng nhập/số điện thoại hoặc mật khẩu không chính xác.'
            ], 401);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đăng nhập: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Đăng xuất và xóa token
     */
    public function apiLogout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                // Xóa token hiện tại
                $request->user()->currentAccessToken()->delete();

                // Ghi nhật ký đăng xuất
                if ($user instanceof Employee) {
                    UserLog::logActivity(
                        $user->id,
                        'logout',
                        'auth',
                        'Đăng xuất API thành công (nhân viên)',
                        ['username' => $user->username, 'name' => $user->name],
                        null
                    );
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Đăng xuất thành công'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 401);

        } catch (\Exception $e) {
            Log::error('API logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đăng xuất: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy thông tin người dùng hiện tại
     */
    public function apiUser(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 401);
            }

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            if ($user instanceof Employee) {
                $userData['type'] = 'employee';
                $userData['phone'] = $user->phone;
                $userData['department'] = $user->department;
            } elseif ($user instanceof User && $user->role === 'customer') {
                $userData['type'] = 'customer';
                $userData['customer_id'] = $user->customer_id;
                if ($user->customer) {
                    $userData['customer'] = [
                        'id' => $user->customer->id,
                        'name' => $user->customer->name,
                        'company_name' => $user->customer->company_name,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $userData
            ]);

        } catch (\Exception $e) {
            Log::error('API user info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy thông tin hồ sơ người dùng hiện tại
     */
    public function apiProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'AUTH_001'
            ], 401);
        }

        if ($user instanceof User) {
            $user->loadMissing('customer');
        }

        return response()->json([
            'id' => $user->id,
            'fullname' => $user->name ?? $user->fullname ?? $user->username,
            'phone' => $this->resolveUserPhone($user),
            'email' => $user->email,
            'avatar_url' => $this->resolveAvatarUrl($user),
        ]);
    }

    /**
     * Xác định số điện thoại phù hợp cho phản hồi API profile
     */
    protected function resolveUserPhone($user): ?string
    {
        if (!empty($user->phone)) {
            return $user->phone;
        }

        if ($user instanceof User && $user->customer) {
            return $user->customer->phone ?? $user->customer->company_phone;
        }

        return null;
    }

    /**
     * Xây dựng URL avatar đầy đủ nếu có lưu trong hệ thống
     */
    protected function resolveAvatarUrl($user): ?string
    {
        $avatarPath = $user->avatar ?? null;

        if (!$avatarPath) {
            return null;
        }

        if (filter_var($avatarPath, FILTER_VALIDATE_URL)) {
            return $avatarPath;
        }

        if (Storage::disk('public')->exists($avatarPath)) {
            return asset(Storage::url($avatarPath));
        }

        return asset($avatarPath);
    }

    /**
     * API: Đổi mật khẩu
     * Route: PUT /api/user/change-password
     */
    public function apiChangePassword(Request $request)
    {
        try {
            // Validation
            $validator = \Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'new_password_confirmation' => 'required|string',
            ], [
                'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
                'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
                'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
                'new_password.confirmed' => 'Mật khẩu xác nhận không khớp.',
                'new_password_confirmation.required' => 'Vui lòng nhập lại mật khẩu mới.',
            ]);

            // Custom validation cho password complexity
            $validator->after(function ($validator) use ($request) {
                $password = $request->new_password;

                if ($password) {
                    if (!preg_match('/[a-z]/', $password)) {
                        $validator->errors()->add('new_password', 'Mật khẩu mới phải chứa ít nhất 1 ký tự thường.');
                    }
                    if (!preg_match('/[A-Z]/', $password)) {
                        $validator->errors()->add('new_password', 'Mật khẩu mới phải chứa ít nhất 1 ký tự in hoa.');
                    }
                    if (!preg_match('/[0-9]/', $password)) {
                        $validator->errors()->add('new_password', 'Mật khẩu mới phải chứa ít nhất 1 số.');
                    }
                    if (!preg_match('/[@$!%*#?&]/', $password)) {
                        $validator->errors()->add('new_password', 'Mật khẩu mới phải chứa ít nhất 1 ký tự đặc biệt (@$!%*#?&).');
                    }
                }
            });

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.'
                ], 401);
            }

            // Kiểm tra mật khẩu hiện tại
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không chính xác.'
                ], 400);
            }

            // Kiểm tra mật khẩu mới không trùng với mật khẩu cũ
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.'
                ], 400);
            }

            // Cập nhật mật khẩu
            if ($user instanceof Employee) {
                // Cập nhật cho nhân viên
                DB::table('employees')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make($request->new_password)]);

                // Ghi log
                UserLog::logActivity(
                    $user->id,
                    'update',
                    'auth',
                    'Đổi mật khẩu thành công qua API (nhân viên)',
                    null,
                    ['username' => $user->username]
                );
            } elseif ($user instanceof User && $user->role === 'customer') {
                // Cập nhật cho khách hàng
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make($request->new_password)]);

                // Cập nhật mật khẩu gốc trong bảng customers
                if ($user->customer_id) {
                    DB::table('customers')
                        ->where('id', $user->customer_id)
                        ->update(['account_password' => $request->new_password]);
                }

                // Ghi log
                UserLog::logActivity(
                    $user->id,
                    'update',
                    'auth',
                    'Đổi mật khẩu thành công qua API (khách hàng)',
                    null,
                    ['username' => $user->username]
                );
            }

            // Xóa tất cả token hiện tại để bắt buộc đăng nhập lại
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API change password error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đổi mật khẩu: ' . $e->getMessage()
            ], 500);
        }
    }
}