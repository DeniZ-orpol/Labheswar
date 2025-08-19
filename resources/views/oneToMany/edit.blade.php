@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Conversion
        </h2>

        <form action="{{ route('one-to-many.update', $oneToMany->id ) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- Manufactured Product -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <!-- Ledger Name -->
                <div class="input-form col-span-4 mt-3">
                    <label for="ledger_name" class="form-label w-full flex flex-col sm:flex-row">
                        Ledger Name<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="search-dropdown">
                        <input id="ledger_name" type="text" name="ledger_name_display" class="form-control search-input"
                            placeholder="Search or type ledger name" autocomplete="off"
                            value="{{ $oneToMany->ledger->party_name }}" required readonly>
                        <input type="hidden" id="hidden_ledger_id" name="hidden_ledger_id" class="ledger_id"
                            value="{{ $oneToMany->ledger_id }}" required>
                        <div class="dropdown-list" id="ledgerDropdown"></div>
                    </div>
                </div>
                <!-- Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="date" class="form-label w-full flex flex-col sm:flex-row">
                        Date
                    </label>
                    <input id="date" type="date" name="date" class="form-control field-new"
                        placeholder="DD/MM or YYYY-MM-DD" value="{{ old('bill_date', $oneToMany->date) }}">
                </div>
                <!-- Entry No -->
                <div class="input-form col-span-4 mt-3">
                    <label for="entry_no" class="form-label w-full flex flex-col sm:flex-row">
                        Entry No.
                    </label>
                    <input id="entry_no" type="text" name="entry_no" class="form-control field-new"
                        placeholder="Entry NO" maxlength="255" value="{{ $oneToMany->entry_no }}" readonly>
                </div>

                <!-- Row Item Name -->
                <div class="input-form col-span-8">
                    <label for="raw_product_search" class="form-label w-full flex flex-col sm:flex-row">
                        Raw Item<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="product-search-container" style="position: relative;">
                        <input id="raw_product_search" type="text" name="raw_product_search"
                            class="form-control field-new product_search" placeholder="Type to search products..."
                            autocomplete="off" value="{{ $oneToMany->rawItem->product_name }}" required readonly>
                        <input type="hidden" id="product_id" name="raw_product_id" class="product_id"
                            value="{{ $oneToMany->raw_item }}" required>
                        <input type="hidden" name="raw_quantity" id="hidden_raw_quantity" value="{{ $oneToMany->qty }}">
                        <input type="hidden" id="raw_product_quantity" value="{{ $oneToMany->rawItem->available_qty }}">
                        <input type="hidden" id="raw_product_unit" value="{{ $oneToMany->rawItem->unit_types ?? 'PCS' }}">
                        <div id="product_dropdown" class="product-dropdown" style="display: none;">
                            <div class="dropdown-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="row float-right">
                <div class="">
                    <button type="button" id="addRowBtn" class="btn btn-primary float-right">+ Add</button>
                </div>
            </div> --}}
            <div class="block w-full">
                <table class="w-full mb-4 bg-transparent table-striped mt-4" id="billTbl">
                    <thead>
                        <tr class="border-b text-gray-700 uppercase text-sm">
                            <th scope="col" class="required">Product</th>
                            <th scope="col" class="required">Volume</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="quote-item-container product-table-body" id="product-table-body">
                        @foreach ($oneToMany->item_to_create as $newProduct)
                            <tr class="border-b text-gray-700 uppercase text-sm quote-item-row" data-index="0">
                                <th scope="col" class="required">
                                    <div class="product-search-container" style="position: relative;">
                                        <input id="add_product_search" type="text" name="add_product_search[]"
                                            class="form-control field-new product_search"
                                            placeholder="Type to search products..." autocomplete="off" value="{{ $newProduct->product_name }}" required>
                                        <input type="hidden" id="add_product_id" name="add_product_id[]"
                                            class="product_id add-product-search-input" value="{{ $newProduct->product_id }}" required>
                                        <div id="add_product_dropdown" class="product-dropdown" style="display: none;">
                                            <div class="dropdown-content"></div>
                                        </div>
                                    </div>
                                </th>
                                <th scope="col" class="required">
                                    <input type="number" name="add_quantity[]" id="add_quantity"
                                        class="form-control qty" required min="0" value="{{ $newProduct->qty }}">
                                </th>
                                <th scope="col" class="text-end">
                                    <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                                </th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="column">
                    <!-- Submit Button -->
                    <div class="">
                        <button type="button" id="submit-btn" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Raw Quantity Modal -->
    <div id="raw-qty-modal" class="modal" aria-hidden="true" style="z-index: 50">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Raw Item Quantity</h2>
                </div>
                <!-- END: Modal Header -->

                <!-- BEGIN: Modal Body -->
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label for="modal-raw-product-name" class="form-label">Product Name</label>
                        <input id="modal-raw-product-name" name="modal_raw_product_name" type="text"
                            class="form-control" readonly>
                    </div>
                    <div class="col-span-6">
                        <label for="modal-available-qty" class="form-label">Available Quantity</label>
                        <input id="modal-available-qty" type="text" class="form-control" readonly
                            style="background-color: #f8f9fa; color: #28a745; font-weight: bold;">
                    </div>
                    <div class="col-span-12">
                        <label for="modal-raw-qty" class="form-label">Quantity</label>
                        <input id="modal-raw-qty" name="raw_quantity" type="number" class="form-control"
                            placeholder="Enter Quantity of raw product" value="{{ $oneToMany->qty }}" required>
                        <small class="text-muted">Maximum available: <span id="max-qty-text">0</span></small>
                    </div>
                </div>
                <!-- END: Modal Body -->

                <!-- BEGIN: Modal Footer -->
                <div class="modal-footer">
                    <button type="button" id="cancel-modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="save-modal" class="btn btn-primary w-20">Save</button>
                </div>
                <!-- END: Modal Footer -->
            </div>
        </div>
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

        .search-dropdown {
            position: relative;
            width: 100%;
        }

        .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .dropdown-list.show {
            display: block;
        }

        .dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
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
            var rowIndex = 0;
            const today = new Date();
            const todayString = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
            document.getElementById('date').value = todayString;

            // Initialize Ledger Dropdown (Parties)
            initPartyDropdown();
            setupEnterNavigation();
            // Initialize product search for raw product search input
            const rawProductSearchInput = document.getElementById('raw_product_search');
            if (rawProductSearchInput) {
                initProductSearch(rawProductSearchInput);
            }

            // START:Raw product quantity modal
            const submitBtn = document.getElementById('submit-btn');
            const modal = document.getElementById('raw-qty-modal');
            const cancelBtn = document.getElementById('cancel-modal');
            const saveBtn = document.getElementById('save-modal');
            const mainForm = document.querySelector('form[action="{{ route('one-to-many.update', $oneToMany->id) }}"]');
            const rawProductSearch = document.getElementById('raw_product_search');
            const modalProductName = document.getElementById('modal-raw-product-name');
            const modalRawQty = document.getElementById('modal-raw-qty');
            const hiddenRawQty = document.getElementById('hidden_raw_quantity');

            // Function to show modal
            function showModal() {
                // modal.style.display = 'block';
                // modal.setAttribute('aria-hidden', 'false');

                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
                modal.style.marginTop = '50px';
                modal.style.marginLeft = '0';
                modal.classList.add('show');
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
            }

            // Function to hide modal
            function hideModal() {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                modalRawQty.value = '';
            }

            // When main submit button is clicked, show modal instead of submitting form
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (!rawProductSearch.value.trim()) {
                    alert('Please select a raw product first');
                    rawProductSearch.focus();
                    return;
                }

                const productId = document.getElementById('product_id').value;
                if (!productId) {
                    alert('Please select a valid product');
                    return;
                }

                modalProductName.value = rawProductSearch.value;

                // Get quantity from stored data instead of API call
                const availableQty = document.getElementById('raw_product_quantity').value || 0;
                const unitType = document.getElementById('raw_product_unit').value || 'units';

                document.getElementById('modal-available-qty').value = availableQty + ' ' + unitType;
                document.getElementById('max-qty-text').textContent = availableQty;
                document.getElementById('modal-raw-qty').setAttribute('max', availableQty);

                showModal();

                setTimeout(() => {
                    modalRawQty.focus();
                }, 100);
            });

            // Cancel button - hide modal
            cancelBtn.addEventListener('click', function() {
                hideModal();
            });

            // Save button in modal - validate and submit form
            saveBtn.addEventListener('click', function() {
                const quantity = parseFloat(modalRawQty.value);
                const maxQty = parseFloat(document.getElementById('modal-raw-qty').getAttribute('max')) ||
                    0;

                if (!quantity || quantity <= 0) {
                    alert('Please enter a valid quantity');
                    modalRawQty.focus();
                    return;
                }

                if (quantity > maxQty) {
                    alert(`Entered quantity (${quantity}) exceeds available quantity (${maxQty})`);
                    modalRawQty.focus();
                    return;
                }

                hiddenRawQty.value = quantity;
                hideModal();
                mainForm.submit();
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal();
                }
            });
            // END:Raw product quantity modal

            document.addEventListener('input', function(e) {
                if (e.target && e.target.classList.contains('product_search')) {
                    initProductSearch(e.target);
                }
            });
            // Initialize product search for the first row
            // initProductSearch(rowIndex);

            // Add new row on clicking add button
            // document.getElementById('addRowBtn').addEventListener('click', function() {
            //     rowIndex++;
            //     const tbody = document.querySelector('#billTbl tbody');
            //     const newRow = document.createElement('tr');
            //     newRow.classList.add('border-b', 'text-gray-700', 'uppercase', 'text-sm', 'quote-item-row');
            //     newRow.dataset.index = rowIndex;

            //     newRow.innerHTML = `
        //         <th scope="col" class="required">
        //             <div class="product-search-container" style="position: relative;">
        //                 <input id="add_product_search_${rowIndex}" type="text" name="add_product_search[]" class="form-control field-new product_search"
        //                     placeholder="Type to search products..." autocomplete="off" required>
        //                 <input type="hidden" id="add_product_id_${rowIndex}" name="add_product_id[]" class="product_id" required>
        //                 <div id="add_product_dropdown_${rowIndex}" class="product-dropdown" style="display: none;">
        //                     <div class="dropdown-content"></div>
        //                 </div>
        //             </div>
        //         </th>
        //         <th scope="col" class="required">
        //             <input type="number" name="add_quantity[]" id="add_quantity_${rowIndex}" class="form-control qty" required min="0">
        //         </th>
        //         <th scope="col" class="text-end">
        //             <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
        //         </th>
        //     `;

            //     tbody.appendChild(newRow);

            //     // Initialize product search for the new row
            //     initProductSearch(rowIndex);
            // });

            // Delete row on clicking delete button
            document.querySelector('#billTbl tbody').addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('deleteRowBtn')) {
                    const row = e.target.closest('tr');
                    if (row) {
                        row.remove();
                        updateDeleteButtons();
                    }
                }
            });

        });

        function initProductSearch(productSearchInput) {
            if (productSearchInput.dataset.initialized === 'true') return;
            productSearchInput.dataset.initialized = 'true';

            const container = productSearchInput.closest('.product-search-container');
            const dropdown = container.querySelector('.product-dropdown');
            const dropdownContent = dropdown.querySelector('.dropdown-content');
            const hiddenInput = container.querySelector('input.product_id');
            // const row = container.closest('tr');
            // const qtyInput = row.querySelector('.qty');

            let currentIndex = -1;
            let currentProducts = [];
            let isSelecting = false;

            if (!productSearchInput || !dropdown) return;

            // Debounced product search
            let searchTimeout;
            productSearchInput.addEventListener('input', function() {
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
            productSearchInput.addEventListener('keydown', function(e) {
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

            productSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && dropdown.style.display !== 'none') {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    hideDropdown();
                }
            });

            function searchProducts(searchTerm) {
                fetch(`{{ route('products.search') }}?search=${encodeURIComponent(searchTerm)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentProducts = data.products;
                            console.log(currentProducts);

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
                    item.addEventListener('click', function() {
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
                items[currentIndex].scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }

            function selectProduct(product) {
                console.log(product);

                isSelecting = true;
                productSearchInput.value = product.product_name;


                if (hiddenInput) {
                    hiddenInput.value = product.id;
                }

                // Store product quantity for modal use
                if (productSearchInput.id === 'raw_product_search') {
                    document.getElementById('raw_product_quantity').value = product.available_qty || 0;
                    document.getElementById('raw_product_unit').value = product.unit_types || 'PCS';
                }

                hideDropdown();

                // if (qtyInput) {
                //     qtyInput.focus();
                // }

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

        function updateDeleteButtons() {
            const tableBody = document.getElementById('product-table-body');
            const allRows = tableBody.querySelectorAll('tr');
            const deleteButtons = tableBody.querySelectorAll('.deleteRowBtn');

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
        // START: Set up Enter navigation
        function setupEnterNavigation() {
            let currentFieldIndex = 0;
            let currentRowIndex = 0;

            // Define field sequence
            const formFields = [{
                    selector: '#ledger_name',
                    type: 'input'
                },
                {
                    selector: '#date',
                    type: 'input'
                },
                {
                    selector: '#raw_product_search',
                    type: 'input'
                },
            ];

            const productFields = [
                'input[name="add_product_search[]"]',
                'input[name="add_quantity[]"]',
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
                        // After raw product field, move to first product field of first row
                        if (fieldIndex === 2) { // Raw product field
                            // Always move to first product row, no validation
                            currentFieldIndex = 0;
                            currentRowIndex = 0;
                            const firstRow = getCurrentProductRow();
                            focusField(productFields[0], firstRow);
                            return;
                        }
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

            // Set initial focus
            setTimeout(() => {
                focusField(formFields[0].selector);
            }, 500);

            function handleProductFieldNavigation(e, fieldIndex, row) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    // Product search field (fieldIndex 0) - always move to quantity
                    if (fieldIndex === 0) {
                        focusField(productFields[fieldIndex + 1], row);
                        return;
                    }

                    // Quantity field (fieldIndex 1) - Check if this is the last row
                    if (fieldIndex === 1) {
                        const allRows = document.querySelectorAll('#product-table-body tr');
                        const currentRowIndex = Array.from(allRows).indexOf(row);
                        const isLastRow = currentRowIndex === allRows.length - 1;

                        if (isLastRow) {
                            // This is the last row, add new row and focus directly on its product search
                            addProductRow();

                            // Directly focus on the newly added row's product search
                            setTimeout(() => {
                                const allRowsAfterAdd = document.querySelectorAll('#product-table-body tr');
                                const newLastRow = allRowsAfterAdd[allRowsAfterAdd.length - 1];
                                const newProductSearch = newLastRow.querySelector(
                                    'input[name="add_product_search[]"]');
                                if (newProductSearch) {
                                    newProductSearch.focus();
                                    console.log(
                                        `Added new row ${allRowsAfterAdd.length}. Focused on product search.`);
                                }
                            }, 100);
                        } else {
                            // Not the last row, move to next row's product search
                            const nextRow = allRows[currentRowIndex + 1];
                            if (nextRow) {
                                const nextProductSearch = nextRow.querySelector('input[name="add_product_search[]"]');
                                if (nextProductSearch) {
                                    nextProductSearch.focus();
                                    console.log(`Moved to existing row ${currentRowIndex + 2} product search.`);
                                }
                            }
                        }
                        return;
                    }
                }
                // Handle Escape key from ANY quantity field to move to submit button
                else if (e.key === 'Escape' && fieldIndex === 1) {
                    e.preventDefault();
                    const submitButton = document.getElementById('submit-btn');
                    if (submitButton) {
                        submitButton.focus();
                        console.log('Escaped from quantity field to save button');
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
                const rowCount = tableBody.children.length;

                // Clear all input values
                newRow.querySelectorAll('input').forEach(input => {
                    if (input.type !== 'hidden' || input.name.includes('product_id')) {
                        input.value = '';
                    }
                });

                // Update IDs to make them unique
                const productSearch = newRow.querySelector('input[name="add_product_search[]"]');
                const productId = newRow.querySelector('input[name="add_product_id[]"]');
                const quantityInput = newRow.querySelector('input[name="add_quantity[]"]');
                const dropdown = newRow.querySelector('.product-dropdown');

                if (productSearch) {
                    productSearch.id = `add_product_search_${rowCount}`;
                    productSearch.dataset.initialized = 'false';
                }
                if (productId) {
                    productId.id = `add_product_id_${rowCount}`;
                }
                if (quantityInput) {
                    quantityInput.id = `add_quantity_${rowCount}`;
                }
                if (dropdown) {
                    dropdown.id = `add_product_dropdown_${rowCount}`;
                }

                // Update row index
                newRow.dataset.index = rowCount;

                tableBody.appendChild(newRow);

                // Initialize product search for new row
                if (productSearch) {
                    initProductSearch(productSearch);
                }

                // Update delete button states
                updateDeleteButtons();

                console.log(`Added new row ${rowCount}. Total rows: ${tableBody.children.length}`);
            }

            // Fix delete button functionality
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('deleteRowBtn')) {
                    const row = e.target.closest('tr');
                    const tableBody = document.getElementById('product-table-body');
                    const allRows = tableBody.querySelectorAll('tr');

                    // Only allow deletion if more than one row exists
                    if (allRows.length > 1) {
                        row.remove();
                        updateDeleteButtons();
                        console.log(`Row deleted. Remaining rows: ${tableBody.querySelectorAll('tr').length}`);
                    } else {
                        console.log('Cannot delete the last row');
                    }
                }
            });

            // Handle Enter on Submit Button to open modal
            const submitButton = document.getElementById('submit-btn');
            if (submitButton) {
                submitButton.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.click(); // Trigger the modal opening
                    }
                });
            }

            // Handle modal navigation
            document.addEventListener('keydown', function(e) {
                const modal = document.getElementById('raw-qty-modal');
                if (modal && modal.classList.contains('show')) {
                    const modalRawQty = document.getElementById('modal-raw-qty');
                    const saveModalBtn = document.getElementById('save-modal');

                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (document.activeElement === modalRawQty) {
                            // From quantity field, move to save button
                            saveModalBtn.focus();
                        } else if (document.activeElement === saveModalBtn) {
                            // From save button, trigger save
                            saveModalBtn.click();
                        } else {
                            // Default: focus on save button
                            saveModalBtn.focus();
                        }
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        const cancelBtn = document.getElementById('cancel-modal');
                        if (cancelBtn) {
                            cancelBtn.click();
                        }
                    }
                }
            });
        }
        // END: Set up Enter navigation

        // Initialize Ledger Dropdown (Parties)
        function initPartyDropdown() {
            const input = document.getElementById('ledger_name');
            const dropdown = document.getElementById('ledgerDropdown');
            const searchUrl = '{{ route('purchase.party.search') }}'; // You'll need to create this route
            let timeout;
            let selectedIndex = -1;
            let currentPartyData = [];

            input.addEventListener('input', function() {
                clearTimeout(timeout);
                const value = this.value.trim();
                selectedIndex = -1;

                if (value.length < 1) {
                    dropdown.classList.remove('show');
                    currentPartyData = [];
                    return;
                }

                timeout = setTimeout(async () => {
                    try {
                        let url = `${searchUrl}?search=${value}`;

                        // Add branch_id if needed
                        const branchSelect = document.getElementById('branch');
                        if (branchSelect && branchSelect.value) {
                            url += `&branch_id=${branchSelect.value}`;
                        }

                        const response = await fetch(url, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        currentPartyData = data.parties || [];

                        let html = '';

                        // Show existing parties
                        currentPartyData.forEach((party, index) => {
                            const partyInfo = party.party_phone ?
                                ` (${party.party_phone})` : '';
                            html +=
                                `<div class="dropdown-item" data-index="${index}">${party.party_name}${partyInfo}</div>`;
                        });

                        dropdown.innerHTML = html;
                        dropdown.classList.add('show');
                        const scroll_container = document.querySelector('.custome_scroll');
                        if (scroll_container) scroll_container.style.overflowX = 'clip';
                        selectedIndex = -1;

                        // Add click listeners to dropdown items
                        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                            item.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                if (this.dataset.newValue) {
                                    // Creating new party
                                    openPartyModal(this.dataset.newValue);
                                } else if (this.dataset.index !==
                                    undefined) {
                                    // Selecting existing party
                                    const index = parseInt(this.dataset
                                        .index);
                                    selectParty(currentPartyData[index]
                                        .party_name,
                                        currentPartyData[index].id);
                                }
                            });
                        });

                    } catch (error) {
                        console.error('Party search error:', error);
                        dropdown.classList.remove('show');
                        const scroll_container = document.querySelector('.custome_scroll');
                        if (scroll_container) scroll_container.style.overflowX = 'auto';
                        currentPartyData = [];
                    }
                }, 200);
            });

            // Arrow key navigation (same as HSN dropdown)
            input.addEventListener('keydown', function(e) {
                const items = dropdown.querySelectorAll('.dropdown-item');

                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
                    updateHighlight(dropdown, items, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
                    updateHighlight(dropdown, items, selectedIndex);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && items[selectedIndex]) {
                        handlePartyDropdownItemClick(items[selectedIndex]);
                    }
                } else if (e.key === 'Escape') {
                    dropdown.classList.remove('show');
                    const scroll_container = document.querySelector('.custome_scroll');
                    if (scroll_container) scroll_container.style.overflowX = 'auto';
                    selectedIndex = -1;
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    selectedIndex = -1;
                }
            });

            // Handle dropdown item selection
            function handlePartyDropdownItemClick(item) {
                dropdown.classList.remove('show');


                if (item.dataset.newValue) {
                    openPartyModal(item.dataset.newValue);
                } else if (item.dataset.index !== undefined) {
                    const index = parseInt(item.dataset.index);
                    selectParty(currentPartyData[index].party_name, currentPartyData[index].id);
                }
            }

            // Update highlight function (reuse from your existing code)
            function updateHighlight(dropdown, items, selectedIndex) {
                items.forEach((item, index) => {
                    item.style.backgroundColor = index === selectedIndex ? '#e9ecef' : '';
                });

                if (selectedIndex >= 0 && items[selectedIndex]) {
                    const selectedItem = items[selectedIndex];
                    const dropdownScrollTop = dropdown.scrollTop;
                    const dropdownHeight = dropdown.clientHeight;
                    const itemTop = selectedItem.offsetTop;
                    const itemHeight = selectedItem.offsetHeight;

                    if (itemTop < dropdownScrollTop) {
                        dropdown.scrollTop = itemTop;
                    } else if (itemTop + itemHeight > dropdownScrollTop + dropdownHeight) {
                        dropdown.scrollTop = itemTop + itemHeight - dropdownHeight;
                    }
                }
            }
        }

        function selectParty(partyName, partyId = null) {
            const input = document.getElementById('ledger_name');
            const dropdown = document.getElementById('ledgerDropdown');

            dropdown.classList.remove('show');
            input.value = partyName;

            // Store party ID in hidden field for form submission
            let hiddenPartyIdField = document.getElementById('hidden_ledger_id');
            if (!hiddenPartyIdField) {
                hiddenPartyIdField = document.createElement('input');
                hiddenPartyIdField.type = 'hidden';
                hiddenPartyIdField.id = 'hidden_ledger_id';
                hiddenPartyIdField.name = 'ledger_name'; // This will be the actual field name for backend
                input.parentNode.appendChild(hiddenPartyIdField);

                // Change display field name to avoid conflict
                input.name = 'ledger_name_display';
            }
            hiddenPartyIdField.value = partyId || '';
        }
    </script>
@endpush
