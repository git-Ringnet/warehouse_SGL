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
                    <input type="text" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 date-input" value="{{ old('request_date', $projectRequest->request_date->format('d/m/Y')) }}">
                        </div>
                        <div id="proposer_section">
                    <label for="technician" class="block text-sm font-medium text-gray-700 mb-1">Kỹ thuật đề xuất</label>
                    <input type="text" name="technician" id="technician" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100" value="{{ $projectRequest->proposer ? $projectRequest->proposer->name : '' }}" readonly>
                        </div>
                    </div>
                  
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự án / Phiếu cho thuê</label>
                            <select name="project_id" id="project_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn dự án / phiếu cho thuê --</option>
                                <optgroup label="Dự án">
                                    @foreach($projects as $project)
                                        <option value="project_{{ $project->id }}"
                                                data-type="project"
                                                data-customer-id="{{ $project->customer->id }}"
                                                data-customer-name="{{ $project->customer->name }}"
                                                data-customer-phone="{{ $project->customer->phone }}"
                                                data-customer-email="{{ $project->customer->email }}"
                                                data-customer-address="{{ $project->customer->address }}"
                                                data-project-name="{{ $project->project_name }}"
                                                data-project-address="{{ $project->project_address ?? '' }}"
                                                {{ old('project_id', $projectRequest->project_id ? 'project_'.$projectRequest->project_id : ($projectRequest->rental_id ? 'rental_'.$projectRequest->rental_id : '')) == 'project_' . $project->id ? 'selected' : '' }}>
                                            {{ $project->project_name }} ({{ $project->project_code }})
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Phiếu cho thuê">
                                    @foreach($rentals as $rental)
                                        <option value="rental_{{ $rental->id }}"
                                                data-type="rental"
                                                data-customer-id="{{ $rental->customer->id }}"
                                                data-customer-name="{{ $rental->customer->name }}"
                                                data-customer-phone="{{ $rental->customer->phone }}"
                                                data-customer-email="{{ $rental->customer->email }}"
                                                data-customer-address="{{ $rental->customer->address }}"
                                                data-project-name="{{ $rental->rental_name }}"
                                                data-project-address="{{ $rental->rental_address ?? '' }}"
                                                {{ old('project_id', $projectRequest->project_id ? 'project_'.$projectRequest->project_id : ($projectRequest->rental_id ? 'rental_'.$projectRequest->rental_id : '')) == 'rental_' . $rental->id ? 'selected' : '' }}>
                                            {{ $rental->rental_name }} ({{ $rental->rental_code }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <input type="hidden" name="project_name" id="project_name" value="{{ old('project_name', $projectRequest->project_name) }}">
                            <input type="hidden" name="project_type" id="project_type" value="{{ $projectRequest->project_id ? 'project' : ($projectRequest->rental_id ? 'rental' : '') }}">
                        </div>
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Đối tác</label>
                            <select name="customer_id" id="customer_id" readonly disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
                                <option value="">-- Đối tác sẽ được tự động điền --</option>
                            </select>
                        </div>
                    </div>
                    <div id="customer_details" class="md:col-span-2 border border-gray-200 rounded-lg p-4 bg-gray-50 mt-4">
                        <h3 class="text-md font-medium text-gray-800 mb-2">Thông tin đối tác</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Tên người liên hệ:</span>
                                <p id="customer_name_display" class="font-medium text-gray-700"></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Số điện thoại:</span>
                                <p id="customer_phone_display" class="font-medium text-gray-700"></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Email:</span>
                                <p id="customer_email_display" class="font-medium text-gray-700"></p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-sm text-gray-500">Địa chỉ:</span>
                                <p id="customer_address_display" class="font-medium text-gray-700"></p>
                            </div>
                        </div>
                        <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name', $projectRequest->customer_name) }}">
                        <input type="hidden" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $projectRequest->customer_phone) }}">
                        <input type="hidden" name="customer_email" id="customer_email" value="{{ old('customer_email', $projectRequest->customer_email) }}">
                        <input type="hidden" name="customer_address" id="customer_address" value="{{ old('customer_address', $projectRequest->customer_address) }}">
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
                <div class="flex items-center" id="material_radio">
                    <input type="radio" name="item_type" id="material_type" value="material" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', $projectRequest->items->where('item_type', 'material')->count() > 0 ? 'material' : '') == 'material' ? 'checked' : '' }}>
                    <label for="material_type" class="ml-2 block text-sm font-medium text-gray-700">Vật tư</label>
                </div>
                <div class="flex items-center" id="good_radio">
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
<script>
    // Đồng bộ project dropdown -> project_name + customer hiển thị (giống create)
    document.addEventListener('DOMContentLoaded', function() {
        const projectSelect = document.getElementById('project_id');
        const customerSelect = document.getElementById('customer_id');
        const projectNameInput = document.getElementById('project_name');
        const projectTypeInput = document.getElementById('project_type');

        function updateProjectAndCustomer() {
            const selectedOption = projectSelect.options[projectSelect.selectedIndex];
            if (!selectedOption) return;

            const name = selectedOption.getAttribute('data-project-name');
            const type = selectedOption.getAttribute('data-type');
            const customerId = selectedOption.getAttribute('data-customer-id');
            const customerName = selectedOption.getAttribute('data-customer-name');
            const customerPhone = selectedOption.getAttribute('data-customer-phone');
            const customerEmail = selectedOption.getAttribute('data-customer-email');
            const customerAddress = selectedOption.getAttribute('data-customer-address');

            if (projectNameInput) projectNameInput.value = name || '';
            if (projectTypeInput) projectTypeInput.value = type || '';

            // Cập nhật đối tác readonly select
            customerSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = customerId || '';
            option.textContent = customerName || '';
            customerSelect.appendChild(option);

            // Hiển thị chi tiết đối tác như create
            document.getElementById('customer_name_display').textContent = customerName || 'N/A';
            document.getElementById('customer_phone_display').textContent = customerPhone || 'N/A';
            document.getElementById('customer_email_display').textContent = customerEmail || 'N/A';
            document.getElementById('customer_address_display').textContent = customerAddress || 'N/A';

            // Đồng bộ hidden inputs
            const nameHidden = document.getElementById('customer_name');
            const phoneHidden = document.getElementById('customer_phone');
            const emailHidden = document.getElementById('customer_email');
            const addressHidden = document.getElementById('customer_address');
            if (nameHidden) nameHidden.value = customerName || '';
            if (phoneHidden) phoneHidden.value = customerPhone || '';
            if (emailHidden) emailHidden.value = customerEmail || '';
            if (addressHidden) addressHidden.value = customerAddress || '';
        }

        if (projectSelect) {
            projectSelect.addEventListener('change', updateProjectAndCustomer);
            updateProjectAndCustomer();
        }
    });

    // Xử lý hiển thị thông tin phương thức xử lý
    document.querySelectorAll('input[name="approval_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Ẩn tất cả các thông tin
            document.getElementById('production_info').classList.add('hidden');
            document.getElementById('warehouse_info').classList.add('hidden');
            
            // Hiển thị thông tin tương ứng
            if (this.value === 'production') {
                document.getElementById('production_info').classList.remove('hidden');
                // Hiển thị trường "Kỹ thuật đề xuất" khi chọn "Sản xuất lắp ráp"
                document.getElementById('proposer_section').style.display = 'block';
                // Chỉ hiển thị radio "Thành phẩm" khi chọn "Sản xuất lắp ráp"
                document.getElementById('equipment_radio').style.display = 'flex';
                document.getElementById('material_radio').style.display = 'none';
                document.getElementById('good_radio').style.display = 'none';
                // Tự động chọn "Thành phẩm"
                document.getElementById('equipment_type').checked = true;
                // Kích hoạt sự kiện change để hiển thị section thành phẩm
                document.getElementById('equipment_type').dispatchEvent(new Event('change'));
            } else if (this.value === 'warehouse') {
                document.getElementById('warehouse_info').classList.remove('hidden');
                // Ẩn trường "Kỹ thuật đề xuất" khi chọn "Xuất kho"
                document.getElementById('proposer_section').style.display = 'none';
                // Hiển thị radio "Thành phẩm" và "Hàng hóa" khi chọn "Xuất kho", ẩn "Vật tư"
                document.getElementById('equipment_radio').style.display = 'flex';
                document.getElementById('material_radio').style.display = 'none';
                document.getElementById('good_radio').style.display = 'flex';
                // Tự động chọn "Thành phẩm" nếu chưa có gì được chọn
                if (!document.querySelector('input[name="item_type"]:checked')) {
                    document.getElementById('equipment_type').checked = true;
                    document.getElementById('equipment_type').dispatchEvent(new Event('change'));
                }
            }
        });
    });
    
    // Khởi tạo trạng thái ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        const productionRadio = document.getElementById('production');
        const warehouseRadio = document.getElementById('warehouse');
        
        if (productionRadio.checked) {
            // Nếu mặc định chọn "Sản xuất lắp ráp"
            document.getElementById('proposer_section').style.display = 'block';
            document.getElementById('equipment_radio').style.display = 'flex';
            document.getElementById('material_radio').style.display = 'none';
            document.getElementById('good_radio').style.display = 'none';
            document.getElementById('equipment_type').checked = true;
        } else if (warehouseRadio.checked) {
            // Nếu mặc định chọn "Xuất kho"
            document.getElementById('proposer_section').style.display = 'none';
            document.getElementById('equipment_radio').style.display = 'flex';
            document.getElementById('material_radio').style.display = 'none';
            document.getElementById('good_radio').style.display = 'flex';
            // Tự động chọn "Thành phẩm" nếu chưa có gì được chọn
            if (!document.querySelector('input[name="item_type"]:checked')) {
                document.getElementById('equipment_type').checked = true;
            }
        }
    });
</script>
@endsection
@endsection 