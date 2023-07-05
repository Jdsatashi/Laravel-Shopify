<script>
    const selectAllCheckbox = document.getElementById('selectAll');
    const productCheckboxes = Array.from(document.getElementsByClassName('product-checkbox'));
    const variantCheckboxes = Array.from(document.getElementsByClassName('variant-checkbox'));
    const discountButton = document.getElementById('discountButton');
    const revertButton = document.getElementById('revertButton');

    // Xử lý sự kiện khi checkbox all thay đổi
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = selectAllCheckbox.checked;
        productCheckboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
        variantCheckboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
        updateButtonStatus();
    });

    // Xử lý sự kiện khi checkbox product thay đổi
    productCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const isChecked = checkbox.checked;
            updateVariantCheckboxes(checkbox, isChecked);
            updateButtonStatus();
            updateSelectAllCheckbox();
        });
    });

    // Xử lý sự kiện khi checkbox variant thay đổi
    variantCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const productId = checkbox.dataset.productId;
            const productCheckbox = document.getElementById(`productCheckbox_${productId}`);
            updateProductCheckbox(productCheckbox);
            updateButtonStatus();
            updateSelectAllCheckbox();
        });
    });

    // Cập nhật trạng thái của các checkbox variant dựa trên checkbox product tương ứng
    function updateVariantCheckboxes(productCheckbox, isChecked) {
        const productId = productCheckbox.dataset.productId;
        const variantCheckboxes = Array.from(document.querySelectorAll(`.variant-checkbox[data-product-id="${productId}"]`));
        variantCheckboxes.forEach(function(variantCheckbox) {
            variantCheckbox.checked = isChecked;
        });
    }

    // Cập nhật trạng thái của checkbox product dựa trên trạng thái của các checkbox variant
    function updateProductCheckbox(productCheckbox) {
        const productId = productCheckbox.dataset.productId;
        const variantCheckboxes = Array.from(document.querySelectorAll(`.variant-checkbox[data-product-id="${productId}"]`));
        const isProductChecked = variantCheckboxes.every(function(checkbox) {
            return checkbox.checked;
        });
        productCheckbox.checked = isProductChecked;
    }

    // Cập nhật trạng thái của checkbox all trên th dựa trên trạng thái của các checkbox product và variant
    function updateSelectAllCheckbox() {
        const isAllChecked = productCheckboxes.every(function(checkbox) {
            return checkbox.checked;
        });
        const isAnyVariantUnchecked = variantCheckboxes.some(function(checkbox) {
            return !checkbox.checked;
        });
        selectAllCheckbox.checked = isAllChecked && !isAnyVariantUnchecked;
    }

    // Cập nhật trạng thái của nút
    function updateButtonStatus() {
        const isAnyCheckboxChecked = productCheckboxes.some(function(checkbox) {
            return checkbox.checked;
        });

        const isAnyVariantChecked = variantCheckboxes.some(function(checkbox) {
            return checkbox.checked;
        });

        if (isAnyCheckboxChecked || isAnyVariantChecked) {
            discountButton.disabled = false;
            revertButton.disabled = false;
        } else {
            discountButton.disabled = true;
            revertButton.disabled = true;
        }
    }
</script>