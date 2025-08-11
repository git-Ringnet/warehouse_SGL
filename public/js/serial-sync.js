/**
 * Serial Numbers Synchronization Module
 * Đồng bộ hóa số Serial giữa giao diện chính và modal cập nhật mã thiết bị
 */

class SerialSyncManager {
    constructor() {
        this.isModalOpen = false;
        this.syncInterval = null;
        this.init();
    }

    init() {
        this.addEventListeners();
        this.startConsistencyCheck();
    }

    /**
     * Thêm các event listeners cho đồng bộ hóa
     */
    addEventListeners() {
        // Lắng nghe thay đổi trong giao diện chính
        document.addEventListener('change', (e) => {
            if (e.target.matches('select[name*="serial_numbers"]')) {
                this.syncFromMainToModal();
            }
        });

        // Lắng nghe thay đổi trong modal
        document.addEventListener('input', (e) => {
            if (e.target.matches('#device-code-modal input[name*="serial_main"]')) {
                this.syncFromModalToMain();
            }
        });

        // Lắng nghe khi modal mở/đóng
        const deviceCodeModal = document.getElementById('device-code-modal');
        if (deviceCodeModal) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        this.isModalOpen = !deviceCodeModal.classList.contains('hidden');
                        if (this.isModalOpen) {
                            this.syncFromMainToModal();
                        }
                    }
                });
            });
            observer.observe(deviceCodeModal, { attributes: true });
        }
    }

    /**
     * Đồng bộ từ giao diện chính sang modal
     * Chỉ đồng bộ khi modal chưa có serial chính
     */
    syncFromMainToModal() {
        if (!this.isModalOpen) return;

        const mainSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
        const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
        
        // Chỉ đồng bộ những modal input chưa có giá trị
        modalSerialInputs.forEach((input, index) => {
            if (!input.value && mainSerialSelects[index] && mainSerialSelects[index].value) {
                input.value = mainSerialSelects[index].value;
                // Trigger change event để cập nhật UI
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        console.log('Synced from main to modal (only empty fields)');
    }

    /**
     * Đồng bộ từ modal sang giao diện chính
     * Ưu tiên serial_main từ modal (vì đó là serial mới được đổi tên)
     */
    syncFromModalToMain() {
        const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
        const mainSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
        
        // Tạo mapping giữa modal serials và main serials theo type
        const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
        
        // Lấy type hiện tại từ modal
        const currentType = this.getCurrentModalType();
        
        // Chỉ đồng bộ serial của cùng type
        mainSerialSelects.forEach((select, index) => {
            if (modalSerials[index]) {
                // Kiểm tra xem select này có thuộc type hiện tại không
                const selectType = this.getSelectType(select);
                if (selectType === currentType) {
                    // Ưu tiên serial_main từ modal (serial mới được đổi tên)
                    const newSerial = modalSerials[index];
                    
                    // Tìm option tương ứng trong select
                    const option = Array.from(select.options).find(opt => opt.value === newSerial);
                    
                    if (option) {
                        select.value = newSerial;
                    } else {
                        // Nếu không tìm thấy option, tạo mới
                        const newOption = document.createElement('option');
                        newOption.value = newSerial;
                        newOption.textContent = newSerial;
                        select.appendChild(newOption);
                        select.value = newSerial;
                    }
                    
                    // Trigger change event để cập nhật UI
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        console.log('Synced from modal to main (prioritizing modal serials for type ' + currentType + '):', modalSerials);
    }

    /**
     * Lấy type hiện tại của modal
     */
    getCurrentModalType() {
        // Kiểm tra xem modal nào đang mở
        const contractBtn = document.getElementById('update_contract_device_codes_btn');
        const backupBtn = document.getElementById('update_backup_device_codes_btn');
        
        if (contractBtn && !contractBtn.disabled) {
            return 'contract';
        } else if (backupBtn && !backupBtn.disabled) {
            return 'backup';
        }
        
        return 'unknown';
    }

    /**
     * Lấy type của select element
     */
    getSelectType(select) {
        const name = select.name || '';
        if (name.includes('contract_items')) {
            return 'contract';
        } else if (name.includes('backup_items')) {
            return 'backup';
        }
        return 'unknown';
    }

    /**
     * Đồng bộ hai chiều
     * Ưu tiên serial chính từ modal (serial mới được đổi tên)
     */
    syncSerialNumbers() {
        // Đầu tiên đồng bộ từ modal sang main (ưu tiên serial mới)
        this.syncFromModalToMain();
        // Sau đó đồng bộ từ main sang modal (chỉ những field trống)
        this.syncFromMainToModal();
        this.hideInconsistencyWarning();
        console.log('Serial numbers synchronized (modal priority)');
    }

    /**
     * Validate tính nhất quán của serial numbers
     * Giao diện chính phải hiển thị serial_main từ modal (nếu có)
     */
    validateSerialConsistency() {
        const mainSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
        const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
        
        // Lấy type hiện tại từ modal
        const currentType = this.getCurrentModalType();
        
        // Chỉ kiểm tra serial của cùng type
        const typeMainSerials = Array.from(mainSerialSelects)
            .filter(select => this.getSelectType(select) === currentType)
            .map(select => select.value)
            .filter(Boolean);
            
        const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
        
        // Kiểm tra xem giao diện chính có hiển thị đúng serial_main từ modal không
        const inconsistencies = [];
        modalSerials.forEach((modalSerial, index) => {
            if (modalSerial && typeMainSerials[index] && typeMainSerials[index] !== modalSerial) {
                // Giao diện chính không hiển thị serial_main từ modal
                inconsistencies.push({
                    index: index + 1,
                    main: typeMainSerials[index],
                    modal: modalSerial,
                    expected: modalSerial,
                    type: currentType
                });
            }
        });
        
        if (inconsistencies.length > 0) {
            console.warn('Serial number inconsistencies detected for type ' + currentType + ' (main should show modal serial):', inconsistencies);
            return false;
        }
        
        return true;
    }

    /**
     * Hiển thị cảnh báo về sự không nhất quán
     */
    showInconsistencyWarning() {
        // Xóa cảnh báo cũ
        this.hideInconsistencyWarning();

        const currentType = this.getCurrentModalType();
        const typeLabel = currentType === 'contract' ? 'hợp đồng' : currentType === 'backup' ? 'dự phòng' : '';

        const warningDiv = document.createElement('div');
        warningDiv.className = 'serial-inconsistency-warning bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4';
        warningDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Cảnh báo:</strong>
            </div>
            <p class="mt-2">Giao diện chính chưa hiển thị đúng Serial chính từ modal cập nhật mã thiết bị (${typeLabel}). 
            Serial chính trong modal là serial mới được đổi tên và sẽ được hiển thị ở giao diện chính.</p>
            <button type="button" onclick="serialSyncManager.syncSerialNumbers()" class="mt-2 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                <i class="fas fa-sync-alt mr-1"></i> Cập nhật Serial
            </button>
        `;

        // Thêm vào đầu form
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(warningDiv, form.firstChild);
        }
    }

    /**
     * Ẩn cảnh báo về sự không nhất quán
     */
    hideInconsistencyWarning() {
        const oldWarning = document.querySelector('.serial-inconsistency-warning');
        if (oldWarning) {
            oldWarning.remove();
        }
    }

    /**
     * Bắt đầu kiểm tra tính nhất quán định kỳ
     */
    startConsistencyCheck() {
        this.syncInterval = setInterval(() => {
            if (this.isModalOpen) {
                if (!this.validateSerialConsistency()) {
                    this.showInconsistencyWarning();
                } else {
                    this.hideInconsistencyWarning();
                }
            }
        }, 5000); // Kiểm tra mỗi 5 giây
    }

    /**
     * Dừng kiểm tra tính nhất quán
     */
    stopConsistencyCheck() {
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
            this.syncInterval = null;
        }
    }

    /**
     * Lấy thông tin serial numbers hiện tại
     */
    getCurrentSerialInfo() {
        const mainSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
        const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
        
        return {
            main: Array.from(mainSerialSelects).map(select => select.value).filter(Boolean),
            modal: Array.from(modalSerialInputs).map(input => input.value).filter(Boolean),
            isConsistent: this.validateSerialConsistency()
        };
    }
}

// Khởi tạo SerialSyncManager khi DOM đã sẵn sàng
let serialSyncManager;
document.addEventListener('DOMContentLoaded', function() {
    serialSyncManager = new SerialSyncManager();
});

// Export cho sử dụng toàn cục
window.SerialSyncManager = SerialSyncManager;
