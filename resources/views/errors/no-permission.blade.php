@extends('layouts.app')

@section('styles')
<style>
    .error-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        padding: 20px;
    }
    
    .error-container {
        width: 100%;
        max-width: 500px;
        margin: auto;
    }
    
    .error-card {
        background: white;
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transform: translateY(0);
        transition: all 0.3s ease;
    }
    
    .error-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .error-header {
        background: linear-gradient(45deg, #ff416c 0%, #ff4b2b 100%);
        padding: 25px 20px;
        text-align: center;
    }
    
    .error-header h5 {
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .error-body {
        padding: 40px 30px;
        background: white;
    }
    
    .lock-icon {
        display: inline-block;
        font-size: 4.5rem;
        color: #ff416c;
        margin-bottom: 25px;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-15px);
        }
        100% {
            transform: translateY(0px);
        }
    }
    
    .error-title {
        color: #2d3436;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .error-message {
        color: #636e72;
        font-size: 1.1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .contact-info {
        background: rgba(245, 246, 250, 0.8);
        padding: 25px;
        border-radius: 15px;
        margin: 30px 0;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .contact-info h5 {
        color: #2d3436;
        font-size: 1.2rem;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .contact-info p {
        margin: 15px 0;
        color: #2d3436;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
    }
    
    .contact-info i {
        width: 30px;
        color: #ff416c;
        font-size: 1.2rem;
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-custom {
        padding: 12px 25px;
        font-weight: 500;
        border-radius: 50px;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-retry {
        background: linear-gradient(45deg, #ff416c 0%, #ff4b2b 100%);
        border: none;
        color: white;
        min-width: 200px;
    }
    
    .btn-retry:hover {
        background: linear-gradient(45deg, #ff4b2b 0%, #ff416c 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        color: white;
    }
    
    .btn-logout {
        background: transparent;
        border: 2px solid #ff416c;
        color: #ff416c;
        min-width: 140px;
    }
    
    .btn-logout:hover {
        background: rgba(255, 65, 108, 0.1);
        color: #ff416c;
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="error-page">
    <div class="error-container">
        <div class="error-card">
            <div class="error-header">
                <h5>Không có quyền truy cập</h5>
            </div>

            <div class="error-body text-center">
                <i class="fas fa-lock lock-icon"></i>
                <h4 class="error-title">Tài khoản của bạn chưa được phân quyền</h4>
                <p class="error-message">Vui lòng liên hệ Admin để được cấp quyền truy cập hệ thống.</p>

                <div class="contact-info">
                    <h5>Liên hệ hỗ trợ</h5>
                    <p><i class="fas fa-envelope"></i> admin@example.com</p>
                    <p><i class="fas fa-phone"></i> (028) 1234 56789</p>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('dashboard') }}" class="btn btn-custom btn-retry">
                        <i class="fas fa-home"></i> Thử lại truy cập Dashboard
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-custom btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 