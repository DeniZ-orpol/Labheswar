@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
        Edit Formula
    </h2>
    @if (session('success'))
            <div id="success-alert" class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif
    
        @if (session('error'))
            <div id="error-alert" class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

    <form action="{{ route('formula.update', $formula->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Manufactured Product -->
        <div class="row">
            <div class="column">
                <div class="input-form col-span-3 ">
                    <label for="product_search" class="form-label w-full flex flex-col sm:flex-row">
                        Product<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="product-search-container" style="position: relative;">
                        <input id="product_search" type="text" name="product_search" class="form-control field-new product_search"
                            placeholder="Type to search products..." autocomplete="off" required
                            value="{{ $formula->product->product_name ?? '' }}">
                        <input type="hidden" id="product_id" name="product_id" class="product_id" required value="{{ $formula->product_id }}">
                        <div id="product_dropdown_0" class="product-dropdown" style="display: none;">
                            <div class="dropdown-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity<span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="text" name="quantity" id="quantity" class="form-control quantity" required value="{{ $formula->quantity }}">
                </div>
            </div>
            <div class="column">
                <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                    <label for="auto_production" class="form-label w-48 flex flex-col sm:flex-row">Auto Production</label>
                    <input id="auto_production" type="checkbox" name="auto_production" {{ $formula->auto_production ? 'checked' : '' }} class="form-check-input mr-0 ml-3">
                </div>
            </div>
        </div>
        <div class="block w-full">
            <table class="w-full mb-4 bg-transparent table-striped mt-4" id="billTbl">
                <thead>
                    <tr class="border-b text-gray-700 uppercase text-sm">
                        <th scope="col" class="required">Product</th>
                        <th scope="col" class="required">Volume</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="quote-item-container table-body" id="product-table-body">
                    @foreach($ingredientsWithProduct as $index => $ingredient)
                    <tr class="border-b text-gray-700 uppercase text-sm quote-item-row" data-index="{{ $index }}" >
                        <td scope="col" class="required">
                            <div class="product-search-container" style="position: relative;">
                                <input id="ingredient_product_search_{{ $index }}" type="text" name="product_search[]" class="form-control field-new product_search product-search-input"
                                    placeholder="Type to search products..." autocomplete="off" required
                                    value="{{ $ingredient['product']->product_name ?? '' }}">
                                <input type="hidden" id="ingredient_product_id_{{ $index }}" name="ingredient_product_id[]" class="product_id" required value="{{ $ingredient['product']->id ?? '' }}">
                                <div id="ingredient_product_dropdown_{{ $index }}" class="product-dropdown" style="display: none;">
                                    <div class="dropdown-content"></div>
                                </div>
                            </div>
                        </td>
                        <td scope="col" class="required">
                            <input type="text" name="ingredient_quantity[]" id="ingredient_quantity_{{ $index }}" class="form-control qty" required  value="{{ $ingredient['qty'] ?? 0 }}">
                        </td>
                        <td scope="col" class="text-end">
                            <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($ingredientsWithProduct) == 0)
                    <tr class="border-b text-gray-700 uppercase text-sm quote-item-row" data-index="0">
                        <td scope="col" class="required">
                            <div class="product-search-container" style="position: relative;">
                                <input id="ingredient_product_search_0" type="text" name="product_search[]" class="form-control field-new product_search product-search-input"
                                    placeholder="Type to search products..." autocomplete="off" required>
                                <input type="hidden" id="ingredient_product_id_0" name="ingredient_product_id[]" class="product_id" required>
                                <div id="ingredient_product_dropdown_0" class="product-dropdown" style="display: none;">
                                    <div class="dropdown-content"></div>
                                </div>
                            </div>
                        </td>
                        <td scope="col" class="required">
                            <input type="number" name="ingredient_quantity[]" id="ingredient_quantity_0" class="form-control qty" required min="0">
                        </td>
                        <td scope="col" class="text-end">
                            <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>          

        <div class="row">
            <div class="column">
                <!-- Submit Button -->
                <div class="">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .column {
            width: 50%;
            /* Adjust as needed */
            /* background-color: #f2f2f2; */
            padding: 10px;
            /* border: 1px solid #ddd; */
            box-sizing: border-box;
        }
        .product-search-container {
            position: relative;
        }

        .product-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover,
        .dropdown-item.highlighted {
            background-color: #f5f5f5;
        }

        .dropdown-item.selected {
            background-color: #e3f2fd;
        }

        .product-name {
            font-weight: 500;
        }

        .product-prices {
            font-size: 0.85em;
            color: #666;
        }

        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var rowIndex = {{ json_encode(count($ingredientsWithProduct) > 0 ? count($ingredientsWithProduct) - 1 : 0) }};
        setupEnterNavigation();
        function initProductSearch(productSearchInput) {
            if (productSearchInput.dataset.initialized === 'true') return;
                productSearchInput.dataset.initialized = 'true';
            const container = productSearchInput.closest('.product-search-container');
            const dropdown = container.querySelector('.product-dropdown');
            const dropdownContent = dropdown.querySelector('.dropdown-content');
            const hiddenInput = container.querySelector('input.product_id');

            let currentIndex = -1;
            let currentProducts = [];
            let isSelecting = false;

            if (!productSearchInput || !dropdown) return;
            // Debounced product search
            let searchTimeout;
            productSearchInput.addEventListener('input', function () {
                const searchTerm = this.value.trim();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (searchTerm.length >= 2) {
                        searchProducts(searchTerm);
                    } else {
                        hideDropdown();
                    }
                }, 300);
            });

            // Keyboard navigation
            productSearchInput.addEventListener('keydown', function (e) {
                if (dropdown.style.display === 'none') return;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        navigateDropdown(1);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        navigateDropdown(-1);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (currentIndex >= 0 && currentProducts[currentIndex]) {
                            selectProduct(currentProducts[currentIndex]);
                        }
                        break;
                    case 'Escape':
                        hideDropdown();
                        break;
                }
            });

            productSearchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter' && dropdown.style.display !== 'none') {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', function (e) {
                if (!container.contains(e.target)) {
                    hideDropdown();
                }
            });

            function searchProducts(searchTerm) {
                fetch(`{{ route('products.search') }}?search=${encodeURIComponent(searchTerm)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentProducts = data.products;
                        displayProducts(currentProducts);
                    } else {
                        showNoResults();
                    }
                })
                .catch(error => {
                    console.error('Error searching products:', error);
                    showNoResults();
                });
            }

            function displayProducts(products) {
                if (products.length === 0) {
                    showNoResults();
                    return;
                }

                dropdownContent.innerHTML = '';
                currentIndex = -1;

                products.forEach((product, index) => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item';
                    item.dataset.index = index;
                    item.innerHTML = `<div class="product-name">${product.product_name}</div>`;
                    item.addEventListener('click', function () {
                        selectProduct(product);
                    });
                    dropdownContent.appendChild(item);
                });

                showDropdown();
            }

            function showNoResults() {
                dropdownContent.innerHTML = '<div class="no-results">No products found</div>';
                currentProducts = [];
                currentIndex = -1;
                showDropdown();
            }

            function navigateDropdown(direction) {
                const items = dropdownContent.querySelectorAll('.dropdown-item');
                if (items.length === 0) return;

                if (currentIndex >= 0) {
                    items[currentIndex].classList.remove('highlighted');
                }

                currentIndex += direction;
                if (currentIndex < 0) currentIndex = items.length - 1;
                if (currentIndex >= items.length) currentIndex = 0;

                items[currentIndex].classList.add('highlighted');
                items[currentIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            function selectProduct(product) {
                isSelecting = true;
                productSearchInput.value = product.product_name;

                if (hiddenInput) {
                    hiddenInput.value = product.id;
                }

                const row = productSearchInput.closest('tr');

                if (row) {
                    const qtyInput = row.querySelector('.qty');
                    if (qtyInput) {
                        qtyInput.focus();
                    }
                }

                hideDropdown();

                setTimeout(() => {
                    isSelecting = false;
                }, 100);
            }

            function showDropdown() {
                dropdown.style.display = 'block';
            }

            function hideDropdown() {
                dropdown.style.display = 'none';
                currentIndex = -1;

                const items = dropdownContent.querySelectorAll('.dropdown-item');
                items.forEach(item => item.classList.remove('highlighted'));
            }
        }

        document.addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('product_search')) {
                initProductSearch(e.target);
            }
        });

        // Delete row on clicking delete button
        document.querySelector('#billTbl tbody').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('deleteRowBtn')) {
                const row = e.target.closest('tr');
                if (row) {
                    row.remove();
                    calculateAllTotals();
                }
            }
        });

        function setupEnterNavigation() {
            let currentFieldIndex = 0;
            let currentRowIndex = 0;

            // Define field sequence
            const formFields = [{
                    selector: '#product_search',
                    type: 'select'
                },
                {
                    selector: '#quantity',
                    type: 'input'
                }
            ];

            const productFields = [
                '.product-search-input',
                'input[name="ingredient_quantity[]"]'
            ];

            document.addEventListener('keydown', function(e) {
                const target = e.target;

                // Handle product fields
                if (target.closest('#product-table-body')) {
                    const row = target.closest('tr');
                    const rows = Array.from(document.querySelectorAll('#product-table-body tr'));
                    const rowIndex = rows.indexOf(row);

                    productFields.forEach((fieldSelector, fieldIndex) => {
                        if (target.matches(fieldSelector)) {
                            currentRowIndex = rowIndex;
                            handleProductFieldNavigation(e, fieldIndex, row);
                        }
                    });
                }

            });

            function focusField(selector, row = null) {
                let element;
                if (row) {
                    element = row.querySelector(selector);
                } else {
                    element = document.querySelector(selector);
                }

                if (element) {
                    element.focus();
                    if (element.tagName === 'SELECT') {
                        // For select elements, simulate click to open dropdown
                        setTimeout(() => {
                            if (element.size <= 1) {
                                element.click();
                            }
                        }, 100);
                    }
                }
            }

            function handleFormFieldNavigation(e, fieldIndex) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    if (fieldIndex < formFields.length - 1) {
                        // Move to next form field
                        currentFieldIndex = fieldIndex + 1;
                        focusField(formFields[currentFieldIndex].selector);
                    } else {
                        // Move to first product field of first row
                        currentFieldIndex = 0;
                        currentRowIndex = 0;
                        const firstRow = getCurrentProductRow();
                        focusField(productFields[0], firstRow);
                    }
                }
            }

            // Setup form field navigation
            formFields.forEach((field, index) => {
                const element = document.querySelector(field.selector);
                if (element) {
                    element.addEventListener('keydown', (e) => handleFormFieldNavigation(e, index));
                }
            });

            setTimeout(() => {
                focusField(formFields[0].selector);
            }, 500);

            function handleProductFieldNavigation(e, fieldIndex, row) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    // Special handling for product search field (fieldIndex 0)
                    if (fieldIndex === 0) {
                        const productInput = row.querySelector('.product-search-input');

                        // Don't navigate if barcode is being processed or product details are loading
                        if (productInput && (
                            productInput.dataset.processingBarcode === 'true' ||
                            productInput.dataset.loadingProductDetails === 'true'
                        )) {
                            console.log('Blocking navigation - barcode processing or details loading');
                            return;
                        }

                        // Check if product is selected and details are loaded
                        const hiddenSelect = row.querySelector('.hidden-product-select');
                        if (hiddenSelect && hiddenSelect.value) {
                            // Product is selected, check if product name is loaded in the display
                            const currentItemElement = document.getElementById('current-item');
                            const productNameLoaded = currentItemElement && currentItemElement.textContent && currentItemElement.textContent !== '-';

                            if (productNameLoaded) {
                                // Product details are loaded, proceed to next field
                                focusField(productFields[fieldIndex + 1], row);
                            } else {
                                // Product selected but name not displayed yet, wait
                                console.log('Product selected but name not displayed, waiting...');
                                setTimeout(() => {
                                    // Try again after a delay
                                    const nameStillLoading = currentItemElement && currentItemElement.textContent && currentItemElement.textContent !== '-';
                                    if (nameStillLoading) {
                                        focusField(productFields[fieldIndex + 1], row);
                                    } else {
                                        console.log('Product name still not loaded, staying on product field');
                                    }
                                }, 300);
                            }
                        } else {
                            // No product selected, don't move
                            console.log('No product selected, staying on product field');
                        }
                        return;
                    }
                    // Handle other fields normally
                    if (fieldIndex < productFields.length - 1) {
                        focusField(productFields[fieldIndex + 1], row);
                    } else {
                        // Last field - add new row
                        addProductRow();
                        currentRowIndex++;
                        const newRow = getCurrentProductRow();
                        setTimeout(() => {
                            focusField(productFields[0], newRow);
                        }, 100);
                    }
                } else if (e.key === 'Escape' && fieldIndex === productFields.length - 1) {
                    e.preventDefault();
                    const totalInvoiceField = document.getElementById('total-invoice-value');
                    if (totalInvoiceField) {
                        totalInvoiceField.focus();
                    }
                }
            }

            function getCurrentProductRow() {
                const rows = document.querySelectorAll('#product-table-body tr');
                return rows[currentRowIndex] || rows[rows.length - 1];
            }

            function addProductRow() {
                const tableBody = document.getElementById('product-table-body');
                const existingRow = tableBody.querySelector('tr');
                const newRow = existingRow.cloneNode(true);

                // Clear all input values
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                // Update event handlers for new row
                const productSearchInput = newRow.querySelector('.product-search-input');
                productSearchInput.dataset.initialized = 'false';


                // Ensure the delete button has the correct onclick handler
                const deleteButton = newRow.querySelector('button[onclick*="removeRow"]');
                if (deleteButton) {
                    deleteButton.setAttribute('onclick', 'removeRow(this)');
                }

                tableBody.appendChild(newRow);

                updateDeleteButtons();
            }

            function updateDeleteButtons() {
                const tableBody = document.getElementById('product-table-body');
                const allRows = tableBody.querySelectorAll('tr');
                const deleteButtons = tableBody.querySelectorAll('button[onclick*="removeRow"]');

                if (allRows.length === 1) {
                    // Disable delete button if only one row
                    deleteButtons.forEach(button => {
                        button.disabled = true;
                        button.classList.add('opacity-50', 'cursor-not-allowed');
                        button.classList.remove('hover:bg-red-100');
                    });
                } else {
                    // Enable all delete buttons if more than one row
                    deleteButtons.forEach(button => {
                        button.disabled = false;
                        button.classList.remove('opacity-50', 'cursor-not-allowed');
                        button.classList.add('hover:bg-red-100');
                    });
                }
            }
        }
    });
</script>
@endpush
