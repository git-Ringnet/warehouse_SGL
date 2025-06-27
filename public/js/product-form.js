document.addEventListener("DOMContentLoaded", function () {
    // Image upload functionality
    initializeImageUpload();

    // Material management functionality
    initializeMaterialManagement();

    // Warehouse selection functionality
    initializeWarehouseSelection();

    // Form submission handling
    initializeFormSubmission();
});

// Initialize image upload
function initializeImageUpload() {
    const addImageBtn = document.getElementById("addImageBtn");
    const imageInput = document.getElementById("imageInput");
    const imagePreviewContainer = document.getElementById(
        "imagePreviewContainer"
    );
    const dropzone = document.getElementById("dropzone");
    const deletedImagesInput = document.getElementById("deletedImages");
    let deletedImages = [];

    if (!addImageBtn || !imageInput || !dropzone || !imagePreviewContainer) {
        console.log("Image upload elements not found");
        return;
    }

    // Trigger file input when button is clicked
    addImageBtn.addEventListener("click", function (e) {
        e.stopPropagation(); // Prevent the dropzone click event
        imageInput.click();
    });

    // Click anywhere on the dropzone to trigger file input
    dropzone.addEventListener("click", function () {
        imageInput.click();
    });

    // Drag and drop functionality
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Visual feedback when dragging over the dropzone
    ["dragenter", "dragover"].forEach((eventName) => {
        dropzone.addEventListener(
            eventName,
            function () {
                dropzone.classList.add("border-blue-500", "bg-blue-50");
            },
            false
        );
    });

    ["dragleave", "drop"].forEach((eventName) => {
        dropzone.addEventListener(
            eventName,
            function () {
                dropzone.classList.remove("border-blue-500", "bg-blue-50");
            },
            false
        );
    });

    // Handle drop event
    dropzone.addEventListener("drop", function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        handleFiles(files);
    });

    // Handle file input change
    imageInput.addEventListener("change", function (e) {
        handleFiles(this.files);
    });

    // Process files and create previews
    function handleFiles(files) {
        files = [...files];

        files.forEach((file, index) => {
            if (!file.type.match("image.*")) {
                alert("Chỉ chấp nhận file hình ảnh!");
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("File quá lớn. Kích thước tối đa là 2MB!");
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                const previewId = "preview-" + Date.now() + "-" + index;

                const previewDiv = document.createElement("div");
                previewDiv.className = "relative";
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
                previewDiv
                    .querySelector(".remove-image")
                    .addEventListener("click", function () {
                        const previewId = this.getAttribute("data-preview-id");
                        document.getElementById(previewId).remove();
                    });
            };

            reader.readAsDataURL(file);
        });
    }

    // Handle deletion of existing images
    if (deletedImagesInput) {
        document
            .querySelectorAll(".delete-existing-image")
            .forEach((button) => {
                button.addEventListener("click", function () {
                    const imageId = this.getAttribute("data-image-id");

                    // Add to deleted images array
                    deletedImages.push(imageId);
                    deletedImagesInput.value = deletedImages.join(",");

                    // Remove from DOM
                    document
                        .getElementById("existing-image-" + imageId)
                        .remove();
                });
            });
    }
}

// Initialize material management
function initializeMaterialManagement() {
    const addMaterialBtn = document.getElementById("addMaterialBtn");
    const materialsContainer = document.getElementById("materialsContainer");
    const noMaterialsMessage = document.getElementById("noMaterialsMessage");

    if (!addMaterialBtn || !materialsContainer) {
        console.log("Material management elements not found");
        return;
    }

    // Get materials data from the page (passed from controller)
    let materialsData = [];
    if (window.materialsData) {
        materialsData = window.materialsData;
    }

    // Add new material row
    addMaterialBtn.addEventListener("click", function () {
        const materialRow = createMaterialRow(materialsData);
        materialsContainer.appendChild(materialRow);
        if (noMaterialsMessage) {
            noMaterialsMessage.classList.add("hidden");
        }
    });

    // Handle existing material rows' remove buttons
    document.querySelectorAll(".remove-material-btn").forEach((button) => {
        button.addEventListener("click", function () {
            this.closest(".material-row").remove();
            if (
                materialsContainer.querySelectorAll(".material-row").length ===
                    0 &&
                noMaterialsMessage
            ) {
                noMaterialsMessage.classList.remove("hidden");
            }
        });
    });

    // Function to create a new material row
    function createMaterialRow(materials = []) {
        const row = document.createElement("div");
        row.className = "material-row grid grid-cols-12 gap-2 items-center";

        let optionsHtml = '<option value="">-- Chọn vật tư --</option>';
        materials.forEach((material) => {
            optionsHtml += `<option value="${material.id}">${material.name} (${material.code})</option>`;
        });

        row.innerHTML = `
            <div class="col-span-5">
                <select class="material-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" required placeholder="Số lượng" min="0.01" step="0.01" value="1" class="material-quantity w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div class="col-span-3">
                <input type="text" placeholder="Ghi chú" class="material-note w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div class="col-span-1">
                <button type="button" class="remove-material-btn w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                    <i class="fas fa-times text-red-500 group-hover:text-white text-sm"></i>
                </button>
            </div>
        `;

        // Add event listener to remove button
        row.querySelector(".remove-material-btn").addEventListener(
            "click",
            function () {
                this.closest(".material-row").remove();
                if (
                    materialsContainer.querySelectorAll(".material-row")
                        .length === 0 &&
                    noMaterialsMessage
                ) {
                    noMaterialsMessage.classList.remove("hidden");
                }
            }
        );

        return row;
    }
}

// Initialize warehouse selection
function initializeWarehouseSelection() {
    const warehouseAll = document.getElementById("warehouse_all");
    const warehouseCheckboxes = document.querySelectorAll(
        'input[name="inventory_warehouses[]"]:not(#warehouse_all)'
    );

    if (!warehouseAll) {
        return;
    }

    // Handle "All warehouses" checkbox
    warehouseAll.addEventListener("change", function () {
        if (this.checked) {
            warehouseCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        }
    });

    // Handle individual warehouse checkboxes
    warehouseCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            if (this.checked) {
                warehouseAll.checked = false;
            }

            // If no individual warehouses are selected, check "All warehouses"
            const anyChecked = Array.from(warehouseCheckboxes).some(
                (cb) => cb.checked
            );
            if (!anyChecked) {
                warehouseAll.checked = true;
            }
        });
    });
}

// Initialize form submission
function initializeFormSubmission() {
    // ✅ Target đúng form: form có action chứa 'products' hoặc form trong main content area
    const form =
        document.querySelector('form[action*="products"]') ||
        document.querySelector(".content-area form") ||
        document.querySelector("main form");

    if (!form) {
        console.log("❌ Product form not found");
        return;
    }
    
    form.addEventListener("submit", function (e) {
        console.log("🚀 Form submission started");

        // Remove any existing hidden material inputs to avoid duplicates
        const existingMaterialInputs = form.querySelectorAll(
            'input[name^="materials["]'
        );
        console.log(
            "🗑️ Removing existing material inputs:",
            existingMaterialInputs.length
        );
        existingMaterialInputs.forEach((input) => input.remove());

        // Collect materials data
        const materialsData = [];
        const materialRows = document.querySelectorAll(".material-row");

        console.log("📋 Found material rows:", materialRows.length);

        materialRows.forEach((row, index) => {
            console.log(`🔍 Processing row ${index + 1}:`);

            const materialSelect = row.querySelector(".material-select");
            const quantityInput = row.querySelector(".material-quantity");
            const noteInput = row.querySelector(".material-note");

            console.log(
                "  - Material select:",
                materialSelect ? materialSelect.value : "NOT FOUND"
            );
            console.log(
                "  - Quantity input:",
                quantityInput ? quantityInput.value : "NOT FOUND"
            );
            console.log(
                "  - Note input:",
                noteInput ? noteInput.value : "NOT FOUND"
            );

            if (
                materialSelect &&
                materialSelect.value &&
                quantityInput &&
                quantityInput.value
            ) {
                const materialData = {
                    id: materialSelect.value,
                    quantity: parseFloat(quantityInput.value),
                    notes: noteInput ? noteInput.value || null : null,
                };

                materialsData.push(materialData);
                console.log("  ✅ Added material:", materialData);
            } else {
                console.log("  ❌ Skipped row - missing data");
            }
        });

        console.log("📦 Final materials data:", materialsData);

        if (materialsData.length === 0) {
            console.log("⚠️ No materials to add");
        }

        // Add materials data to form as hidden inputs
        materialsData.forEach((material, index) => {
            const hiddenInputs = [
                { name: `materials[${index}][id]`, value: material.id },
                {
                    name: `materials[${index}][quantity]`,
                    value: material.quantity,
                },
                {
                    name: `materials[${index}][notes]`,
                    value: material.notes || "",
                },
            ];

            hiddenInputs.forEach((input) => {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = input.name;
                hiddenInput.value = input.value;
                form.appendChild(hiddenInput);
                console.log(
                    "➕ Added hidden input to PRODUCT form:",
                    input.name,
                    "=",
                    input.value
                );
            });
        });

        // Final form data check
        const formData = new FormData(form);
        console.log("📋 Final form data:");
        for (let [key, value] of formData.entries()) {
            if (key.includes("materials")) {
                console.log(`  ${key}: ${value}`);
            }
        }

        console.log("✅ Form ready to submit");
    });
}
