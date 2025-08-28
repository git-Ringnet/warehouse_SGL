// Xử lý hiển thị section theo loại item được chọn
document.querySelectorAll('input[name="item_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Ẩn tất cả các section
        document.querySelectorAll('.item-section').forEach(section => {
            section.classList.add('hidden');
            
            // Tắt required cho các trường trong section ẩn
            section.querySelectorAll('[required]').forEach(field => {
                field.removeAttribute('required');
            });
        });
        
        // Hiển thị section tương ứng
        const selectedType = this.value;
        const selectedSection = document.getElementById(selectedType + '_section');
        selectedSection.classList.remove('hidden');
        
        // Bật required cho các trường trong section hiển thị
        selectedSection.querySelectorAll('.required-field').forEach(field => {
            field.setAttribute('required', 'required');
        });
    });
});

// Xử lý hiển thị thông tin phương thức xử lý
document.querySelectorAll('input[name="approval_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Ẩn tất cả các thông tin
        const productionInfo = document.getElementById('production_info');
        const warehouseInfo = document.getElementById('warehouse_info');
        
        if (productionInfo) productionInfo.classList.add('hidden');
        if (warehouseInfo) warehouseInfo.classList.add('hidden');
        
        // Hiển thị thông tin tương ứng
        if (this.value === 'production') {
            if (productionInfo) productionInfo.classList.remove('hidden');
            // Chỉ hiển thị radio "Thành phẩm" khi chọn "Sản xuất lắp ráp"
            const equipmentRadio = document.getElementById('equipment_radio');
            const materialRadio = document.getElementById('material_radio');
            const goodRadio = document.getElementById('good_radio');
            
            if (equipmentRadio) equipmentRadio.style.display = 'flex';
            if (materialRadio) materialRadio.style.display = 'none';
            if (goodRadio) goodRadio.style.display = 'none';
            
            // Tự động chọn "Thành phẩm"
            const equipmentType = document.getElementById('equipment_type');
            if (equipmentType) {
                equipmentType.checked = true;
                // Kích hoạt sự kiện change để hiển thị section thành phẩm
                equipmentType.dispatchEvent(new Event('change'));
            }
        } else if (this.value === 'warehouse') {
            if (warehouseInfo) warehouseInfo.classList.remove('hidden');
            // Hiển thị đầy đủ 3 radio khi chọn "Xuất kho"
            const equipmentRadio = document.getElementById('equipment_radio');
            const materialRadio = document.getElementById('material_radio');
            const goodRadio = document.getElementById('good_radio');
            
            if (equipmentRadio) equipmentRadio.style.display = 'flex';
            if (materialRadio) materialRadio.style.display = 'flex';
            if (goodRadio) goodRadio.style.display = 'flex';
        }
    });
});

// Kiểm tra tồn kho khi chọn item
function checkStock(itemType, itemId, selectElement) {
    if (!itemId) return;
    
    fetch(`/api/check-stock/${itemType}/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.has_stock) {
                    // Hiển thị thông tin tồn kho
                    const stockInfo = data.warehouses.map(w => 
                        `${w.warehouse_name}: ${w.quantity}`
                    ).join(', ');
                    
                    // Tạo thông báo thành công
                    const successMsg = `✅ Đã chọn thành công: ${data.item_name} (${data.item_code})\n📦 Tổng tồn kho: ${data.total_stock}\n🏢 Kho: ${stockInfo}`;
                    
                    // Hiển thị thông báo
                    showNotification(successMsg, 'success');
                    
                    // Thêm class thành công cho select
                    selectElement.classList.add('border-green-500');
                    selectElement.classList.remove('border-red-500');
                } else {
                    // Thông báo không đủ tồn kho
                    const errorMsg = `❌ Không đủ tồn kho cho: ${data.item_name} (${data.item_code})\n📦 Tổng tồn kho: ${data.total_stock}`;
                    
                    showNotification(errorMsg, 'error');
                    
                    // Reset select và thêm class lỗi
                    selectElement.value = '';
                    selectElement.classList.add('border-red-500');
                    selectElement.classList.remove('border-green-500');
                }
            } else {
                showNotification('❌ Lỗi khi kiểm tra tồn kho', 'error');
            }
        })
        .catch(error => {
            console.error('Error checking stock:', error);
            showNotification('❌ Lỗi khi kiểm tra tồn kho', 'error');
        });
}

// Hiển thị thông báo
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Lấy item type từ select
function getItemTypeFromSelect(selectElement) {
    const name = selectElement.name;
    if (name.includes('equipment')) return 'product';
    if (name.includes('material')) return 'material';
    if (name.includes('good')) return 'good';
    return 'product';
}

// Hàm populate dropdown với dữ liệu từ server
function populateDropdown(selectElement, itemType) {
    // Lấy dữ liệu từ các select có sẵn để copy options
    let sourceSelect = null;
    
    if (itemType === 'equipment') {
        sourceSelect = document.querySelector('select[name="equipment[0][id]"]');
    } else if (itemType === 'material') {
        sourceSelect = document.querySelector('select[name="material[0][id]"]');
    } else if (itemType === 'good') {
        sourceSelect = document.querySelector('select[name="good[0][id]"]');
    }
    
    if (sourceSelect) {
        // Copy tất cả options từ source select
        Array.from(sourceSelect.options).forEach(option => {
            const newOption = option.cloneNode(true);
            selectElement.appendChild(newOption);
        });
    }
}

// Xử lý thêm/xóa rows cho equipment
let equipmentIndex = 1;

document.addEventListener('DOMContentLoaded', function() {
    const addEquipmentBtn = document.getElementById('add_equipment');
    if (addEquipmentBtn) {
        addEquipmentBtn.addEventListener('click', function() {
            const container = document.getElementById('equipment_container');
            if (!container) return;
            
            const newRow = document.createElement('div');
            newRow.className = 'equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="equipment_id_${equipmentIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị</label>
                    <select name="equipment[${equipmentIndex}][id]" id="equipment_id_${equipmentIndex}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-select">
                        <option value="">-- Chọn thiết bị --</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="equipment_quantity_${equipmentIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="equipment[${equipmentIndex}][quantity]" id="equipment_quantity_${equipmentIndex}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-quantity" value="1">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            
            // Populate dropdown với dữ liệu
            const newSelect = newRow.querySelector('select');
            populateDropdown(newSelect, 'equipment');
            
            equipmentIndex++;
            
            // Hiển thị nút xóa cho tất cả rows
            updateRemoveButtons();
        });
    }
    
    // Xử lý thêm/xóa rows cho material
    let materialIndex = 1;
    const addMaterialBtn = document.getElementById('add_material');
    if (addMaterialBtn) {
        addMaterialBtn.addEventListener('click', function() {
            const container = document.getElementById('material_container');
            if (!container) return;
            
            const newRow = document.createElement('div');
            newRow.className = 'material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="material_id_${materialIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                    <select name="material[${materialIndex}][id]" id="material_id_${materialIndex}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                        <option value="">-- Chọn vật tư --</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="material_quantity_${materialIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="material[${materialIndex}][quantity]" id="material_quantity_${materialIndex}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="1">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            
            // Populate dropdown với dữ liệu
            const newSelect = newRow.querySelector('select');
            populateDropdown(newSelect, 'material');
            
            materialIndex++;
            
            // Hiển thị nút xóa cho tất cả rows
            updateRemoveButtons();
        });
    }
    
    // Xử lý thêm/xóa rows cho good
    let goodIndex = 1;
    const addGoodBtn = document.getElementById('add_good');
    if (addGoodBtn) {
        addGoodBtn.addEventListener('click', function() {
            const container = document.getElementById('good_container');
            if (!container) return;
            
            const newRow = document.createElement('div');
            newRow.className = 'good-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="good_id_${goodIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Hàng hóa</label>
                    <select name="good[${goodIndex}][id]" id="good_id_${goodIndex}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                        <option value="">-- Chọn hàng hóa --</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label for="good_quantity_${goodIndex}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="good[${goodIndex}][quantity]" id="good_quantity_${goodIndex}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="1">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            
            // Populate dropdown với dữ liệu
            const newSelect = newRow.querySelector('select');
            populateDropdown(newSelect, 'good');
            
            goodIndex++;
            
            // Hiển thị nút xóa cho tất cả rows
            updateRemoveButtons();
        });
    }
    
    // Xử lý xóa row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('.equipment-row, .material-row, .good-row');
            if (row) {
                row.remove();
                updateRemoveButtons();
            }
        }
    });
    
    // Thêm event listener cho các select items - chỉ kiểm tra tồn kho khi chọn "Xuất kho"
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[id]"]')) {
            const itemType = getItemTypeFromSelect(e.target);
            const itemId = e.target.value;
            
            // Chỉ kiểm tra tồn kho khi chọn "Xuất kho"
            const approvalMethod = document.querySelector('input[name="approval_method"]:checked');
            if (approvalMethod && approvalMethod.value === 'warehouse' && itemId) {
                checkStock(itemType, itemId, e.target);
            } else if (itemId) {
                // Nếu không phải "Xuất kho", chỉ hiển thị thông báo chọn thành công
                const selectElement = e.target;
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                if (selectedOption && selectedOption.text !== '-- Chọn thiết bị --' && selectedOption.text !== '-- Chọn vật tư --' && selectedOption.text !== '-- Chọn hàng hóa --') {
                    showNotification(`✅ Đã chọn thành công: ${selectedOption.text}`, 'success');
                    selectElement.classList.add('border-green-500');
                    selectElement.classList.remove('border-red-500');
                }
            }
        }
    });
    
    // Hàm cập nhật hiển thị nút xóa
    function updateRemoveButtons() {
        const sections = ['equipment', 'material', 'good'];
        sections.forEach(section => {
            const container = document.getElementById(section + '_container');
            if (!container) return;
            
            const rows = container.querySelectorAll('.' + section + '-row');
            
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-row');
                if (rows.length === 1) {
                    removeBtn.classList.add('invisible');
                } else {
                    removeBtn.classList.remove('invisible');
                }
            });
        });
    }
    
    // Khởi tạo khi trang load
    updateRemoveButtons();
    
    // Kích hoạt sự kiện change cho approval_method để hiển thị đúng radio buttons
    const approvalMethod = document.querySelector('input[name="approval_method"]:checked');
    if (approvalMethod) {
        approvalMethod.dispatchEvent(new Event('change'));
    }
    
    // Kích hoạt sự kiện change cho item_type để hiển thị đúng section
    const itemType = document.querySelector('input[name="item_type"]:checked');
    if (itemType) {
        itemType.dispatchEvent(new Event('change'));
    }
    
    // Thêm event listener cho approval_method để cập nhật kiểm tra tồn kho
    document.querySelectorAll('input[name="approval_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Cập nhật lại tất cả các select items đã chọn
            const selectedItems = document.querySelectorAll('select[name*="[id]"]');
            selectedItems.forEach(select => {
                if (select.value) {
                    const itemType = getItemTypeFromSelect(select);
                    const itemId = select.value;
                    
                    if (this.value === 'warehouse') {
                        // Kiểm tra tồn kho khi chuyển sang "Xuất kho"
                        checkStock(itemType, itemId, select);
                    } else {
                        // Chỉ hiển thị thông báo chọn thành công khi chuyển sang "Sản xuất lắp ráp"
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.text !== '-- Chọn thiết bị --' && selectedOption.text !== '-- Chọn vật tư --' && selectedOption.text !== '-- Chọn hàng hóa --') {
                            showNotification(`✅ Đã chọn thành công: ${selectedOption.text}`, 'success');
                            select.classList.add('border-green-500');
                            select.classList.remove('border-red-500');
                        }
                    }
                }
            });
        });
    });
    
    // Thêm form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Xóa tất cả thông báo lỗi cũ
            clearAllErrorMessages();
            
            // Kiểm tra validation
            if (validateForm()) {
                // Nếu validation pass thì submit form
                form.submit();
            }
        });
    }
});

// Xóa tất cả thông báo lỗi
function clearAllErrorMessages() {
    // Xóa error messages cũ
    const oldErrors = document.querySelectorAll('.validation-error');
    oldErrors.forEach(error => error.remove());
    
    // Xóa border đỏ
    const errorInputs = document.querySelectorAll('.border-red-500');
    errorInputs.forEach(input => {
        input.classList.remove('border-red-500');
        input.classList.add('border-gray-300');
    });
}

// Hiển thị thông báo lỗi cho một field
function showFieldError(fieldName, message) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
        // Thêm border đỏ
        field.classList.remove('border-gray-300');
        field.classList.add('border-red-500');
        
        // Tạo thông báo lỗi
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error text-red-500 text-sm mt-1';
        errorDiv.textContent = message;
        
        // Chèn thông báo lỗi sau field
        const parent = field.parentElement;
        parent.appendChild(errorDiv);
    }
}

// Validation form
function validateForm() {
    let isValid = true;
    
    // Kiểm tra các trường cơ bản
    const requiredFields = [
        { name: 'request_date', label: 'Ngày đề xuất' },
        { name: 'project_id', label: 'Dự án / Phiếu cho thuê' }
    ];
    
    requiredFields.forEach(field => {
        const value = document.querySelector(`[name="${field.name}"]`).value;
        if (!value || value.trim() === '') {
            showFieldError(field.name, `${field.label} không được để trống`);
            isValid = false;
        }
    });
    
    // Kiểm tra approval method
    const approvalMethod = document.querySelector('input[name="approval_method"]:checked');
    if (!approvalMethod) {
        showFieldError('approval_method', 'Phương thức xử lý không được để trống');
        isValid = false;
    }
    
    // Kiểm tra item type
    const itemType = document.querySelector('input[name="item_type"]:checked');
    if (!itemType) {
        showFieldError('item_type', 'Loại sản phẩm không được để trống');
        isValid = false;
    }
    
    // Kiểm tra danh sách items theo loại được chọn
    const selectedItemType = itemType ? itemType.value : '';
    let hasItems = false;
    
    if (selectedItemType === 'equipment') {
        const equipmentRows = document.querySelectorAll('.equipment-row');
        equipmentRows.forEach((row, index) => {
            const itemId = row.querySelector('select[name*="[id]"]');
            const quantity = row.querySelector('input[name*="[quantity]"]');
            
            if (!itemId || !itemId.value) {
                showFieldError(`equipment[${index}][id]`, 'Thiết bị không được để trống');
                isValid = false;
            } else {
                hasItems = true;
            }
            
            if (!quantity || !quantity.value || parseInt(quantity.value) < 1) {
                showFieldError(`equipment[${index}][quantity]`, 'Số lượng phải lớn hơn hoặc bằng 1');
                isValid = false;
            }
        });
    } else if (selectedItemType === 'good') {
        const goodRows = document.querySelectorAll('.good-row');
        goodRows.forEach((row, index) => {
            const itemId = row.querySelector('select[name*="[id]"]');
            const quantity = row.querySelector('input[name*="[quantity]"]');
            
            if (!itemId || !itemId.value) {
                showFieldError(`good[${index}][id]`, 'Hàng hóa không được để trống');
                isValid = false;
            } else {
                hasItems = true;
            }
            
            if (!quantity || !quantity.value || parseInt(quantity.value) < 1) {
                showFieldError(`good[${index}][quantity]`, 'Số lượng phải lớn hơn hoặc bằng 1');
                isValid = false;
            }
        });
    }
    
    if (!hasItems) {
        // Hiển thị lỗi cho container
        const container = document.getElementById(selectedItemType + '_container');
        if (container) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error text-red-500 text-sm mt-1';
            errorDiv.textContent = `Vui lòng thêm ít nhất một ${selectedItemType === 'equipment' ? 'thiết bị' : 'hàng hóa'}`;
            container.appendChild(errorDiv);
        }
        isValid = false;
    }
    
    return isValid;
} 