<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
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
        
        // Kiểm tra xem người dùng có tồn tại không
        $employee = Employee::where('username', $credentials['username'])->first();
        if (!$employee) {
            Log::warning('Không tìm thấy người dùng với username: ' . $credentials['username']);
            return back()->withErrors([
                'username' => 'Không tìm thấy tài khoản với tên đăng nhập này.',
            ])->withInput($request->only('username'));
        }
        
        // Thử đăng nhập
        if (Auth::attempt($credentials)) {
            Log::info('Đăng nhập thành công với username: ' . $credentials['username']);
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Hiển thị trang profile
     */
    public function profile()
    {
        $employee = Auth::user();
        return view('auth.profile', compact('employee'));
    }
} 