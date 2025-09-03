// Date Format Helper - Hỗ trợ định dạng ngày dd/mm/yyyy
(function() {
    'use strict';

    // Biến global để quản lý date picker
    let currentDatePicker = null;

    // Hàm format ngày từ Date object sang dd/mm/yyyy
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Hàm parse ngày từ dd/mm/yyyy sang Date object
    function parseDate(dateString) {
        if (!dateString) return null;
        
        const parts = dateString.split('/');
        if (parts.length !== 3) return null;
        
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);
        
        if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
        
        const date = new Date(year, month, day);
        if (date.getDate() !== day || date.getMonth() !== month || date.getFullYear() !== year) {
            return null; // Invalid date
        }
        
        return date;
    }

    // Hàm validate định dạng ngày dd/mm/yyyy
    function isValidDateFormat(dateString) {
        return parseDate(dateString) !== null;
    }

    // Hàm tạo date picker popup
    function showDatePicker(inputElement) {
        // Đóng date picker hiện tại nếu có
        if (currentDatePicker) {
            document.body.removeChild(currentDatePicker);
        }

        // Parse ngày hiện tại từ input
        let currentDate = parseDate(inputElement.value) || new Date();
        
        // Tạo popup container
        const picker = document.createElement('div');
        picker.className = 'date-picker-popup fixed bg-white border border-gray-300 rounded-lg shadow-lg z-50 p-4';
        picker.style.minWidth = '280px';
        
        // Tính vị trí hiển thị
        const rect = inputElement.getBoundingClientRect();
        picker.style.left = rect.left + 'px';
        picker.style.top = (rect.bottom + 5) + 'px';
        
        // Tạo header với navigation
        const header = document.createElement('div');
        header.className = 'flex items-center justify-between mb-4';
        
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.className = 'p-2 hover:bg-gray-100 rounded';
        prevBtn.onclick = () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        };
        
        const monthYear = document.createElement('span');
        monthYear.className = 'font-semibold text-gray-800';
        
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.className = 'p-2 hover:bg-gray-100 rounded';
        nextBtn.onclick = () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        };
        
        header.appendChild(prevBtn);
        header.appendChild(monthYear);
        header.appendChild(nextBtn);
        
        // Tạo grid cho các ngày trong tuần
        const weekdays = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        const weekdaysRow = document.createElement('div');
        weekdaysRow.className = 'grid grid-cols-7 gap-1 mb-2';
        
        weekdays.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'text-center text-sm font-medium text-gray-600 py-2';
            dayHeader.textContent = day;
            weekdaysRow.appendChild(dayHeader);
        });
        
        // Tạo grid cho các ngày
        const daysGrid = document.createElement('div');
        daysGrid.className = 'grid grid-cols-7 gap-1';
        
        // Hàm render calendar
        function renderCalendar() {
            monthYear.textContent = `${currentDate.getMonth() + 1}/${currentDate.getFullYear()}`;
            
            // Xóa các ngày cũ
            daysGrid.innerHTML = '';
            
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Ngày đầu tiên của tháng
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            
            // Ngày đầu tiên của tuần (Chủ nhật = 0)
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            // Render 6 tuần (42 ngày)
            for (let i = 0; i < 42; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);
                
                const dayCell = document.createElement('div');
                dayCell.className = 'text-center py-2 cursor-pointer hover:bg-blue-100 rounded text-sm';
                
                // Kiểm tra ngày hiện tại
                const today = new Date();
                const isToday = date.toDateString() === today.toDateString();
                const isCurrentMonth = date.getMonth() === month;
                const isSelected = inputElement.value === formatDate(date);
                
                if (isToday) {
                    dayCell.className += ' bg-blue-500 text-white hover:bg-blue-600';
                } else if (isSelected) {
                    dayCell.className += ' bg-blue-200 text-blue-800';
                } else if (!isCurrentMonth) {
                    dayCell.className += ' text-gray-400';
                }
                
                dayCell.textContent = date.getDate();
                
                dayCell.onclick = () => {
                    inputElement.value = formatDate(date);
                    inputElement.dispatchEvent(new Event('change'));
                    document.body.removeChild(picker);
                    currentDatePicker = null;
                };
                
                daysGrid.appendChild(dayCell);
            }
        }
        
        // Render calendar lần đầu
        renderCalendar();
        
        // Thêm các elements vào picker
        picker.appendChild(header);
        picker.appendChild(weekdaysRow);
        picker.appendChild(daysGrid);
        
        // Thêm vào body
        document.body.appendChild(picker);
        currentDatePicker = picker;
        
        // Đóng picker khi click bên ngoài
        document.addEventListener('click', function closePicker(e) {
            if (!picker.contains(e.target) && e.target !== inputElement) {
                if (currentDatePicker) {
                    document.body.removeChild(currentDatePicker);
                    currentDatePicker = null;
                }
                document.removeEventListener('click', closePicker);
            }
        });
    }

    // Hàm khởi tạo date inputs
    function initDateInputs() {
        // Tìm tất cả các input có class date-input
        const dateInputs = document.querySelectorAll('.date-input');
        
        dateInputs.forEach(input => {
            // Thêm padding-right để tránh icon bị đè lên text
            input.style.paddingRight = '40px';
            
            // Tạo icon calendar
            const icon = document.createElement('i');
            icon.className = 'fas fa-calendar-alt absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer';
            icon.style.pointerEvents = 'auto';
            
            // Wrap input trong div để có thể đặt icon
            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            wrapper.appendChild(icon);
            
            // Thêm event listeners
            icon.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                showDatePicker(input);
            };
            
            input.onclick = () => {
                showDatePicker(input);
            };
            
            // Validate khi blur
            input.onblur = function() {
                if (this.value && !isValidDateFormat(this.value)) {
                    this.style.borderColor = '#ef4444';
                    this.style.backgroundColor = '#fef2f2';
                } else {
                    this.style.borderColor = '';
                    this.style.backgroundColor = '';
                }
            };
            
            // Format khi nhập
            input.oninput = function() {
                let value = this.value.replace(/\D/g, ''); // Chỉ giữ số
                
                if (value.length >= 8) {
                    value = value.substring(0, 8);
                }
                
                // Thêm dấu / tự động
                if (value.length >= 4) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4);
                } else if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                
                this.value = value;
            };
        });
    }

    // Khởi tạo khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDateInputs);
    } else {
        initDateInputs();
    }

    // Export functions để có thể sử dụng từ bên ngoài
    window.DateFormatHelper = {
        formatDate: formatDate,
        parseDate: parseDate,
        isValidDateFormat: isValidDateFormat,
        showDatePicker: showDatePicker,
        initDateInputs: initDateInputs
    };
})();
