@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Open Stock
        </h2>

        <form action="{{ route('inventory.store') }}" method="POST" class="form-updated validate-form mt-5">
            @csrf

            <div class="row">
                <!-- Select Product -->
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

                <!-- Quantity -->
                <div class="input-form col-span-3 mt-3">
                    <label for="quantity" class="form-label w-full flex flex-col sm:flex-row">
                        Quantity<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <input id="quantity" type="number" name="quantity" class="form-control field-new" required>
                </div>

                <!-- Type -->
                <div class="input-form col-span-3 mt-3">
                    <label for="type" class="form-label w-full flex flex-col sm:flex-row">
                        Type<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="type" name="type" class="form-control field-new" required>
                        <option value="in">IN</option>
                        <option value="out">OUT</option>
                    </select>
                </div>

                <!-- MRP -->
                <div class="input-form col-span-3 mt-3">
                    <label for="mrp" class="form-label w-full flex flex-col sm:flex-row">
                        MRP
                    </label>
                    <input id="mrp" type="number" name="mrp" step="0.01" class="form-control field-new">
                </div>

                <!-- Sale price -->
                <div class="input-form col-span-3 mt-3">
                    <label for="sale_price" class="form-label w-full flex flex-col sm:flex-row">
                        Sale Price
                    </label>
                    <input id="sale_price" type="number" name="sale_price" step="0.01" class="form-control field-new">
                </div>

                <!-- Purchase price -->
                <div class="input-form col-span-3 mt-3">
                    <label for="purchase_price" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Price
                    </label>
                    <input id="purchase_price" type="number" name="purchase_price" step="0.01" class="form-control field-new">
                </div>

                <!-- GST -->
                <div class="input-form col-span-3 mt-3">
                    <label for="gst" class="form-label w-full flex flex-col sm:flex-row">
                        GST
                    </label>
                    <select id="gst" name="gst" class="form-control field-new">
                        <option value="on">ON</option>
                        <option value="off">OFF</option>
                    </select>
                </div>

                <!-- Reason -->
                <div class="input-form col-span-3 mt-3">
                    <label for="reason" class="form-label w-full flex flex-col sm:flex-row">
                        Reason
                    </label>
                    <input id="reason" type="text" name="reason" class="form-control field-new">
                </div>
            </div>

            <div class="text-right mt-5">
                <button type="submit" class="btn btn-primary w-32">Submit</button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
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

                const formFields = [
                    { selector: '#product_search', type: 'input' },
                    { selector: '#quantity', type: 'input' },
                    { selector: '#type', type: 'select' },
                    { selector: '#mrp', type: 'input' },
                    { selector: '#sale_price', type: 'input' },
                    { selector: '#purchase_price', type: 'input' },
                    { selector: '#gst', type: 'select' },
                    { selector: '#reason', type: 'input' }
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
