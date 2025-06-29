@extends('layouts.app')

@section('title', 'Không có quyền truy cập - 403')

@section('content')
<div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 mb-6">
                    <i class="fas fa-ban text-red-600 text-4xl"></i>
                </div>
                
                <h1 class="text-4xl font-bold text-gray-900 mb-4">403</h1>
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Không có quyền truy cập</h2>
                
                <p class="text-gray-600 mb-6">
                    {{ $exception->getMessage() ?: 'Bạn không có quyền truy cập trang này. Vui lòng liên hệ quản trị viên để được cấp quyền.' }}
                </p>
                
                <div class="space-y-4">
                    <a href="{{ route('dashboard') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-home mr-2"></i>
                        Quay về trang chủ
                    </a>
                    
                    <button onclick="history.back()" 
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Quay lại trang trước
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Nếu bạn tin rằng đây là lỗi, vui lòng liên hệ quản trị viên hệ thống.
            </p>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .content-area {
        margin-left: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Hide sidebar for error page */
    .sidebar {
        display: none;
    }
    
    /* Hide header for error page */
    header {
        display: none;
    }
</style>
@endsection 