<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Đăng nhập</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-600 py-4">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-white">SGL - Hệ thống quản lý kho</h1>
                </div>
            </div>
            
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Đăng nhập hệ thống</h2>
                
                @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Tên đăng nhập</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" 
                                class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Mật khẩu</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" 
                                class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors">
                            Đăng nhập
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-center text-sm text-gray-600">
                    &copy; {{ date('Y') }} SGL - Hệ thống quản lý kho
                </div>
            </div>
        </div>
    </div>
</body>

</html> 