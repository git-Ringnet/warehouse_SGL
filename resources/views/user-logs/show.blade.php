@extends('layouts.app')

@section('title', 'Chi tiết nhật ký người dùng')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Chi tiết nhật ký người dùng</h1>
        <div>
            <a href="{{ route('user-logs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Thông tin cơ bản</h2>
                    <table class="w-full">
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium w-1/3">ID:</td>
                            <td class="py-2 text-gray-800">{{ $log->id }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Người dùng:</td>
                            <td class="py-2 text-gray-800">
                                @if($log->user)
                                    {{ $log->user->name }} ({{ $log->user->username }})
                                @else
                                    <span class="text-gray-400">Không xác định</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Hành động:</td>
                            <td class="py-2">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    @if($log->action == 'login') bg-green-100 text-green-800
                                    @elseif($log->action == 'logout') bg-purple-100 text-purple-800
                                    @elseif($log->action == 'create') bg-blue-100 text-blue-800
                                    @elseif($log->action == 'update') bg-yellow-100 text-yellow-800
                                    @elseif($log->action == 'delete') bg-red-100 text-red-800
                                    @elseif($log->action == 'export') bg-indigo-100 text-indigo-800
                                    @elseif($log->action == 'import') bg-emerald-100 text-emerald-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Module:</td>
                            <td class="py-2 text-gray-800">{{ $log->module }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Mô tả:</td>
                            <td class="py-2 text-gray-800">{{ $log->description }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Địa chỉ IP:</td>
                            <td class="py-2 text-gray-800">{{ $log->ip_address }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-600 font-medium">Trình duyệt:</td>
                            <td class="py-2 text-gray-800 break-all">{{ $log->user_agent }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600 font-medium">Thời gian:</td>
                            <td class="py-2 text-gray-800">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-4">Dữ liệu chi tiết</h2>
                    
                    @if($log->old_data)
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Dữ liệu cũ:</h3>
                            <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                                <pre class="text-sm text-gray-800">{{ json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                    
                    @if($log->new_data)
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Dữ liệu mới:</h3>
                            <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                                <pre class="text-sm text-gray-800">{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                    
                    @if(!$log->old_data && !$log->new_data)
                        <div class="text-gray-500 italic">Không có dữ liệu chi tiết</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 