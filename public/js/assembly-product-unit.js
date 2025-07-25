document.addEventListener("DOMContentLoaded", function () {
    console.log(
        "Assembly product unit script loaded - New approach with product units"
    );

    // Định nghĩa biến toàn cục
    window.isFetchingSerials = false;
    window.isCreatingProduct = false;

    // Ensure global arrays exist
    if (!window.selectedComponents) window.selectedComponents = [];
    if (!window.selectedProducts) window.selectedProducts = [];

    // Hàm tìm kiếm thẻ cha của một phần tử
    function findAncestor(element, tagName) {
        while (element && element.tagName !== tagName.toUpperCase()) {
            element = element.parentElement;
        }
        return element;
    }

    // Hàm tìm kiếm các hàng component cho một productId
    function findComponentRows(productId) {
        const componentList = document.getElementById(
            `component_list_${productId}`
        );
        if (!componentList) {
            console.error(`No component list found for product ${productId}`);
            return [];
        }
        return Array.from(
            componentList.querySelectorAll(
                "tr:not(#no_components_row_" + productId + ")"
            )
        );
    }

    // Hàm tải serial cho một vật tư
    function fetchMaterialSerials(row, productIndex) {
        const componentId =
            row.getAttribute("data-component-id") ||
            row.getAttribute("data-material-id") ||
            row.querySelector("input[name*='[id]']")?.value;

        const productId = row
            .closest("[id^='component_list_']")
            ?.id.replace("component_list_", "");

        if (!componentId || !productId) {
            console.error("Missing required IDs:", { componentId, row });
            return;
        }

        // Get product container and add fetch button if needed
        const serialCell = row.querySelector(".serial-cell");
        if (!serialCell) return;

        // Lấy container thành phẩm
        let productContainer = serialCell.querySelector(
            `.product-serial-container[data-product-index="${productIndex}"]`
        );
        if (!productContainer) {
            // Tạo container mới nếu chưa có
            productContainer = document.createElement("div");
            productContainer.className =
                "product-serial-container mb-3 p-2 border border-gray-200 rounded";
            productContainer.setAttribute("data-product-index", productIndex);

            // Thêm tiêu đề cho container
            const containerHeader = document.createElement("div");
            containerHeader.className = "font-medium text-gray-700 mb-2";
            containerHeader.textContent = `Thành phẩm ${productIndex + 1}`;
            productContainer.appendChild(containerHeader);

            serialCell.appendChild(productContainer);
        }

        // Add fetch button if not already present
        if (!productContainer.querySelector(".fetch-serials-btn")) {
            const fetchButton = document.createElement("button");
            fetchButton.type = "button";
            fetchButton.className =
                "fetch-serials-btn mt-2 text-sm text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded";
            fetchButton.innerHTML = `<i class="fas fa-sync mr-1"></i> Tải danh sách serial`;
            fetchButton.addEventListener("click", function () {
                loadSerialsForContainer(row, productIndex, productContainer);
            });
            productContainer.appendChild(fetchButton);
        }
    }

    // Hàm tải serials cho một container cụ thể
    function loadSerialsForContainer(row, productIndex, productContainer) {
        // Xác định IDs
        const componentId =
            row.getAttribute("data-component-id") ||
            row.getAttribute("data-material-id") ||
            row.querySelector("input[name*='[id]']")?.value;

        const productId = row
            .closest("[id^='component_list_']")
            ?.id.replace("component_list_", "");

        // Lấy thông tin cần thiết
        const warehouseId =
            document.getElementById("warehouse_id")?.value || "";

        // Xóa nút tải và nội dung cũ
        const fetchButton =
            productContainer.querySelector(".fetch-serials-btn");
        if (fetchButton) {
            productContainer.removeChild(fetchButton);
        }

        // Xóa nội dung cũ trong container (giữ lại header)
        const containerHeader = productContainer.querySelector(".font-medium");
        const children = Array.from(productContainer.children);
        children.forEach((child) => {
            if (child !== containerHeader) {
                productContainer.removeChild(child);
            }
        });

        // Nếu chưa chọn kho xuất, hiển thị thông báo
        if (!warehouseId) {
            const noWarehouseMsg = document.createElement("div");
            noWarehouseMsg.className = "text-amber-500 text-sm";
            noWarehouseMsg.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Vui lòng chọn kho xuất để xem danh sách serial`;
            productContainer.appendChild(noWarehouseMsg);

            // Add the fetch button back
            const newFetchBtn = document.createElement("button");
            newFetchBtn.type = "button";
            newFetchBtn.className =
                "fetch-serials-btn mt-2 text-sm text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded";
            newFetchBtn.innerHTML = `<i class="fas fa-sync mr-1"></i> Tải danh sách serial`;
            newFetchBtn.addEventListener("click", function () {
                loadSerialsForContainer(row, productIndex, productContainer);
            });
            productContainer.appendChild(newFetchBtn);
            return;
        }

        // Hiển thị loading trong container
        const loadingDiv = document.createElement("div");
        loadingDiv.className = "text-center loading-indicator";
        loadingDiv.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Đang tải serial cho thành phẩm ${
            productIndex + 1
        }...`;
        productContainer.appendChild(loadingDiv);

        const assemblyId =
            document.querySelector("form")?.action.split("/").pop() || null;

        // Gọi API lấy danh sách serial
        const url = "/assemblies/material-serials";
        const fetchOptions = {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                material_id: componentId,
                warehouse_id: warehouseId,
                product_index: productIndex,
                assembly_id: assemblyId,
            }),
        };

        console.log("Fetching serials with data:", {
            material_id: componentId,
            warehouse_id: warehouseId,
            product_index: productIndex,
        });

        fetch(url, fetchOptions)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                // Xóa loading
                const loadingIndicator =
                    productContainer.querySelector(".loading-indicator");
                if (loadingIndicator) {
                    productContainer.removeChild(loadingIndicator);
                }

                if (data.success && data.serials && data.serials.length > 0) {
                    // Lấy số lượng từ input
                    const quantityInput = row.querySelector(
                        ".component-quantity-input"
                    );
                    const quantity = parseInt(quantityInput?.value) || 1;

                    // Tạo label cho container
                    const containerLabel = document.createElement("div");
                    containerLabel.className =
                        "text-sm font-medium text-gray-700 mb-2";
                    containerLabel.textContent = `Thành phẩm ${
                        productIndex + 1
                    }`;
                    productContainer.appendChild(containerLabel);

                    // Tạo các select theo số lượng
                    for (let i = 0; i < quantity; i++) {
                        // Tạo label nếu có nhiều serial
                        if (quantity > 1) {
                            const label = document.createElement("label");
                            label.className = "text-xs text-gray-500 mb-1";
                            label.textContent = `Serial ${i + 1}:`;
                            productContainer.appendChild(label);
                        }

                        // Tạo select
                        const select = document.createElement("select");
                        select.className =
                            "serial-input border border-gray-300 rounded-lg px-2 py-1 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2";
                        select.name =
                            quantity > 1
                                ? `components[${
                                      row.dataset.index || 0
                                  }][serials_product_${productIndex}][]`
                                : `components[${
                                      row.dataset.index || 0
                                  }][serial_product_${productIndex}]`;

                        // Thêm option mặc định
                        const defaultOption = document.createElement("option");
                        defaultOption.value = "";
                        defaultOption.textContent = "-- Chọn serial --";
                        select.appendChild(defaultOption);

                        // Lấy serial hiện tại nếu có
                        const currentSerial = row.querySelector(
                            `input[name*='serials'][data-serial-index='${i}']`
                        )?.value;

                        // Thêm các option từ dữ liệu API
                        data.serials.forEach((serial) => {
                            const option = document.createElement("option");
                            option.value = serial;
                            option.textContent = serial;
                            if (serial === currentSerial) {
                                option.selected = true;
                            }
                            select.appendChild(option);
                        });

                        // Thêm hidden input để lưu ID
                        const hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.className = "serial-id-input";
                        hiddenInput.name =
                            quantity > 1
                                ? `components[${
                                      row.dataset.index || 0
                                  }][serial_ids_product_${productIndex}][]`
                                : `components[${
                                      row.dataset.index || 0
                                  }][serial_id_product_${productIndex}]`;

                        // Thêm các phần tử vào container
                        productContainer.appendChild(select);
                        productContainer.appendChild(hiddenInput);

                        // Thêm event listener
                        select.addEventListener("change", function () {
                            const selectedOption =
                                this.options[this.selectedIndex];
                            hiddenInput.value =
                                selectedOption?.getAttribute("data-id") || "";
                        });
                    }
                } else {
                    // Nếu không có serial
                    const noSerialDiv = document.createElement("div");
                    noSerialDiv.className = "text-gray-500";
                    noSerialDiv.textContent = "Không có serial cho vật tư này";
                    productContainer.appendChild(noSerialDiv);
                }
            })
            .catch((error) => {
                console.error("Error fetching serials:", error);

                // Xóa loading
                const loadingIndicator =
                    productContainer.querySelector(".loading-indicator");
                if (loadingIndicator) {
                    productContainer.removeChild(loadingIndicator);
                }

                // Hiển thị lỗi
                const errorDiv = document.createElement("div");
                errorDiv.className = "text-red-500";
                errorDiv.textContent = `Lỗi: ${error.message}`;
                productContainer.appendChild(errorDiv);
            });
    }

    // Hàm cập nhật số lượng thành phẩm
    function updateProductQuantity(productId, newQuantity) {
        console.log(`Updating product ${productId} quantity to ${newQuantity}`);

        // Tìm các hàng vật tư của thành phẩm này
        const componentRows = findComponentRows(productId);
        if (!componentRows || componentRows.length === 0) {
            console.error(`No component rows found for product ${productId}`);
            return;
        }

        console.log(
            `Found ${componentRows.length} component rows for product ${productId}`
        );

        // Tìm thông tin thành phẩm
        const productObject = window.selectedProducts?.find(
            (p) => p.uniqueId === productId
        );
        if (!productObject) {
            console.error(`Product object not found for ${productId}`);
            return;
        }

        const productName = productObject.name || "Thành phẩm";

        // Ẩn các hàng component gốc vì chúng ta sẽ hiển thị theo đơn vị thành phẩm
        componentRows.forEach((row) => {
            row.style.display = "none";
        });

        // Tìm container của sản phẩm này - nếu chưa có, tạo mới
        let productUnitContainers = document.querySelectorAll(
            `.product-units-container[data-product-id="${productId}"]`
        );

        // Nếu không tìm thấy, tạo container cha để chứa tất cả các đơn vị
        if (productUnitContainers.length === 0) {
            // Tạo container cha ở cuối bảng thành phần
            const componentList = document.getElementById(
                `component_list_${productId}`
            );
            if (!componentList) {
                console.error(
                    `Component list for product ${productId} not found`
                );
                return;
            }

            const containerRow = document.createElement("tr");
            containerRow.className = "product-units-row";
            containerRow.innerHTML = `
                <td colspan="8" class="px-0 py-2">
                    <div class="product-units-container" data-product-id="${productId}"></div>
            </td>
        `;

            componentList.appendChild(containerRow);
            productUnitContainers = document.querySelectorAll(
                `.product-units-container[data-product-id="${productId}"]`
            );
        }

        // Lấy container cha
        const productUnitsContainer = productUnitContainers[0];

        // Lưu lại các dữ liệu đã nhập trước khi xóa container
        const savedData = {};
        const existingContainers = productUnitsContainer.querySelectorAll(
            ".product-unit-container"
        );
        existingContainers.forEach((container) => {
            const unitIndex = parseInt(
                container.getAttribute("data-unit-index")
            );
            if (isNaN(unitIndex)) return;

            savedData[unitIndex] = {
                notes: {},
                serials: {},
            };

            // Lưu các ghi chú
            const noteInputs = container.querySelectorAll(".material-note");
            noteInputs.forEach((input) => {
                const componentIndex = input.getAttribute(
                    "data-component-index"
                );
                if (componentIndex) {
                    savedData[unitIndex].notes[componentIndex] = input.value;
                }
            });

            // Lưu các serial đã chọn
            const serialSelects = container.querySelectorAll(".serial-select");
            serialSelects.forEach((select) => {
                const materialId = select.getAttribute("data-material-id");
                if (materialId) {
                    const serialId = container.querySelector(
                        `.serial-id[data-material-id="${materialId}"]`
                    )?.value;
                    savedData[unitIndex].serials[materialId] = {
                        value: select.value,
                        serialId: serialId,
                    };
                }
            });
        });

        // Xóa tất cả container đơn vị cũ
        productUnitsContainer.innerHTML = "";

        // Tạo các container mới cho từng đơn vị thành phẩm
        for (let i = 0; i < newQuantity; i++) {
            // Tạo container cho đơn vị thành phẩm
            const unitContainer = document.createElement("div");
            unitContainer.className =
                "product-unit-container mb-5 p-3 border border-blue-200 rounded bg-blue-50";
            unitContainer.setAttribute("data-unit-index", i);
            unitContainer.setAttribute("data-product-id", productId);

            // Thêm tiêu đề cho đơn vị thành phẩm kèm tên thành phẩm
            const unitHeader = document.createElement("div");
            unitHeader.className =
                "font-medium text-blue-700 mb-3 pb-2 border-b border-blue-200";
            unitHeader.innerHTML = `<i class="fas fa-box-open mr-1"></i> ${productName} - Đơn vị thành phẩm ${
                i + 1
            }`;
            unitContainer.appendChild(unitHeader);

            // Tạo bảng chứa các vật tư cho đơn vị này
            const materialsTable = document.createElement("table");
            materialsTable.className = "min-w-full divide-y divide-gray-200";
            materialsTable.innerHTML = `
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mã</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tên vật tư</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Số lượng</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ghi chú</th>
                            </tr>
                        </thead>
                <tbody class="bg-white divide-y divide-gray-200 material-rows-container"></tbody>
            `;
            unitContainer.appendChild(materialsTable);

            // Thêm container đơn vị vào container cha
            productUnitsContainer.appendChild(unitContainer);

            // Thêm các hàng vật tư vào bảng của đơn vị này
            const materialRowsContainer = materialsTable.querySelector(
                ".material-rows-container"
            );

            // Thêm từng vật tư vào bảng
            componentRows.forEach((originalRow, rowIndex) => {
                // Lấy thông tin vật tư
                const materialId =
                    originalRow.getAttribute("data-component-id") ||
                    originalRow.getAttribute("data-material-id") ||
                    originalRow.querySelector("input[name*='[id]']")?.value;

                const materialCode =
                    originalRow
                        .querySelector("td:nth-child(1)")
                        ?.textContent.trim() || "";
                const materialName =
                    originalRow
                        .querySelector("td:nth-child(3)")
                        ?.textContent.trim() || "";
                const quantityInput = originalRow.querySelector(
                    ".component-quantity-input"
                );
                const quantity = quantityInput ? quantityInput.value : "1";

                // Tìm component trong selectedComponents
                const component = window.selectedComponents?.find(
                    (c) => c.id == materialId && c.productId === productId
                );

                if (!component) {
                    console.error(
                        `Component with ID ${materialId} not found in selectedComponents`
                    );
                    return;
                }

                // Tạo hàng cho vật tư
                const materialRow = document.createElement("tr");
                materialRow.className = "material-row";
                materialRow.setAttribute("data-material-id", materialId);
                materialRow.setAttribute("data-unit-index", i);
                materialRow.setAttribute("data-row-index", rowIndex);

                // Cấu trúc hàng vật tư
                materialRow.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">${materialCode}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">${materialName}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">${quantity}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 serial-cell"></td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                        <input type="text" class="w-full border border-gray-300 rounded px-2 py-1 material-note" 
                               placeholder="Ghi chú" data-component-index="${rowIndex}">
                    </td>
                `;

                materialRowsContainer.appendChild(materialRow);

                // Khôi phục giá trị ghi chú nếu có
                if (savedData[i] && savedData[i].notes[rowIndex]) {
                    const noteInput =
                        materialRow.querySelector(".material-note");
                    if (noteInput) {
                        noteInput.value = savedData[i].notes[rowIndex];
                    }
                }

                // Thêm input serial cho vật tư này
                const serialCell = materialRow.querySelector(".serial-cell");
                if (serialCell) {
                    // Tạo select box cho serial
                    createSerialSelector(serialCell, component, i, rowIndex);

                    // Khôi phục giá trị serial nếu có
                    if (savedData[i] && savedData[i].serials[materialId]) {
                        const serialSelect =
                            serialCell.querySelector(".serial-select");
                        const serialIdInput =
                            serialCell.querySelector(".serial-id");

                        if (serialSelect && serialIdInput) {
                            // Thêm option tạm thời để giữ giá trị
                            const tempOption = document.createElement("option");
                            tempOption.value =
                                savedData[i].serials[materialId].value;
                            tempOption.textContent =
                                savedData[i].serials[materialId].value;
                            tempOption.setAttribute(
                                "data-serial-id",
                                savedData[i].serials[materialId].serialId
                            );
                            serialSelect.appendChild(tempOption);
                            serialSelect.value =
                                savedData[i].serials[materialId].value;
                            serialIdInput.value =
                                savedData[i].serials[materialId].serialId;
                        }
                    }

                    // Nếu đã chọn kho, tự động tải danh sách serial
                    const warehouseId =
                        document.getElementById("warehouse_id")?.value;
                    if (warehouseId && component) {
                        const serialSelect =
                            serialCell.querySelector(".serial-select");
                        if (serialSelect) {
                            loadSerials(serialSelect, component, i);
                        }
                    }
                }
            });
        }
    }

    // Hàm tạo select box cho serial
    function createSerialSelector(container, component, unitIndex, rowIndex) {
        // Xóa nội dung hiện tại
        container.innerHTML = "";

        // Tạo container cho select và button
        const selectorContainer = document.createElement("div");
        selectorContainer.className = "serial-selector-container";

        // Tạo select box
        const select = document.createElement("select");
        select.className =
            "serial-select w-full border border-gray-300 rounded px-2 py-1";
        select.setAttribute("data-material-id", component.id);
        select.setAttribute("data-unit-index", unitIndex);
        select.setAttribute("data-row-index", rowIndex);
        select.name = `components[${rowIndex}][serial_product_${unitIndex}]`;

        // Thêm option mặc định
        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "-- Chọn serial --";
        select.appendChild(defaultOption);

        // Thêm nút tải serial
        const fetchButton = document.createElement("button");
        fetchButton.type = "button";
        fetchButton.className =
            "fetch-serials-btn mt-1 text-xs text-white bg-blue-500 hover:bg-blue-600 px-2 py-1 rounded";
        fetchButton.innerHTML = `<i class="fas fa-sync mr-1"></i> Tải danh sách serial`;
        fetchButton.addEventListener("click", function () {
            loadSerials(select, component, unitIndex);
        });

        // Thêm hidden input cho serial_id
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = `components[${rowIndex}][serial_id_product_${unitIndex}]`;
        hiddenInput.className = "serial-id";
        hiddenInput.setAttribute("data-material-id", component.id);

        // Thêm sự kiện change cho select
        select.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            const serialId =
                selectedOption?.getAttribute("data-serial-id") || "";
            hiddenInput.value = serialId;

            // Hiển thị thông báo nếu đã chọn serial
            const statusDiv = container.querySelector(".serial-status");
            if (this.value) {
                if (!statusDiv) {
                    const newStatusDiv = document.createElement("div");
                    newStatusDiv.className =
                        "serial-status text-xs text-green-600 mt-1";
                    newStatusDiv.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Đã chọn serial`;
                    container.appendChild(newStatusDiv);
                }
            } else if (statusDiv) {
                container.removeChild(statusDiv);
            }

            // Cập nhật selectedComponents
            if (component) {
                if (!component.serialsByUnit) component.serialsByUnit = {};
                component.serialsByUnit[unitIndex] = {
                    serial: this.value,
                    serial_id: serialId,
                };
            }
        });

        // Thêm vào container
        selectorContainer.appendChild(select);
        selectorContainer.appendChild(hiddenInput);
        container.appendChild(selectorContainer);
        container.appendChild(fetchButton);

        // Nếu đã chọn kho, tự động tải danh sách serial
        const warehouseId = document.getElementById("warehouse_id")?.value;
        if (warehouseId && component) {
            setTimeout(() => {
                loadSerials(select, component, unitIndex);
            }, 100);
        }
    }

    // Hàm tải danh sách serial cho select box
    function loadSerials(selectElement, component, unitIndex) {
        // Lấy thông tin cần thiết
        const materialId = component.id;
        const warehouseId =
            document.getElementById("warehouse_id")?.value || "";

        // Lưu giá trị đã chọn trước khi tải lại
        const currentValue = selectElement.value;
        const currentSerialId =
            selectElement.parentElement.querySelector(".serial-id")?.value;

        // Tạo nút tải
        const fetchButton =
            selectElement.parentElement.querySelector(".fetch-serials-btn");

        // Kiểm tra nếu chưa chọn kho
        if (!warehouseId) {
            // Không hiển thị alert để tránh làm phiền người dùng
            console.log("Chưa chọn kho xuất, không thể tải danh sách serial");

            // Xóa options hiện tại trừ option mặc định
            while (selectElement.options.length > 1) {
                selectElement.remove(1);
            }

            // Thêm thông báo
            const noWarehouseOption = document.createElement("option");
            noWarehouseOption.disabled = true;
            noWarehouseOption.textContent = "Vui lòng chọn kho xuất";
            selectElement.appendChild(noWarehouseOption);

            return;
        }

        // Hiển thị đang tải
        if (fetchButton) {
            fetchButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Đang tải...`;
            fetchButton.disabled = true;
        }

        console.log(
            `Tải danh sách serial cho vật tư ${materialId}, kho ${warehouseId}, đơn vị ${unitIndex}`
        );

        // Gọi API lấy danh sách serial
        fetch("/assemblies/material-serials", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                material_id: materialId,
                warehouse_id: warehouseId,
                product_index: unitIndex,
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                console.log("Kết quả API:", data);

                // Xóa options hiện tại trừ option mặc định
                while (selectElement.options.length > 1) {
                    selectElement.remove(1);
                }

                // Thêm options mới
                if (data.success && data.serials && data.serials.length > 0) {
                    data.serials.forEach((serial) => {
                        const option = document.createElement("option");
                        option.value = serial.serial_number;
                        option.textContent = serial.serial_number;
                        option.setAttribute("data-serial-id", serial.id);
                        selectElement.appendChild(option);
                    });

                    // Khôi phục giá trị đã chọn nếu có
                    if (currentValue) {
                        let found = false;
                        for (let i = 0; i < selectElement.options.length; i++) {
                            if (
                                selectElement.options[i].value === currentValue
                            ) {
                                selectElement.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }

                        // Nếu không tìm thấy serial cũ trong danh sách mới, thêm lại vào
                        if (!found && currentValue) {
                            const tempOption = document.createElement("option");
                            tempOption.value = currentValue;
                            tempOption.textContent =
                                currentValue + " (Không có sẵn)";
                            tempOption.setAttribute(
                                "data-serial-id",
                                currentSerialId
                            );
                            tempOption.className = "text-amber-500";
                            selectElement.appendChild(tempOption);
                            selectElement.value = currentValue;

                            // Cập nhật hidden input
                            const hiddenInput =
                                selectElement.parentElement.querySelector(
                                    ".serial-id"
                                );
                            if (hiddenInput) {
                                hiddenInput.value = currentSerialId;
                            }
                        }
                    }

                    // Khôi phục giá trị đã lưu trong component
                    if (
                        !currentValue &&
                        component.serialsByUnit &&
                        component.serialsByUnit[unitIndex]
                    ) {
                        const savedSerial =
                            component.serialsByUnit[unitIndex].serial;
                        if (savedSerial) {
                            for (
                                let i = 0;
                                i < selectElement.options.length;
                                i++
                            ) {
                                if (
                                    selectElement.options[i].value ===
                                    savedSerial
                                ) {
                                    selectElement.selectedIndex = i;

                                    // Cập nhật hidden input
                                    const hiddenInput =
                                        selectElement.parentElement.querySelector(
                                            ".serial-id"
                                        );
                                    if (hiddenInput) {
                                        hiddenInput.value =
                                            component.serialsByUnit[
                                                unitIndex
                                            ].serial_id;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    // Thêm thông báo nếu không có serial
                    const noSerialOption = document.createElement("option");
                    noSerialOption.disabled = true;
                    noSerialOption.textContent = "Không có serial khả dụng";
                    selectElement.appendChild(noSerialOption);

                    // Nếu có giá trị cũ, thêm lại
                    if (currentValue) {
                        const tempOption = document.createElement("option");
                        tempOption.value = currentValue;
                        tempOption.textContent =
                            currentValue + " (Đã chọn trước đó)";
                        tempOption.setAttribute(
                            "data-serial-id",
                            currentSerialId
                        );
                        tempOption.className = "text-amber-500";
                        selectElement.appendChild(tempOption);
                        selectElement.value = currentValue;

                        // Cập nhật hidden input
                        const hiddenInput =
                            selectElement.parentElement.querySelector(
                                ".serial-id"
                            );
                        if (hiddenInput) {
                            hiddenInput.value = currentSerialId;
                        }
                    }
                }

                // Khôi phục nút tải
                if (fetchButton) {
                    fetchButton.innerHTML = `<i class="fas fa-sync mr-1"></i> Tải lại`;
                    fetchButton.disabled = false;
                }
            })
            .catch((error) => {
                console.error("Error loading serials:", error);

                // Hiển thị lỗi
                const errorOption = document.createElement("option");
                errorOption.disabled = true;
                errorOption.textContent = "Lỗi tải serial: " + error.message;

                // Xóa options hiện tại trừ option mặc định
                while (selectElement.options.length > 1) {
                    selectElement.remove(1);
                }

                selectElement.appendChild(errorOption);

                // Nếu có giá trị cũ, thêm lại
                if (currentValue) {
                    const tempOption = document.createElement("option");
                    tempOption.value = currentValue;
                    tempOption.textContent = currentValue;
                    tempOption.setAttribute("data-serial-id", currentSerialId);
                    selectElement.appendChild(tempOption);
                    selectElement.value = currentValue;

                    // Cập nhật hidden input
                    const hiddenInput =
                        selectElement.parentElement.querySelector(".serial-id");
                    if (hiddenInput) {
                        hiddenInput.value = currentSerialId;
                    }
                }

                // Khôi phục nút tải
                if (fetchButton) {
                    fetchButton.innerHTML = `<i class="fas fa-sync mr-1"></i> Thử lại`;
                    fetchButton.disabled = false;
                }
            });
    }

    // Khởi tạo các hàng có sẵn
    function initExistingRows() {
        // Lấy danh sách tất cả các bảng thành phần
        const componentLists = document.querySelectorAll(
            "[id^='component_list_']"
        );
        componentLists.forEach((componentList) => {
            const productId = componentList.id.replace("component_list_", "");

            // Tìm số lượng thành phẩm
            const productRow = document.querySelector(
                `tr[data-product-id="${productId}"]`
            );
            const productQuantity = productRow
                ? parseInt(
                      productRow.querySelector("input[name*='[quantity]']")
                          ?.value
                  ) || 1
                : 1;

            console.log(
                `Initializing rows for product ${productId} with quantity ${productQuantity}`
            );

            // Lấy danh sách các hàng
            const rows = componentList.querySelectorAll("tr.component-row");
            rows.forEach((row) => {
                // Trigger loading of serials for existing selects
                const serialSelects = row.querySelectorAll(
                    ".material-serial-select"
                );
                serialSelects.forEach((select) => {
                    const materialId = select.dataset.materialId;
                    const warehouseId = select.dataset.warehouseId;
                    const currentSerial = select.dataset.currentSerial;
                    const productUnit = select.dataset.productUnit;
                    const assemblyId =
                        document
                            .querySelector("form")
                            ?.action.split("/")
                            .pop() || null;

                    console.log("Processing select:", {
                        materialId,
                        warehouseId,
                        currentSerial,
                        productUnit,
                        assemblyId,
                    });

                    if (materialId && warehouseId) {
                        // Save current selection if any
                        const currentSelection = select.value;

                        // Fetch serials from API
                        fetch(
                            `/assemblies/material-serials?material_id=${materialId}&warehouse_id=${warehouseId}&assembly_id=${assemblyId}&product_unit=${productUnit}`,
                            {
                                method: "GET",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute("content"),
                                },
                            }
                        )
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success && data.serials) {
                                    console.log(
                                        "Received serials:",
                                        data.serials
                                    );

                                    // Clear existing options except the first one
                                    while (select.options.length > 1) {
                                        select.remove(1);
                                    }

                                    // Add serial options
                                    data.serials.forEach((serial) => {
                                        const option =
                                            document.createElement("option");
                                        option.value = serial.serial_number;
                                        option.textContent =
                                            serial.serial_number;
                                        option.dataset.serialId = serial.id;

                                        // Select this option if it matches the current serial
                                        if (
                                            currentSerial ===
                                            serial.serial_number
                                        ) {
                                            option.selected = true;
                                        }

                                        select.appendChild(option);
                                    });

                                    // If we have a current serial but it wasn't in the list, add it
                                    if (
                                        currentSerial &&
                                        !Array.from(select.options).some(
                                            (opt) => opt.value === currentSerial
                                        )
                                    ) {
                                        console.log(
                                            "Adding current serial that was not in list:",
                                            currentSerial
                                        );
                                        const option =
                                            document.createElement("option");
                                        option.value = currentSerial;
                                        option.textContent = currentSerial;
                                        option.selected = true;
                                        select.appendChild(option);
                                    }

                                    // Restore previous selection if it exists
                                    if (
                                        currentSelection &&
                                        currentSelection !== ""
                                    ) {
                                        select.value = currentSelection;
                                    }
                                } else {
                                    console.error(
                                        "Failed to load serials:",
                                        data
                                    );
                                }
                            })
                            .catch((error) => {
                                console.error("Error loading serials:", error);
                            });
                    } else {
                        console.error("Missing required data:", {
                            materialId,
                            warehouseId,
                        });
                    }
                });

                // Cập nhật hiển thị số lượng thành phẩm
                const quantityCell = row.querySelector(
                    ".component-quantity-cell"
                );
                if (quantityCell) {
                    const quantityDisplay = document.createElement("div");
                    quantityDisplay.className =
                        "quantity-display text-xs text-blue-600 mt-1";
                    quantityDisplay.textContent = `${productQuantity} thành phẩm`;
                    quantityCell.appendChild(quantityDisplay);
                }
            });
        });
    }

    // Khởi tạo khi trang đã tải
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(initExistingRows, 500);
        initMaterialSerials();
    });

    // Lưu các callback ban đầu và ghi đè hàm updateProductQuantity
    window.updateProductQuantity = function (input) {
        console.log("updateProductQuantity called with", input);

        // Tìm hàng sản phẩm
        const productRow = findAncestor(input, "TR");
        if (!productRow) {
            console.error("Product row not found");
            return;
        }

        // Lấy data-product-id attribute từ hàng
        const productId = productRow.getAttribute("data-product-id");
        if (!productId) {
            console.error("Product ID not found in row");
            return;
        }

        // Lấy thông tin sản phẩm từ data-index
        const productIndex = parseInt(input.getAttribute("data-index"));
        if (isNaN(productIndex)) {
            console.error("Product index not found");
            return;
        }

        // Tìm đối tượng sản phẩm trong mảng selectedProducts
        const productObject = window.selectedProducts?.[productIndex];
        if (!productObject) {
            console.error("Product object not found at index", productIndex);
            return;
        }

        // Lấy số lượng mới
        const newQuantity = parseInt(input.value) || 1;
        const oldQuantity = productObject.quantity || 1;

        console.log(
            `Updating product ${productId} (${productObject.name}) quantity from ${oldQuantity} to ${newQuantity}`
        );

        // Cập nhật số lượng trong đối tượng sản phẩm
        productObject.quantity = newQuantity;

        // Cập nhật serial của thành phẩm
        const serialCell = document.getElementById(`${productId}_serials`);
        if (serialCell) {
            console.log("Updating product serials for", productId);

            // Lưu lại các giá trị serial hiện tại
            const currentSerials = [];
            const existingInputs =
                serialCell.querySelectorAll('input[type="text"]');
            existingInputs.forEach((input) => {
                currentSerials.push(input.value || "");
            });

            // Đảm bảo mảng serials có đúng độ dài
            if (!productObject.serials) productObject.serials = [];

            // Sao chép các giá trị hiện tại vào mảng serials
            for (
                let i = 0;
                i < Math.min(currentSerials.length, oldQuantity);
                i++
            ) {
                productObject.serials[i] = currentSerials[i];
            }

            // Nếu số lượng tăng, thêm serial trống
            while (productObject.serials.length < newQuantity) {
                productObject.serials.push("");
            }

            // Nếu số lượng giảm, cắt bớt mảng
            if (productObject.serials.length > newQuantity) {
                productObject.serials = productObject.serials.slice(
                    0,
                    newQuantity
                );
            }

            // Tạo container cho serial inputs
            const serialContainer = document.createElement("div");
            serialContainer.className = "space-y-2";

            // Clear cell content
            while (serialCell.firstChild) {
                serialCell.removeChild(serialCell.firstChild);
            }

            // Add container to cell
            serialCell.appendChild(serialContainer);

            // Add serial inputs
            for (let i = 0; i < newQuantity; i++) {
                const input = document.createElement("input");
                input.type = "text";
                input.name = `products[${productIndex}][serials][]`;
                input.value = productObject.serials[i] || "";
                input.placeholder = `Serial ${i + 1} (tùy chọn)`;
                input.className =
                    "w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500";

                // Add event listener to update serials array
                input.addEventListener("input", function () {
                    productObject.serials[i] = this.value;
                    if (typeof window.updateHiddenProductList === "function") {
                        window.updateHiddenProductList();
                    }
                });

                serialContainer.appendChild(input);
            }

            // Thêm thông báo nếu có nhiều serial
            if (newQuantity > 3) {
                const noteDiv = document.createElement("div");
                noteDiv.className = "mt-1 text-xs text-blue-700";
                noteDiv.innerHTML =
                    '<i class="fas fa-info-circle mr-1"></i> ' +
                    newQuantity +
                    " serials";
                serialCell.appendChild(noteDiv);
            }

            // Update hidden inputs
            if (typeof window.updateHiddenProductList === "function") {
                window.updateHiddenProductList();
            }
        }

        // Gọi hàm cập nhật container đơn vị thành phẩm
        updateProductQuantity(productId, newQuantity);
    };

    // Hàm xử lý khi số lượng vật tư thay đổi
    function handleComponentQuantityChange(row) {
        // Lấy thông tin cần thiết
        const quantityInput = row.querySelector(".component-quantity-input");
        if (!quantityInput) return;

        const quantity = parseInt(quantityInput.value) || 1;
        const serialCell = row.querySelector(".serial-cell");
        if (!serialCell) return;

        // Lấy ID của component
        const componentId =
            row.getAttribute("data-component-id") ||
            row.getAttribute("data-material-id") ||
            row.querySelector("input[name*='[id]']")?.value;

        const productId =
            row
                .closest("[id^='component_list_']")
                ?.id.replace("component_list_", "") ||
            row.getAttribute("data-product-id");

        if (!componentId || !productId) {
            console.error("Missing required IDs:", { componentId, productId });
            return;
        }

        // Tìm số lượng thành phẩm
        const productRow = document.querySelector(
            `tr[data-product-id="${productId}"]`
        );
        const productQuantity = productRow
            ? parseInt(
                  productRow.querySelector("input[name*='[quantity]']")?.value
              ) || 1
            : 1;

        // Cập nhật từng container thành phẩm
        for (let i = 0; i < productQuantity; i++) {
            fetchMaterialSerials(row, i);
        }
    }

    // Ghi đè hàm updateQuantity
    const originalUpdateQuantity = window.updateQuantity;
    if (typeof originalUpdateQuantity === "function") {
        window.updateQuantity = function (element) {
            // Gọi hàm gốc trước
            const result = originalUpdateQuantity.apply(this, arguments);

            // Sau khi cập nhật số lượng, tìm hàng và xử lý lại serial
            const row = findAncestor(element, "TR");
            if (row) {
                handleComponentQuantityChange(row);
            }

            return result;
        };
    }

    // Ghi đè hàm addMaterial
    const originalAddMaterial = window.addMaterial;
    if (typeof originalAddMaterial === "function") {
        window.addMaterial = function () {
            // Gọi hàm gốc trước
            const result = originalAddMaterial.apply(this, arguments);

            // Sau khi thêm hàng mới, tìm hàng vừa thêm và khởi tạo
            const productId = arguments[0];
            if (productId) {
                const rows = findComponentRows(productId);
                if (rows.length > 0) {
                    // Hàng mới thường là hàng cuối cùng
                    const newRow = rows[rows.length - 1];

                    // Tìm số lượng thành phẩm
                    const productRow = document.querySelector(
                        `tr[data-product-id="${productId}"]`
                    );
                    const productQuantity = productRow
                        ? parseInt(
                              productRow.querySelector(
                                  "input[name*='[quantity]']"
                              )?.value
                          ) || 1
                        : 1;

                    // Tạo container cho từng thành phẩm
                    for (let i = 0; i < productQuantity; i++) {
                        fetchMaterialSerials(newRow, i);
                    }

                    // Cập nhật hiển thị số lượng thành phẩm
                    const quantityCell = newRow.querySelector(
                        ".component-quantity-cell"
                    );
                    if (quantityCell) {
                        const quantityDisplay = document.createElement("div");
                        quantityDisplay.className =
                            "quantity-display text-xs text-blue-600 mt-1";
                        quantityDisplay.textContent = `${productQuantity} thành phẩm`;
                        quantityCell.appendChild(quantityDisplay);
                    }
                }
            }

            return result;
        };
    }

    // Add the missing addSelectedComponent function
    window.addSelectedComponent = function (
        selectedMaterial,
        selectedProduct,
        quantity
    ) {
        // Kiểm tra dữ liệu đầu vào
        if (!selectedMaterial) {
            console.error("Missing material data in addSelectedComponent");
            return null;
        }

        if (!selectedProduct) {
            console.error("Missing product data in addSelectedComponent");
            return null;
        }

        // Ensure window.selectedComponents exists
        if (!window.selectedComponents) {
            console.log("Initializing window.selectedComponents array");
            window.selectedComponents = [];
        }

        const selectedProductId = selectedProduct.uniqueId;

        // Create new component object
        const newComponent = {
            id: selectedMaterial.id,
            code: selectedMaterial.code,
            name: selectedMaterial.name,
            category: selectedMaterial.category || "Không xác định",
            quantity: quantity,
            originalQuantity: quantity, // Store original quantity
            stock_quantity: selectedMaterial.stock_quantity || 0,
            serial: "",
            serials: [],
            note: "",
            productId: selectedProductId,
            actualProductId: selectedProduct.id, // Store actual product ID for backend
            productName: selectedProduct.name,
            isEditable: true, // Make component quantities editable
            productUnit: 0, // Default to first product unit (0)
        };

        // Check if component already exists for this product
        const existingIndex = window.selectedComponents.findIndex(
            (comp) =>
                comp.id === newComponent.id &&
                comp.productId === newComponent.productId
        );

        if (existingIndex >= 0) {
            // Ask user if they want to update quantity or add a duplicate
            const updateQuantity = confirm(
                `Linh kiện "${newComponent.name}" đã tồn tại cho sản phẩm "${newComponent.productName}". Bạn muốn cập nhật số lượng thay vì thêm mới?`
            );

            if (updateQuantity) {
                // Update quantity of existing component
                window.selectedComponents[existingIndex].quantity +=
                    newComponent.quantity;
                window.selectedComponents[
                    existingIndex
                ].manuallyAdjusted = true;
                return window.selectedComponents[existingIndex];
            }
        }

        // Add new component
        window.selectedComponents.push(newComponent);

        // Log success for debugging
        console.log(
            `Added component ${newComponent.name} to product ${newComponent.productName}`,
            newComponent
        );

        // Return the new component
        return newComponent;
    };

    // Công khai các hàm cần thiết
    window.fetchMaterialSerials = function (element) {
        // Prevent recursive calls
        if (isFetchingSerials) {
            console.log("Already fetching serials, skipping duplicate call");
            return;
        }

        isFetchingSerials = true;

        try {
            const row = findAncestor(element, "TR");
            if (!row) {
                console.log("No row found for element", element);
                isFetchingSerials = false;
                return;
            }

            // Lấy ID của component
            const componentId =
                row.getAttribute("data-component-id") ||
                row.getAttribute("data-material-id") ||
                row.querySelector("input[name*='[id]']")?.value;

            const productId =
                row
                    .closest("[id^='component_list_']")
                    ?.id.replace("component_list_", "") ||
                row.getAttribute("data-product-id");

            if (!componentId || !productId) {
                console.error("Missing required IDs:", {
                    componentId,
                    productId,
                });
                return;
            }

            // Tìm số lượng thành phẩm
            const productRow = document.querySelector(
                `tr[data-product-id="${productId}"]`
            );
            const productQuantity = productRow
                ? parseInt(
                      productRow.querySelector("input[name*='[quantity]']")
                          ?.value
                  ) || 1
                : 1;

            // Nếu element là select đơn vị, chỉ cập nhật container cho đơn vị đó
            if (element.classList.contains("product-unit-select")) {
                const productUnit = parseInt(element.value) || 0;
                console.log(
                    `Updating only product unit ${productUnit} for component ${componentId}`
                );
                fetchMaterialSerials(row, productUnit);
            } else {
                // Cập nhật tất cả các container thành phẩm
                console.log(
                    `Updating all ${productQuantity} product units for component ${componentId}`
                );
                for (let i = 0; i < productQuantity; i++) {
                    fetchMaterialSerials(row, i);
                }
            }
        } catch (e) {
            console.error("Error in fetchMaterialSerials:", e);
        } finally {
            // Reset the flag when done
            setTimeout(() => {
                isFetchingSerials = false;
                console.log("Reset isFetchingSerials flag");
            }, 200);
        }
    };

    // Update hidden component list for form submission
    function updateHiddenComponentList() {
        // Get the old component list (hidden but needed for form submission)
        const oldComponentList = document.getElementById("component_list");
        const noComponentsRow = document.getElementById("no_components_row");

        // Remove all rows except the no_components_row
        Array.from(oldComponentList.children).forEach((child) => {
            if (child.id !== "no_components_row") {
                oldComponentList.removeChild(child);
            }
        });

        // Show/hide no components message
        if (selectedComponents.length === 0) {
            noComponentsRow.style.display = "";
            return;
        } else {
            noComponentsRow.style.display = "none";
        }

        // Tạo một mảng mới để lưu dữ liệu từ bảng đơn vị sản phẩm
        const processedComponents = [];

        // Lấy tất cả container đơn vị sản phẩm
        const unitContainers = document.querySelectorAll(
            ".product-unit-container"
        );

        // Duyệt qua từng container đơn vị sản phẩm
        unitContainers.forEach((container) => {
            const unitIndex = parseInt(
                container.getAttribute("data-unit-index")
            );
            const productId = container
                .closest(".product-units-container")
                .getAttribute("data-product-id");

            // Duyệt qua các hàng vật tư trong container này
            const materialRows = container.querySelectorAll(".material-row");
            materialRows.forEach((row) => {
                const materialId = row.getAttribute("data-material-id");
                const serialSelect = row.querySelector(".serial-select");
                const serialIdInput = row.querySelector(".serial-id");
                const noteInput = row.querySelector(".material-note");

                // Tìm component tương ứng trong selectedComponents
                const component = selectedComponents.find(
                    (c) => c.id == materialId && c.productId === productId
                );

                if (component) {
                    // Tạo đối tượng mới để lưu thông tin component
                    const processedComponent = {
                        id: component.id,
                        productId: component.productId,
                        actualProductId: component.actualProductId,
                        quantity: component.quantity,
                        unitIndex: unitIndex,
                        serial: serialSelect ? serialSelect.value : "",
                        serialId: serialIdInput ? serialIdInput.value : "",
                        note: noteInput ? noteInput.value : "",
                    };

                    processedComponents.push(processedComponent);
                }
            });
        });

        // Add all components to the hidden list with proper form fields
        processedComponents.forEach((component, index) => {
            const row = document.createElement("tr");
            row.style.display = "none"; // Hide row but keep form fields

            row.innerHTML =
                "<td>" +
                '<input type="hidden" name="components[' +
                index +
                '][id]" value="' +
                component.id +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                '][product_id]" value="' +
                (component.actualProductId ||
                    component.productId.replace("product_", "")) +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                '][quantity]" value="' +
                (component.quantity || 1) +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                '][product_unit]" value="' +
                component.unitIndex +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                "][serial_product_" +
                component.unitIndex +
                ']" value="' +
                (component.serial || "") +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                "][serial_id_product_" +
                component.unitIndex +
                ']" value="' +
                (component.serialId || "") +
                '">' +
                '<input type="hidden" name="components[' +
                index +
                '][note]" value="' +
                (component.note || "") +
                '">' +
                "</td>";

            oldComponentList.insertBefore(row, noComponentsRow);
        });
    }

    // Xử lý sự kiện khi thay đổi kho xuất
    const warehouseSelect = document.getElementById("warehouse_id");
    if (warehouseSelect) {
        warehouseSelect.addEventListener("change", function () {
            const warehouseId = this.value;
            if (!warehouseId) return;

            console.log(`Đã thay đổi kho xuất thành: ${warehouseId}`);

            // Lấy danh sách tất cả các thành phẩm
            const productRows = document.querySelectorAll(
                "tr[data-product-id]"
            );

            // Xử lý từng thành phẩm
            productRows.forEach((productRow) => {
                const productId = productRow.getAttribute("data-product-id");
                if (!productId) return;

                // Lấy số lượng thành phẩm
                const quantityInput = productRow.querySelector(
                    'input[name*="[quantity]"]'
                );
                const quantity = parseInt(quantityInput?.value) || 1;

                console.log(
                    `Processing product ${productId} with quantity ${quantity}`
                );

                // Tìm các container đơn vị thành phẩm hiện có
                const productUnitsContainer = document.querySelector(
                    `.product-units-container[data-product-id="${productId}"]`
                );

                // Nếu chưa có container, tạo mới
                if (!productUnitsContainer) {
                    console.log(
                        `No container found for product ${productId}, creating new containers`
                    );
                    updateProductQuantity(productId, quantity);
                    return;
                }

                // Nếu đã có container, tải lại danh sách serial cho từng đơn vị
                console.log(
                    `Found container for product ${productId}, updating serials`
                );

                // Duyệt qua từng container đơn vị
                const unitContainers = productUnitsContainer.querySelectorAll(
                    ".product-unit-container"
                );

                unitContainers.forEach((unitContainer) => {
                    const unitIndex = parseInt(
                        unitContainer.getAttribute("data-unit-index")
                    );
                    if (isNaN(unitIndex)) return;

                    console.log(
                        `Updating serials for product ${productId}, unit ${unitIndex}`
                    );

                    // Duyệt qua từng hàng vật tư trong container
                    const materialRows =
                        unitContainer.querySelectorAll(".material-row");
                    materialRows.forEach((row) => {
                        const materialId = row.getAttribute("data-material-id");
                        if (!materialId) return;

                        // Tìm component tương ứng
                        const component = window.selectedComponents?.find(
                            (c) =>
                                c.id == materialId && c.productId === productId
                        );

                        if (component) {
                            // Tìm select box serial
                            const serialSelect =
                                row.querySelector(".serial-select");
                            if (serialSelect) {
                                // Lưu giá trị hiện tại
                                const currentValue = serialSelect.value;
                                const currentSerialId =
                                    row.querySelector(".serial-id")?.value;

                                // Tải lại danh sách serial
                                loadSerials(serialSelect, component, unitIndex);

                                console.log(
                                    `Updated serials for material ${materialId}, unit ${unitIndex}, current value: ${currentValue}`
                                );
                            }
                        }
                    });
                });
            });
        });
    }

    // Khi form submit, cập nhật lại hidden component list
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function (e) {
            // Cập nhật hidden component list trước khi submit
            if (typeof updateHiddenComponentList === "function") {
                updateHiddenComponentList();
            }

            // Cập nhật hidden product list
            if (typeof window.updateHiddenProductList === "function") {
                window.updateHiddenProductList();
            }

            // Hiển thị log để debug
            console.log(
                "Form submit - updated hidden components:",
                Array.from(
                    document.querySelectorAll('input[name^="components["]')
                ).map((input) => {
                    return { name: input.name, value: input.value };
                })
            );
        });
    }

    // Expose important functions to global scope
    window.updateHiddenComponentList = updateHiddenComponentList;
    window.findComponentRows = findComponentRows;
    window.loadSerials = loadSerials;
    window.createSerialSelector = createSerialSelector;
    window.handleCreateNewProduct = handleCreateNewProduct;

    // Thêm event listener cho nút tạo thành phẩm mới
    document.addEventListener("click", function (e) {
        if (
            e.target.matches(".create-new-product-btn") ||
            e.target.closest(".create-new-product-btn")
        ) {
            const button = e.target.matches(".create-new-product-btn")
                ? e.target
                : e.target.closest(".create-new-product-btn");
            handleCreateNewProduct(button);
        }
    });
});

// Hàm xử lý tạo thành phẩm mới
function handleCreateNewProduct(button) {
    // Nếu đang xử lý, không cho gọi lại
    if (window.isCreatingProduct === true) {
        console.log("Đang xử lý yêu cầu tạo thành phẩm, vui lòng đợi...");
        return;
    }

    // Đặt cờ là đang xử lý
    window.isCreatingProduct = true;

    const productId = button.getAttribute("data-product-id");
    const uniqueId = button.getAttribute("data-unique-id");

    console.log("Creating new product from:", {
        productId,
        uniqueId,
        buttonAttributes: {
            "data-product-id": button.getAttribute("data-product-id"),
            "data-unique-id": button.getAttribute("data-unique-id"),
        },
    });

    if (!productId || productId === "undefined") {
        Swal.fire({
            icon: "error",
            title: "Lỗi!",
            text: "Không tìm thấy thông tin sản phẩm gốc.",
            confirmButtonText: "Đóng",
        });
        window.isCreatingProduct = false;
        return;
    }

    // Tìm tất cả các hàng component cho sản phẩm này
    const componentRows = findComponentRows(uniqueId);

    console.log(
        `Found ${componentRows.length} component rows for product ${uniqueId}`
    );

    if (!componentRows || componentRows.length === 0) {
        Swal.fire({
            icon: "error",
            title: "Lỗi!",
            text: "Không tìm thấy thông tin linh kiện.",
            confirmButtonText: "Đóng",
        });
        window.isCreatingProduct = false;
        return;
    }

    // Hiển thị loading
    Swal.fire({
        title: "Đang xử lý...",
        text: "Vui lòng chờ trong giây lát",
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    // Thu thập thông tin về các component
    const components = componentRows
        .map((row) => {
            // Tìm ID vật tư theo nhiều cách khác nhau để đảm bảo lấy được đúng ID
            const materialId =
                row.getAttribute("data-material-id") ||
                row.getAttribute("data-component-id") ||
                row.querySelector('input[name*="[id]"]')?.value;

            // Tìm input số lượng
            const quantityInput =
                row.querySelector(".component-quantity-input") ||
                row.querySelector('input[name*="[quantity]"]');

            // Tìm input ghi chú
            const notesInput =
                row.querySelector('input[name*="[notes]"]') ||
                row.querySelector('textarea[name*="[notes]"]') ||
                row.querySelector('input[name*="[note]"]');

            const quantity = quantityInput
                ? parseFloat(quantityInput.value) || 0
                : 0;
            const notes = notesInput ? notesInput.value : "";

            console.log("Component data:", { materialId, quantity, notes });

            return {
                id: materialId,
                quantity: quantity,
                notes: notes,
            };
        })
        .filter((component) => component.id && component.quantity > 0);

    console.log("Collected components data:", components);

    if (components.length === 0) {
        Swal.close();
        Swal.fire({
            icon: "error",
            title: "Lỗi!",
            text: "Không có linh kiện nào được thêm vào thành phẩm.",
            confirmButtonText: "Đóng",
        });
        window.isCreatingProduct = false;
        return;
    }

    // Prepare the request data
    const requestData = {
        original_product_id: productId,
        components: components,
    };

    console.log("Sending API request with data:", requestData);

    // Get CSRF token from meta tag
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    // Gọi API để tạo sản phẩm mới
    fetch("/api/products/create-from-assembly", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(requestData),
        credentials: "same-origin", // Include cookies
    })
        .then((response) => {
            if (!response.ok) {
                // Check for 401 Unauthorized
                if (response.status === 401) {
                    // Try to refresh the page to get a new CSRF token
                    console.log("Authentication error, refreshing page...");
                    Swal.fire({
                        icon: "error",
                        title: "Phiên làm việc đã hết hạn",
                        text: "Vui lòng đăng nhập lại để tiếp tục.",
                        confirmButtonText: "Tải lại trang",
                        allowOutsideClick: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                    throw new Error(
                        "Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại."
                    );
                }

                if (
                    response.headers.get("content-type")?.includes("text/html")
                ) {
                    throw new Error(
                        "Phiên làm việc đã hết hạn hoặc bạn không có quyền thực hiện thao tác này"
                    );
                }
                return response.json().then((data) => {
                    throw new Error(
                        data.message || "Có lỗi xảy ra khi tạo thành phẩm mới"
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            console.log("API response:", data);

            if (data.success) {
                // Thêm sản phẩm mới vào dropdown
                const productSelect = document.querySelector(
                    `select[data-unique-id="${uniqueId}"]`
                );
                if (productSelect) {
                    const option = new Option(
                        data.product.name,
                        data.product.id,
                        false,
                        true
                    );
                    productSelect.add(option);
                    productSelect.value = data.product.id;

                    // Cập nhật danh sách sản phẩm đã chọn
                    if (window.selectedProducts) {
                        window.selectedProducts[data.product.id] = data.product;
                    }

                    // Trigger change event để cập nhật UI
                    productSelect.dispatchEvent(new Event("change"));
                }

                // Hiển thị thông báo thành công
                Swal.fire({
                    icon: "success",
                    title: "Thành công!",
                    text: data.message,
                    confirmButtonText: "Đóng",
                });

                // Đánh dấu thành phẩm đã được tạo
                if (typeof markProductAsCreated === "function") {
                    markProductAsCreated(uniqueId);
                } else {
                    // Fallback nếu hàm markProductAsCreated chưa được định nghĩa
                    const createNewBtn = document.querySelector(
                        `.create-new-product-btn[data-unique-id="${uniqueId}"]`
                    );
                    if (createNewBtn) {
                        createNewBtn.disabled = true;
                        createNewBtn.classList.add("opacity-50");
                        createNewBtn.innerHTML =
                            '<i class="fas fa-check-circle mr-1"></i> Đã tạo thành phẩm';
                    }
                }
            } else {
                throw new Error(
                    data.message || "Có lỗi xảy ra khi tạo thành phẩm mới"
                );
            }
        })
        .catch((error) => {
            console.error("Error creating new product:", error);
            Swal.fire({
                icon: "error",
                title: "Lỗi!",
                text: error.message || "Có lỗi xảy ra khi tạo thành phẩm mới",
                confirmButtonText: "Đóng",
            });
        })
        .finally(() => {
            // Đặt lại cờ khi hoàn thành (dù thành công hay thất bại)
            window.isCreatingProduct = false;
        });
}

// Cập nhật hàm showCreateNewProductModal để sử dụng hàm handleCreateNewProduct
function showCreateNewProductModal(productUniqueId) {
    const componentBlock = document.getElementById(
        "component_block_" + productUniqueId
    );
    if (!componentBlock) return;

    const createNewProductBtn = componentBlock.querySelector(
        ".create-new-product-btn"
    );
    if (createNewProductBtn) {
        window.handleCreateNewProduct(createNewProductBtn);
    }
}

// Thêm hàm khởi tạo serial cho vật tư
function initMaterialSerials() {
    // Tìm tất cả select serial
    const serialSelects = document.querySelectorAll(".material-serial-select");
    serialSelects.forEach((select) => {
        const materialId = select.dataset.materialId;
        const warehouseId = select.dataset.warehouseId;
        const currentSerial = select.dataset.currentSerial;
        const productId = select.dataset.productId;
        const productUnit = select.dataset.productUnit;

        if (!materialId || !warehouseId) return;

        // Gọi API lấy danh sách serial
        const url = "/assemblies/material-serials";
        const fetchOptions = {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                material_id: materialId,
                warehouse_id: warehouseId,
                product_unit: productUnit,
                product_id: productId,
            }),
        };

        fetch(url, fetchOptions)
            .then((response) => response.json())
            .then((data) => {
                if (data.success && data.serials) {
                    // Xóa tất cả option trừ option đã chọn
                    const selectedOption =
                        select.querySelector("option[selected]");
                    select.innerHTML = "";

                    // Thêm lại option mặc định
                    const defaultOption = document.createElement("option");
                    defaultOption.value = "";
                    defaultOption.textContent = "-- Chọn serial --";
                    select.appendChild(defaultOption);

                    // Thêm lại option đã chọn nếu có
                    if (selectedOption) {
                        select.appendChild(selectedOption);
                    }

                    // Thêm các serial mới từ API
                    data.serials.forEach((serial) => {
                        // Chỉ thêm serial chưa được chọn
                        if (serial !== currentSerial) {
                            const option = document.createElement("option");
                            option.value = serial;
                            option.textContent = serial;
                            select.appendChild(option);
                        }
                    });
                }
            })
            .catch((error) => {
                console.error("Error fetching serials:", error);
            });
    });
}
