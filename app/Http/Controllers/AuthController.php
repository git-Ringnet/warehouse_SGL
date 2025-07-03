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
            if ($employee->role === 'admin' || 
                ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview'))) {
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
        if ($employee && Hash::check($credentials['password'], $employee->password)) {
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
            if ($employee->role === 'admin' || 
                ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview'))) {
                return redirect()->intended('/dashboard');
            }
            return redirect('/');
        }
        
        // Nếu không thành công, thử đăng nhập với bảng users (khách hàng)
        $user = User::where('username', $credentials['username'])->first();
        if ($user && $user->role === 'customer') {
            if (Hash::check($credentials['password'], $user->password)) {
                Auth::guard('customer')->login($user);
                Log::info('Đăng nhập thành công với khách hàng: ' . $credentials['username']);
                
                // Lưu thông tin loại người dùng vào session
                Session::put('user_type', 'customer');
                
                // Ghi nhật ký đăng nhập
                $customer = Customer::find($user->customer_id);
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
                
                $request->session()->regenerate();
                return redirect()->intended('/customer/dashboard');
            }
        }
        
        // Kiểm tra xem người dùng có tồn tại không
        if (!$employee && !($user && $user->role === 'customer')) {
            Log::warning('Không tìm thấy người dùng với username: ' . $credentials['username']);
            return back()->withErrors([
                'username' => 'Không tìm thấy tài khoản với tên đăng nhập này.',
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
        
        // Ghi nhật ký đăng xuất trước khi logout
        if (Auth::check()) {
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
} 