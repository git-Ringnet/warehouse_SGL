/**
 * Modal xác nhận xóa
 * Sử dụng: initDeleteModal() để khởi tạo modal
 */

// Tạo modal HTML khi khởi tạo
function initDeleteModal() {
    // Kiểm tra xem modal đã tồn tại chưa
    if (document.getElementById('deleteModal')) {
        return;
    }

    // Tạo modal element
    const modalHTML = `
    <div id="deleteModal" class="modal-overlay">
        <div class="modal shadow-lg p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Xác nhận xóa</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa khách hàng <span id="customerNameToDelete" class="font-semibold"></span>? Hành động này không thể hoàn tác.</p>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors">
                    Hủy
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                    Xác nhận xóa
                </button>
            </div>
        </div>
    </div>
    `;

    // Thêm CSS nếu chưa có
    if (!document.getElementById('modalStyles')) {
        const modalStyles = document.createElement('style');
        modalStyles.id = 'modalStyles';
        modalStyles.textContent = `
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
        `;
        document.head.appendChild(modalStyles);
    }

    // Thêm modal vào body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// Mở modal xác nhận xóa
function openDeleteModal(id, name) {
    // Khởi tạo modal nếu chưa có
    initDeleteModal();
    
    document.getElementById('customerNameToDelete').innerText = name;
    document.getElementById('confirmDeleteBtn').setAttribute('onclick', `deleteCustomer(${id})`);
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Đóng modal xác nhận xóa
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Xử lý khi xác nhận xóa
function deleteCustomer(id) {
    // Đây là nơi xử lý xóa khách hàng
    // Trong một ứng dụng thực, có thể gửi một yêu cầu AJAX để xóa
    
    // Đóng modal
    closeDeleteModal();
    
    // Thông báo đã xóa (có thể thay bằng hành động phù hợp)
    alert(`Đã xóa khách hàng có ID: ${id}`);
    
    // Có thể reload trang hoặc xóa phần tử trực tiếp khỏi DOM
    // window.location.reload();
}

// Tự động khởi tạo modal khi tải trang
document.addEventListener('DOMContentLoaded', function() {
    // Không cần khởi tạo ngay, sẽ khởi tạo khi cần dùng
}); 