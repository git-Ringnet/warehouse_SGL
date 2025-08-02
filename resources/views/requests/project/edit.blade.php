@extends('layouts.app')

@section('title', 'Chỉnh sửa phiếu đề xuất triển khai dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chỉnh sửa phiếu đề xuất triển khai dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $projectRequest->request_code }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm text-gray-500">Ngày tạo: {{ $projectRequest->request_date->format('d/m/Y') }}</span>
            </div>
            </div>
            <div class="flex space-x-2">
            <a href="{{ route('requests.project.show', $projectRequest->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-times mr-2"></i> Hủy
                </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-circle text-red-500"></i></div>
                <div class="ml-3">
                    <p class="font-medium">Vui lòng kiểm tra lại các thông tin sau:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('requests.project.update', $projectRequest->id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        @method('PATCH')
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', $projectRequest->request_date->format('Y-m-d')) }}">
                        </div>
                        <div>
                    <label for="technician" class="block text-sm font-medium text-gray-700 mb-1">Kỹ thuật đề xuất</label>
                    <input type="text" name="technician" id="technician" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100" value="{{ $projectRequest->proposer ? $projectRequest->proposer->name : '' }}" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_name', $projectRequest->project_name) }}">
                        </div>
                        <div>
                            <label for="partner" class="block text-sm font-medium text-gray-700 mb-1 required">Đối tác</label>
                    <input type="text" name="partner" id="partner" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('partner', $projectRequest->customer ? $projectRequest->customer->company_name : $projectRequest->customer_name) }}">
                        </div>
                        <div class="md:col-span-2">
                            <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                    <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_address', $projectRequest->project_address) }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Phương thức xử lý khi duyệt</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input type="radio" name="approval_method" id="production" value="production" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('approval_method', $projectRequest->approval_method) == 'production' ? 'checked' : '' }}>
                        <label for="production" class="ml-2 block text-sm font-medium text-gray-700">Sản xuất lắp ráp</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="approval_method" id="warehouse" value="warehouse" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('approval_method', $projectRequest->approval_method) == 'warehouse' ? 'checked' : '' }}>
                        <label for="warehouse" class="ml-2 block text-sm font-medium text-gray-700">Xuất kho</label>
                            </div>
                        </div>
                        <div class="col-span-2 mt-2">
                            <div id="production_info" class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm text-blue-700 {{ old('approval_method', $projectRequest->approval_method) == 'production' ? 'block' : 'hidden' }}">
                                <i class="fas fa-info-circle mr-1"></i> Khi chọn <strong>Sản xuất lắp ráp</strong>, hệ thống sẽ gửi thông báo đến nhân viên thực hiện để tạo phiếu lắp ráp sau khi phiếu được duyệt.
                            </div>
                            <div id="warehouse_info" class="p-3 bg-green-50 rounded-lg border border-green-200 text-sm text-green-700 {{ old('approval_method', $projectRequest->approval_method) == 'warehouse' ? 'block' : 'hidden' }}">
                                <i class="fas fa-info-circle mr-1"></i> Khi chọn <strong>Xuất kho</strong>, hệ thống sẽ gửi thông báo đến nhân viên thực hiện để tạo phiếu xuất kho sau khi phiếu được duyệt.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Danh mục đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center" id="equipment_radio">
                    <input type="radio" name="item_type" id="equipment_type" value="equipment" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', $projectRequest->items->where('item_type', 'equipment')->count() > 0 ? 'equipment' : 'equipment') == 'equipment' ? 'checked' : '' }}>
                    <label for="equipment_type" class="ml-2 block text-sm font-medium text-gray-700">Thành phẩm</label>
                </div>
                <div class="flex items-center" id="material_radio" style="display:none;">
                    <input type="radio" name="item_type" id="material_type" value="material" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', $projectRequest->items->where('item_type', 'material')->count() > 0 ? 'material' : '') == 'material' ? 'checked' : '' }}>
                    <label for="material_type" class="ml-2 block text-sm font-medium text-gray-700">Vật tư</label>
                </div>
                <div class="flex items-center" id="good_radio" style="display:none;">
                    <input type="radio" name="item_type" id="good_type" value="good" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', $projectRequest->items->where('item_type', 'good')->count() > 0 ? 'good' : '') == 'good' ? 'checked' : '' }}>
                    <label for="good_type" class="ml-2 block text-sm font-medium text-gray-700">Hàng hóa</label>
                </div>
            </div>
            
            <div id="equipment_section" class="item-section">
                    <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Thiết bị đề xuất</h3>
                        <button type="button" id="add_equipment" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm thiết bị
                        </button>
                    </div>
                    
                    <div id="equipment_container">
                        @php $equipmentIndex = 0; @endphp
                        @foreach($projectRequest->items->where('item_type', 'equipment') as $item)
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="equipment_id_{{ $equipmentIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị</label>
                            <select name="equipment[{{ $equipmentIndex }}][id]" id="equipment_id_{{ $equipmentIndex }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-select">
                                <option value="">-- Chọn thiết bị --</option>
                                @foreach($equipments ?? [] as $equipment)
                                    <option value="{{ $equipment->id }}" {{ $item->item_id == $equipment->id ? 'selected' : '' }}>
                                        {{ $equipment->name }} ({{ $equipment->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_{{ $equipmentIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="equipment[{{ $equipmentIndex }}][quantity]" id="equipment_quantity_{{ $equipmentIndex }}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-quantity" value="{{ $item->quantity }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        @php $equipmentIndex++; @endphp
                        @endforeach
                        @if($projectRequest->items->where('item_type', 'equipment')->count() == 0)
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="equipment_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị</label>
                            <select name="equipment[0][id]" id="equipment_id_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-select">
                                <option value="">-- Chọn thiết bị --</option>
                                @foreach($equipments ?? [] as $equipment)
                                    <option value="{{ $equipment->id }}" {{ old('equipment.0.id') == $equipment->id ? 'selected' : '' }}>
                                        {{ $equipment->name }} ({{ $equipment->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="equipment[0][quantity]" id="equipment_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-quantity" value="{{ old('equipment.0.quantity', 1) }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
            <div id="material_section" class="item-section hidden">
                    <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Vật tư đề xuất</h3>
                        <button type="button" id="add_material" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                        </button>
                    </div>
                    
                    <div id="material_container">
                        @php $materialIndex = 0; @endphp
                        @foreach($projectRequest->items->where('item_type', 'material') as $item)
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="material_id_{{ $materialIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                            <select name="material[{{ $materialIndex }}][id]" id="material_id_{{ $materialIndex }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn vật tư --</option>
                                @foreach($materials ?? [] as $material)
                                    <option value="{{ $material->id }}" {{ $item->item_id == $material->id ? 'selected' : '' }}>
                                        {{ $material->name }} ({{ $material->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_{{ $materialIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="material[{{ $materialIndex }}][quantity]" id="material_quantity_{{ $materialIndex }}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ $item->quantity }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        @php $materialIndex++; @endphp
                        @endforeach
                        @if($projectRequest->items->where('item_type', 'material')->count() == 0)
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="material_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                            <select name="material[0][id]" id="material_id_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn vật tư --</option>
                                @foreach($materials ?? [] as $material)
                                    <option value="{{ $material->id }}" {{ old('material.0.id') == $material->id ? 'selected' : '' }}>
                                        {{ $material->name }} ({{ $material->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="material[0][quantity]" id="material_quantity_0" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ old('material.0.quantity', 1) }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div id="good_section" class="item-section hidden">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Hàng hóa đề xuất</h3>
                    <button type="button" id="add_good" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-plus-circle mr-1"></i> Thêm hàng hóa
                    </button>
                </div>
                
                <div id="good_container">
                    @php $goodIndex = 0; @endphp
                    @foreach($projectRequest->items->where('item_type', 'good') as $item)
                    <div class="good-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                        <div class="md:col-span-3">
                            <label for="good_id_{{ $goodIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Hàng hóa</label>
                            <select name="good[{{ $goodIndex }}][id]" id="good_id_{{ $goodIndex }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn hàng hóa --</option>
                                @foreach($goods ?? [] as $good)
                                    <option value="{{ $good->id }}" {{ $item->item_id == $good->id ? 'selected' : '' }}>
                                        {{ $good->name }} ({{ $good->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label for="good_quantity_{{ $goodIndex }}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="good[{{ $goodIndex }}][quantity]" id="good_quantity_{{ $goodIndex }}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ $item->quantity }}">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                            </button>
                        </div>
                    </div>
                    @php $goodIndex++; @endphp
                    @endforeach
                    @if($projectRequest->items->where('item_type', 'good')->count() == 0)
                    <div class="good-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                        <div class="md:col-span-3">
                            <label for="good_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Hàng hóa</label>
                            <select name="good[0][id]" id="good_id_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn hàng hóa --</option>
                                @foreach($goods ?? [] as $good)
                                    <option value="{{ $good->id }}" {{ old('good.0.id') == $good->id ? 'selected' : '' }}>
                                        {{ $good->name }} ({{ $good->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label for="good_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="good[0][quantity]" id="good_quantity_0" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ old('good.0.quantity', 1) }}">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin liên hệ khách hàng</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng</label>
                    <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_name', $projectRequest->customer_name) }}">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại</label>
                    <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_phone', $projectRequest->customer_phone) }}">
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_email', $projectRequest->customer_email) }}">
                        </div>
                        <div class="md:col-span-3">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ</label>
                    <input type="text" name="customer_address" id="customer_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_address', $projectRequest->customer_address) }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $projectRequest->notes) }}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                <i class="fas fa-save mr-2"></i> Cập nhật
                    </button>
                </div>
            </form>
    </div>

<style>
    .required:after {
        content: " *";
        color: red;
    }
</style>

@section('scripts')
<script src="{{ asset('js/project-request-edit.js') }}"></script>
@endsection
@endsection 