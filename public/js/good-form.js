/**
 * Initializes the good form functionality
 * @param {boolean} isEditForm - Whether this is an edit form (true) or create form (false)
 */
function initializeGoodForm(isEditForm) {
    // Category management
    initializeCategoryManagement();
    
    // Image upload functionality
    initializeImageUpload();
    
    // If this is an edit form, initialize the image deletion tracking
    if (isEditForm) {
        initializeExistingImageDeletion();
    }
}

/**
 * Initializes category management functionality
 */
function initializeCategoryManagement() {
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const addCategoryModal = document.getElementById('addCategoryModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');
    const addCategoryForm = document.getElementById('addCategoryForm');
    const categorySelect = document.getElementById('category');
    
    // Open modal when add category button is clicked
    addCategoryBtn.addEventListener('click', function() {
        addCategoryModal.classList.remove('hidden');
    });
    
    // Close modal when close button is clicked
    closeModalBtn.addEventListener('click', function() {
        addCategoryModal.classList.add('hidden');
    });
    
    // Close modal when cancel button is clicked
    cancelCategoryBtn.addEventListener('click', function() {
        addCategoryModal.classList.add('hidden');
    });
    
    // Handle category form submission
    addCategoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newCategoryName = document.getElementById('newCategoryName').value.trim();
        
        if (newCategoryName) {
            // Check if category already exists
            let categoryExists = false;
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === newCategoryName) {
                    categoryExists = true;
                    break;
                }
            }
            
            // If category doesn't exist, add it
            if (!categoryExists) {
                const newOption = document.createElement('option');
                newOption.value = newCategoryName;
                newOption.text = newCategoryName;
                categorySelect.add(newOption);

                // Select the new/existing category
                categorySelect.value = newCategoryName;

                // Reset form and close modal
                document.getElementById('newCategoryName').value = '';
                addCategoryModal.classList.add('hidden');
            }
            
            // Select the new/existing category
            categorySelect.value = newCategoryName;
            
            // Reset form and close modal
            document.getElementById('newCategoryName').value = '';
            addCategoryModal.classList.add('hidden');
        }
    });
}

/**
 * Initializes image upload functionality
 */
function initializeImageUpload() {
    const dropzone = document.getElementById('dropzone');
    const addImageBtn = document.getElementById('addImageBtn');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    
    // Open file dialog when button is clicked
    addImageBtn.addEventListener('click', function() {
        imageInput.click();
    });
    
    // Handle drag and drop events
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('bg-gray-100');
    });
    
    dropzone.addEventListener('dragleave', function() {
        dropzone.classList.remove('bg-gray-100');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('bg-gray-100');
        
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            handleFileSelect();
        }
    });
    
    // Handle file selection via dialog
    imageInput.addEventListener('change', handleFileSelect);
    
    function handleFileSelect() {
        const files = imageInput.files;
        
        if (files.length > 0) {
            // Process each selected file
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Validate file type and size
                if (!file.type.match('image.*')) {
                    alert('Vui lòng chỉ chọn tệp hình ảnh.');
                    continue;
                }
                
                if (file.size > 2 * 1024 * 1024) { // 2MB limit
                    alert('Kích thước hình ảnh không được vượt quá 2MB.');
                    continue;
                }
                
                // Create image preview
                createImagePreview(file);
            }
        }
    }
    
    // Create image preview with delete button
    function createImagePreview(file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const imageContainer = document.createElement('div');
            imageContainer.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
            imageContainer.style.width = '150px';
            imageContainer.style.height = '150px';
            
            const image = document.createElement('img');
            image.src = e.target.result;
            image.className = 'w-full h-full object-cover';
            
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors';
            deleteButton.innerHTML = '<i class="fas fa-times"></i>';
            
            deleteButton.addEventListener('click', function() {
                imageContainer.remove();
                
                // Reset file input to allow selecting the same file again
                // This is a bit tricky as we can't modify the FileList directly
                // A full solution would involve keeping track of selected files manually
            });
            
            imageContainer.appendChild(image);
            imageContainer.appendChild(deleteButton);
            imagePreviewContainer.appendChild(imageContainer);
        };
        
        reader.readAsDataURL(file);
    }
}

/**
 * Initializes deletion tracking for existing images in edit form
 */
function initializeExistingImageDeletion() {
    const existingImagesContainer = document.getElementById('existingImagesContainer');
    const deletedImagesInput = document.getElementById('deletedImages');
    
    if (existingImagesContainer) {
        // Add event listener for the container using delegation
        existingImagesContainer.addEventListener('click', function(e) {
            // Find the delete button if clicked
            if (e.target.closest('.delete-image-btn')) {
                const deleteBtn = e.target.closest('.delete-image-btn');
                const imageContainer = deleteBtn.closest('.image-container');
                const imageId = imageContainer.getAttribute('data-image-id');
                
                // Add the image ID to the hidden input for deletion tracking
                const deletedIds = deletedImagesInput.value ? 
                    deletedImagesInput.value.split(',') : [];
                
                if (!deletedIds.includes(imageId)) {
                    deletedIds.push(imageId);
                    deletedImagesInput.value = deletedIds.join(',');
                }
                
                // Hide the image preview
                imageContainer.remove();
            }
        });
    }
} 