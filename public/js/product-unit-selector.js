/**
 * Product Unit Selector JavaScript
 * Handles the functionality for selecting product units and managing their components
 */

function initializeProductUnitSelector() {
    const productUnitSelector = document.getElementById('product_unit_selector');
    const componentProductSelect = document.getElementById('component_product_id');
    const addComponentBtn = document.getElementById('add_component_btn');

    // Update product unit selector when a product is selected
    componentProductSelect.addEventListener('change', function() {
        updateProductUnitOptions();
    });

    // Update component's product unit when unit is selected
    productUnitSelector.addEventListener('change', function() {
        const selectedUnit = this.value;
        if (selectedUnit) {
            // Store the selected unit to be used when adding the component
            addComponentBtn.setAttribute('data-selected-unit', selectedUnit);
        } else {
            addComponentBtn.removeAttribute('data-selected-unit');
        }
    });

    // Function to update product unit options based on selected product
    function updateProductUnitOptions() {
        const selectedProductId = componentProductSelect.value;
        productUnitSelector.innerHTML = '<option value="">--Đơn vị--</option>';
        
        if (!selectedProductId) {
            productUnitSelector.disabled = true;
            return;
        }

        // Get the product row from the product list
        const productRow = document.querySelector(`tr[data-product-id="${selectedProductId}"]`);
        if (!productRow) {
            productUnitSelector.disabled = true;
            return;
        }

        // Get the quantity of the product
        const quantity = parseInt(productRow.getAttribute('data-quantity') || '0');
        
        // Enable the selector and add unit options (0 to quantity-1)
        productUnitSelector.disabled = false;
        for (let i = 0; i < quantity; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Đơn vị ${i + 1}`;
            productUnitSelector.appendChild(option);
        }
    }

    // Function to get the selected product unit
    function getSelectedProductUnit() {
        return productUnitSelector.value;
    }

    // Function to reset the product unit selector
    function resetProductUnitSelector() {
        productUnitSelector.innerHTML = '<option value="">--Đơn vị--</option>';
        productUnitSelector.disabled = true;
        addComponentBtn.removeAttribute('data-selected-unit');
    }

    // Export functions for external use
    window.productUnitSelector = {
        update: updateProductUnitOptions,
        getSelected: getSelectedProductUnit,
        reset: resetProductUnitSelector
    };
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeProductUnitSelector();
}); 