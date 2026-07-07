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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50" x-data="{ tab: 'login', showSuccessModal: false }"
    @register-success.window="showSuccessModal = true; tab = 'login'">
    <div class="min-h-screen flex items-center justify-center relative">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-600 py-4">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-white">SGL - Hệ thống quản lý kho</h1>
                </div>
            </div>

            <div class="p-6">
                <!-- Tab Login -->
                <div x-show="tab === 'login'">
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
                            <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Tên đăng nhập /
                                Số
                                điện thoại</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="username" name="username" value="{{ old('username') }}"
                                    class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập tên đăng nhập hoặc số điện thoại" required autofocus>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Khách hàng có thể đăng nhập bằng số điện thoại</p>
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
                                <input type="checkbox" id="remember" name="remember"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember" class="ml-2 block text-sm text-gray-700">Ghi nhớ đăng nhập</label>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors">
                                Đăng nhập
                            </button>
                        </div>

                        <!-- Toggle Link to Register -->
                        <div class="mt-6 pt-6 border-t border-gray-100 text-center text-sm text-gray-600 hidden">
                            Chưa có tài khoản?
                            <a href="#" @click.prevent="tab = 'register'"
                                class="font-semibold text-blue-600 hover:text-blue-500 underline focus:outline-none">
                                Đăng ký trải nghiệm tại đây
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tab Register -->
                <div x-show="tab === 'register'" style="display: none;">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Đăng ký trải nghiệm</h2>
                    <form id="register-form" class="space-y-4">
                        <!-- Name (Username) -->
                        <div>
                            <label for="register_username" class="block text-gray-700 text-sm font-medium mb-2">Tên đăng
                                nhập</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="register_username" name="username" required
                                    class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập tên đăng nhập mong muốn" />
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <label for="register_email"
                                class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="register_email" name="email" required
                                    class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập địa chỉ email" />
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mt-4">
                            <label for="register_phone" class="block text-gray-700 text-sm font-medium mb-2">Số điện
                                thoại</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="tel" id="register_phone" name="phone" required
                                    class="pl-10 w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập số điện thoại liên hệ" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors">
                                Đăng ký
                            </button>
                        </div>

                        <!-- Toggle Link -->
                        <div class="mt-6 pt-6 border-t border-gray-100 text-center text-sm text-gray-600">
                            Đã có tài khoản?
                            <a href="#" id="toggle-to-login" @click.prevent="tab = 'login'"
                                class="font-semibold text-blue-600 hover:text-blue-500 underline focus:outline-none">
                                Đăng nhập tại đây
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-center text-sm text-gray-600">
                    &copy; {{ date('Y') }} SGL - Hệ thống quản lý kho
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all w-full max-w-md"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Success Icon -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>

                    <!-- Content -->
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Đăng ký thành công!</h3>
                        <p class="text-sm text-gray-600 mb-4">Thông tin đăng ký của bạn đã được ghi nhận. Vui lòng dùng
                            tài khoản demo dưới đây để trải nghiệm:</p>

                        <!-- Credentials Box -->
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-5 text-left space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Tên đăng nhập:</span>
                                <span class="text-gray-900 font-bold">admin</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Mật khẩu:</span>
                                <span class="text-gray-900 font-bold">password</span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-gray-200/50 pt-2">
                                <span class="text-gray-500 font-medium">Vai trò:</span>
                                <span class="text-green-600 font-semibold">Admin hệ thống</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 italic mb-6">
                            *(Hệ thống có sẵn tài khoản nhân viên dùng thử: <strong
                                class="text-gray-700">nhanvien</strong> / <strong
                                class="text-gray-700">password</strong>)
                        </p>
                    </div>

                    <!-- Button -->
                    <div>
                        <button type="button" @click="showSuccessModal = false"
                            class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Trải nghiệm ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const CONFIG = {
                hubUrl: "https://portal.app.ringnet.vn/api/demo-register",
                siteName: "Sài Gòn Lab"
            };

            const registerForm = document.getElementById("register-form");
            if (!registerForm) return;

            registerForm.addEventListener("submit", async (e) => {
                // 1. Ngăn chặn hành vi gửi form tải lại trang ban đầu
                e.preventDefault();

                const usernameInput = registerForm.querySelector('input[name="username"]') || registerForm.querySelector('#register_username') || registerForm.querySelector('input[name="name"]');
                const emailInput = registerForm.querySelector('input[name="email"]') || registerForm.querySelector('#register_email');
                const phoneInput = registerForm.querySelector('input[name="phone"]') || registerForm.querySelector('input[name="tel"]') || registerForm.querySelector('#register_phone');
                const submitButton = registerForm.querySelector('button[type="submit"]') || registerForm.querySelector('input[type="submit"]');

                if (!usernameInput || !emailInput || !phoneInput) {
                    alert("Không tìm thấy các trường thông tin đăng ký (Username, Email, Phone) trong form.");
                    return;
                }

                const payload = {
                    username: usernameInput.value.trim(),
                    email: emailInput.value.trim(),
                    phone: phoneInput.value.trim(),
                    site_name: CONFIG.siteName
                };

                const originalButtonHtml = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = "Đang xử lý...";

                try {
                    // 2. Gửi thông tin đăng ký lên Hub cha để ghi nhận log quan tâm
                    const response = await fetch(CONFIG.hubUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // 3. Tự động điền tài khoản demo vào form đăng nhập
                        const loginEmailInput = document.getElementById("username");
                        const loginPasswordInput = document.getElementById("password");
                        if (loginEmailInput) loginEmailInput.value = "admin";
                        if (loginPasswordInput) loginPasswordInput.value = "password";

                        // 4. Reset form đăng ký
                        registerForm.reset();

                        // 5. Gửi event báo thành công để AlpineJS bật modal và chuyển tab đăng nhập
                        window.dispatchEvent(new CustomEvent('register-success'));
                    } else {
                        alert(`Lỗi: ${result.message || "Đăng ký thất bại"}`);
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }
                } catch (error) {
                    console.error(error);
                    alert("Không thể kết nối đến hệ thống Hub trung tâm.");
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHtml;
                }
            });
        });
    </script>
</body>

</html>