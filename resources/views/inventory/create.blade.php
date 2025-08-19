@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Open Stock
        </h2>

        @if (session('success'))
            <div id="success-alert" class="alert alert-success"
                style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="alert alert-danger"
                style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('inventory.store') }}" method="POST" class="form-updated validate-form mt-5">
            @csrf

            <table class="table table-bordered" id="productTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="25%">Product Name</th>
                        <th>Barcode</th>
                        <th width="7%">Qty</th>
                        <th width="10%">MRP</th>
                        <th width="10%">Sale Rate</th>
                        <th width="10%">Purchase Price</th>
                        <!-- <th width="7%">GST %</th> -->
                        <th>GST (On/Off)</th>
                        <!-- <th>More</th> -->
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="product-row already-avalible" data-product-id="{{ $product->id }}">
                            <td class="serial text-center">{{ $loop->index + 1 }}
                                <input type="hidden" name="products[{{ $product->id }}][product_id]"
                                    value="{{ $product->id }}">
                                <input type="hidden" name="products[{{ $product->id }}][gst_p]"
                                    value="{{ $product->hsnCode->gst ?? 0 }}">
                            </td>
                            <td>
                                <input type="text" name="products[{{ $product->id }}][name]"
                                    value="{{ $product->product_name }}" class="form-control"
                                    data-id="{{ $product->id }}" data-column_name="product_name">
                            </td>
                            <td>
                                <input type="text" name="products[{{ $product->id }}][barcode]"
                                    value="{{ $product->barcode }}" class="form-control" data-id="{{ $product->id }}"
                                    data-column_name="barcode">
                            </td>
                            <td>
                                <input type="text" name="products[{{ $product->id }}][qty]"
                                    value="{{ $product->inventories->first()->total_qty ?? 0 }}" class="form-control"
                                    data-id="{{ $product->id }}" data-column_name="total_qty">
                            </td>
                            <td>
                                <input type="number" name="products[{{ $product->id }}][mrp]"
                                    value="{{ $product->mrp }}" class="form-control" data-id="{{ $product->id }}"
                                    data-column_name="mrp">
                            </td>
                            <td>
                                <input type="number" name="products[{{ $product->id }}][sale_rate]"
                                    value="{{ $product->sale_rate_a }}" class="form-control" data-id="{{ $product->id }}"
                                    data-column_name="sale_rate">
                            </td>
                            <td>
                                <input type="number" name="products[{{ $product->id }}][purchase_price]"
                                    value="{{ $product->purchase_rate }}" class="form-control"
                                    data-id="{{ $product->id }}" data-column_name="purchase_price">
                            </td>
                            <td>
                                <select name="products[{{ $product->id }}][gst]" class="form-control"
                                    data-id="{{ $product->id }}" data-column_name="gst">
                                    <option value="on">ON</option>
                                    <option value="off">OFF</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm add-row">[+]</button>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <!-- <div class="row">
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="product_search" class="form-label w-full flex flex-col sm:flex-row">
                                                                Product<span style="color: red;margin-left: 3px;"> *</span>
                                                            </label>
                                                            <div class="product-search-container" style="position: relative;">
                                                                <input id="product_search" type="text" name="product_search" class="form-control field-new"
                                                                    placeholder="Type to search products..." autocomplete="off" required>
                                                                <input type="hidden" id="product_id" name="product_id" required>
                                                                <div id="product_dropdown" class="product-dropdown" style="display: none;">
                                                                    <div class="dropdown-content"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="quantity" class="form-label w-full flex flex-col sm:flex-row">
                                                                Quantity<span style="color: red;margin-left: 3px;"> *</span>
                                                            </label>
                                                            <input id="quantity" type="number" name="quantity" class="form-control field-new" required>
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="type" class="form-label w-full flex flex-col sm:flex-row">
                                                                Type<span style="color: red;margin-left: 3px;"> *</span>
                                                            </label>
                                                            <select id="type" name="type" class="form-control field-new" required>
                                                                <option value="in">IN</option>
                                                                <option value="out">OUT</option>
                                                            </select>
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="mrp" class="form-label w-full flex flex-col sm:flex-row">
                                                                MRP
                                                            </label>
                                                            <input id="mrp" type="number" name="mrp" step="0.01" class="form-control field-new">
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="sale_price" class="form-label w-full flex flex-col sm:flex-row">
                                                                Sale Price
                                                            </label>
                                                            <input id="sale_price" type="number" name="sale_price" step="0.01" class="form-control field-new">
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="purchase_price" class="form-label w-full flex flex-col sm:flex-row">
                                                                Purchase Price
                                                            </label>
                                                            <input id="purchase_price" type="number" name="purchase_price" step="0.01" class="form-control field-new">
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="gst" class="form-label w-full flex flex-col sm:flex-row">
                                                                GST
                                                            </label>
                                                            <select id="gst" name="gst" class="form-control field-new">
                                                                <option value="on">ON</option>
                                                                <option value="off">OFF</option>
                                                            </select>
                                                        </div>
                                                        <div class="input-form col-span-3 mt-3">
                                                            <label for="reason" class="form-label w-full flex flex-col sm:flex-row">
                                                                Reason
                                                            </label>
                                                            <input id="reason" type="text" name="reason" class="form-control field-new">
                                                        </div>
                                                    </div> -->

            <div class="text-right mt-5">
                <button type="submit" class="btn btn-primary w-32">Submit</button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        #productTable td {
            padding: 2px !important;
        }

        #productTable [type='text'],
        [type='email'],
        [type='url'],
        [type='password'],
        [type='number'],
        [type='date'],
        [type='datetime-local'],
        [type='month'],
        [type='search'],
        [type='tel'],
        [type='time'],
        [type='week'],
        [multiple],
        textarea,
        select {
            padding-right: 5px !important;
            padding-left: 5px !important;
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
        document.addEventListener('click', function(e) {
            setTimeout(() => {
                if (window._productTableBlurListenerAttached) return; // prevent duplicate listener
                window._productTableBlurListenerAttached = true;

                const table = document.getElementById('productTable');
                table.addEventListener('blur', function(event) {
                    const input = event.target;
                    const tr = input.closest('tr');
                    if (!tr || !tr.classList.contains('already-avalible')) return;
                    if (event.target.tagName === 'INPUT' || event.target.tagName === 'SELECT') {
                        // console.log(`Field blurred. Value: ${event.target.value}`);
                        const input = event.target;
                        const value = input.value;
                        const productId = input.dataset.id;
                        const column = input.dataset.column_name;

                        const gstInput = tr.querySelector('[data-column_name="gst"]');
                        const gstValue = gstInput ? gstInput.value : "off";

                        const gstPInput = tr.querySelector('input[name="products[' + productId + '][gst_p]"]');
                        const gstPValue = gstPInput ? gstPInput.value : 0;

                        if (!productId || !column) return;
                        fetch("{{ route('inventory.quickUpdate') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                                },
                                credentials: "same-origin",
                                body: JSON.stringify({
                                    product_id: productId,
                                    column: column,
                                    value: value,
                                    gst: gstValue,
                                    gst_p: gstPValue
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log("Update success:", data);
                            })
                            .catch(error => {
                                console.error("Update failed:", error);
                            });

                    }
                }, true);
            }, 500);
            if (e.target.classList.contains('add-row')) {
                const currentRow = e.target.closest('tr');
                const productId = currentRow.getAttribute('data-product-id');
                const productNameInput = currentRow.querySelector('input[name$="[name]"]');
                const productName = productNameInput ? productNameInput.value : '';

                const newRow = currentRow.cloneNode(true);

                // Generate a unique identifier for the new row
                const newRowId = 'new_' + productId + '_' + Date.now();

                newRow.querySelectorAll('input, select').forEach(input => {
                    let name = input.getAttribute('name');
                    if (!name) return;

                    const fieldMatch = name.match(/\[(\w+)\]$/);
                    const field = fieldMatch ? fieldMatch[1] : '';

                    // Create new name with unique identifier
                    const newName = `products[${newRowId}][${field}]`;
                    input.setAttribute('name', newName);

                    // For new products, clear product_id, gst_p and qty fields
                    if (field === 'product_id' || field === 'qty') {
                        input.value = '';
                    }
                    // Keep other fields autofilled from the original row
                });

                // Add reference_id field to track the original product
                const referenceIdInput = document.createElement('input');
                referenceIdInput.type = 'hidden';
                referenceIdInput.name = `products[${newRowId}][reference_id]`;
                referenceIdInput.value = productId;

                // Add the reference_id input to the first cell
                const firstCell = newRow.querySelector('.serial');
                firstCell.appendChild(referenceIdInput);

                // Clear serial number and update button
                firstCell.innerHTML = firstCell.innerHTML.replace(/\d+/, ''); // Remove existing serial number
                newRow.querySelector('td:last-child').innerHTML =
                    `<button type="button" class="btn btn-danger btn-sm delete-row">🗑️</button> <button type="button" class="btn btn-warning btn-sm NewitemInsert">✔️</button>`;

                // Insert the new row after current row
                currentRow.parentNode.insertBefore(newRow, currentRow.nextSibling);
                newRow.classList.remove('already-avalible');

            }

            if (e.target.classList.contains('NewitemInsert')) {
                const row = e.target.closest('tr');
                const inputs = row.querySelectorAll('input, select');
                const productData = {};

                inputs.forEach(input => {
                    const name = input.name;
                    const value = input.value;

                    // Extract field name from: products[new_1_123456789][field]
                    const match = name.match(/\[[^\]]+\]\[([^\]]+)\]/);
                    if (match) {
                        const field = match[1];
                        productData[field] = value;
                    }
                });
                console.log(productData);

                fetch("{{ route('inventory.newstore') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(productData)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status == 'success') {
                            alert(result.message || 'Item stored successfully!');
                            location.reload(); 
                            
                        } else {
                            alert(result.message || 'Failed to store item.');
                        }
                    })
                    .catch(error => {
                        alert('Error storing item.');
                        console.error(error);
                    });
            }
            if (e.target.classList.contains('delete-row')) {
                const row = e.target.closest('tr');
                row.parentNode.removeChild(row);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const productSearch = document.getElementById('product_search');
            const productId = document.getElementById('product_id');
            const dropdown = document.getElementById('product_dropdown');
            const dropdownContent = dropdown.querySelector('.dropdown-content');
            const mrpInput = document.getElementById('mrp');
            const salePriceInput = document.getElementById('sale_price');
            const purchasePriceInput = document.getElementById('purchase_price');

            let currentProducts = [];
            let currentIndex = -1;
            let searchTimeout;
            let isSelecting = false;

            function setupEnterNavigation() {
                let currentFieldIndex = 0;

                const formFields = [{
                        selector: '#product_search',
                        type: 'input'
                    },
                    {
                        selector: '#quantity',
                        type: 'input'
                    },
                    {
                        selector: '#type',
                        type: 'select'
                    },
                    {
                        selector: '#mrp',
                        type: 'input'
                    },
                    {
                        selector: '#sale_price',
                        type: 'input'
                    },
                    {
                        selector: '#purchase_price',
                        type: 'input'
                    },
                    {
                        selector: '#gst',
                        type: 'select'
                    },
                    {
                        selector: '#reason',
                        type: 'input'
                    }
                ];

                function focusField(selector) {
                    const element = document.querySelector(selector);
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
                            currentFieldIndex = fieldIndex + 1;
                            focusField(formFields[currentFieldIndex].selector);
                        } else {
                            const submitButton = document.querySelector('button[type="submit"]');
                            if (submitButton) {
                                submitButton.focus();
                            }
                        }
                    }
                }

                formFields.forEach((field, index) => {
                    const element = document.querySelector(field.selector);
                    if (element) {
                        element.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter') {
                                handleFormFieldNavigation(e, index);
                            }
                        });
                    }
                });

                setTimeout(() => {
                    focusField(formFields[0].selector);
                }, 500);
            }

            setupEnterNavigation();

            // Search products with debounce
            productSearch.addEventListener('input', function() {
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

            // Handle keyboard navigation
            productSearch.addEventListener('keydown', function(e) {
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

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!productSearch.contains(e.target) && !dropdown.contains(e.target)) {
                    hideDropdown();
                }
            });

            // Prevent form submission on Enter if dropdown is open
            productSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && dropdown.style.display !== 'none') {
                    e.preventDefault();
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

                    item.innerHTML = `
                        <div class="product-name">${product.product_name}</div>
                        `;
                    // <div class="product-prices">
                    //     MRP: ₹${product.mrp || 0} | 
                    //     Sale: ₹${product.sale_rate_a || 0} | 
                    //     Purchase: ₹${product.purchase_rate || 0}
                    // </div>

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

                // Remove current highlight
                if (currentIndex >= 0) {
                    items[currentIndex].classList.remove('highlighted');
                }

                // Calculate new index
                currentIndex += direction;
                if (currentIndex < 0) currentIndex = items.length - 1;
                if (currentIndex >= items.length) currentIndex = 0;

                // Add highlight to new item
                items[currentIndex].classList.add('highlighted');

                // Scroll into view
                items[currentIndex].scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }

            function selectProduct(product) {
                isSelecting = true;

                productSearch.value = product.product_name;
                productId.value = product.id;

                // Auto-fill prices
                if (product.mrp) mrpInput.value = product.mrp;
                if (product.sale_rate_a) salePriceInput.value = product.sale_rate_a;
                if (product.purchase_rate) purchasePriceInput.value = product.purchase_rate;

                hideDropdown();

                // Focus on next input
                document.getElementById('quantity').focus();

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

                // Clear highlights
                const items = dropdownContent.querySelectorAll('.dropdown-item');
                items.forEach(item => item.classList.remove('highlighted'));
            }

            // Clear product selection if search input is manually cleared
            productSearch.addEventListener('blur', function() {
                if (!isSelecting && this.value.trim() === '') {
                    productId.value = '';
                    mrpInput.value = '';
                    salePriceInput.value = '';
                    purchasePriceInput.value = '';
                }
            });
        });
    </script>
@endpush
