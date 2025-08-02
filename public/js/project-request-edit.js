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
    
    // Thêm event listener cho các select items để kiểm tra tồn kho
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[id]"]')) {
            const itemType = getItemTypeFromSelect(e.target);
            const itemId = e.target.value;
            
            if (itemId) {
                checkStock(itemType, itemId, e.target);
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
}); 