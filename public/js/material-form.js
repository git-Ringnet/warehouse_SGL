/**
 * Material Form JavaScript - Shared functionality for material create and edit forms
 */

// Image upload handling
function initializeImageUpload() {
    const addImageBtn = document.getElementById('addImageBtn');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const dropzone = document.getElementById('dropzone');
    
    // Trigger file input when button is clicked
    addImageBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent the dropzone click event
        imageInput.click();
    });
    
    // Click anywhere on the dropzone to trigger file input
    dropzone.addEventListener('click', function() {
        imageInput.click();
    });
    
    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Visual feedback when dragging over the dropzone
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function() {
            dropzone.classList.add('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function() {
            dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    // Handle drop event
    dropzone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        handleFiles(files);
    });
    
    // Handle file input change
    imageInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });
    
    // Process files and create previews
    function handleFiles(files) {
        files = [...files];
        
        files.forEach((file, index) => {
            if (!file.type.match('image.*')) {
                alert('Chỉ chấp nhận file hình ảnh!');
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                alert('File quá lớn. Kích thước tối đa là 2MB!');
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewId = 'preview-' + Date.now() + '-' + index;
                
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative';
                previewDiv.id = previewId;
                
                previewDiv.innerHTML = `
                    <div class="w-32 h-32 border border-gray-200 rounded-lg overflow-hidden">
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                    </div>
                    <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 remove-image" data-preview-id="${previewId}">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                
                imagePreviewContainer.appendChild(previewDiv);
                
                // Add event listener to remove button
                previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                    const previewId = this.getAttribute('data-preview-id');
                    document.getElementById(previewId).remove();
                });
            };
            
            reader.readAsDataURL(file);
        });
    }
}

// Category management
function initializeCategoryModal() {
    const modal = document.getElementById('addCategoryModal');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');
    const addCategoryForm = document.getElementById('addCategoryForm');
    const categorySelect = document.getElementById('category');

    // Mở modal
    addCategoryBtn.addEventListener('click', function() {
        modal.classList.remove('hidden');
    });

    // Đóng modal
    function closeModal() {
        modal.classList.add('hidden');
        addCategoryForm.reset();
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelCategoryBtn.addEventListener('click', closeModal);

    // Đóng modal khi click bên ngoài
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Xử lý form thêm loại vật tư
    addCategoryForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const newCategoryName = document.getElementById('newCategoryName').value.trim();

        if (newCategoryName) {
            // Kiểm tra trùng lặp
            let isDuplicate = false;
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === newCategoryName) {
                    isDuplicate = true;
                    break;
                }
            }

            if (!isDuplicate) {
                // Thêm option mới vào select
                const newOption = document.createElement('option');
                newOption.value = newCategoryName;
                newOption.text = newCategoryName;
                categorySelect.add(newOption);

                // Chọn option vừa thêm
                categorySelect.value = newCategoryName;

                // Đóng modal
                closeModal();
            } else {
                alert('Loại vật tư này đã tồn tại!');
            }
        }
    });
}

// Handle existing images deletion (for edit form)
function initializeExistingImageDeletion() {
    const deletedImagesInput = document.getElementById('deletedImages');
    if (!deletedImagesInput) return; // Exit if not on edit page
    
    let deletedImages = [];
    
    document.querySelectorAll('.delete-existing-image').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-image-id');
            
            // Add to deleted images array
            deletedImages.push(imageId);
            deletedImagesInput.value = deletedImages.join(',');
            
            // Remove from DOM
            document.getElementById('existing-image-' + imageId).remove();
        });
    });
}

// Form change detection (for edit form)
function initializeFormChangeDetection() {
    const form = document.querySelector('form');
    const originalFormState = form.innerHTML;

    window.addEventListener('beforeunload', function(e) {
        if (form.innerHTML !== originalFormState) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

// Initialize all form functionality
function initializeMaterialForm(isEditForm = false) {
    initializeImageUpload();
    initializeCategoryModal();
    
    if (isEditForm) {
        initializeExistingImageDeletion();
        initializeFormChangeDetection();
    }
} 