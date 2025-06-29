@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-red-500 text-white p-6 text-center">
            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
            <h1 class="text-xl font-bold">Không có quyền truy cập</h1>
        </div>
        
        <div class="p-6 text-center">
            <p class="text-gray-600 mb-4">
                Tài khoản của bạn chưa được cấp quyền truy cập vào bất kỳ chức năng nào trong hệ thống.
            </p>
            
            <p class="text-gray-600 mb-6">
                Vui lòng liên hệ với quản trị viên để được cấp quyền phù hợp.
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('profile') }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-200">
                    <i class="fas fa-user mr-2"></i>
                    Xem thông tin cá nhân
                </a>
                
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 