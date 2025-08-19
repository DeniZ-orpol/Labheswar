@extends('app')
@push('styles')
    <style>
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .column {
            width: 50%;
            padding: 10px;
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

        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Stock in Hand
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
        <form action="{{ route('stock-in-hand.update', $stock->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="column">
                    <!-- Select Product -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_search" class="form-label w-full flex flex-col sm:flex-row">
                            Select Product<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <div class="product-search-container" style="position: relative;">
                            <input id="product_search" type="text" name="product_search" class="form-control field-new"
                                placeholder="Type to search products..." autocomplete="off" required value="{{ $stock->product->product_name ?? '' }}">
                            <input type="hidden" id="product" name="product" value="{{ $stock->product->id ?? '' }}" required>
                            <div id="product_dropdown" class="product-dropdown" style="display: none;">
                                <div class="dropdown-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="price" class="form-label w-full flex flex-col sm:flex-row">
                            Price
                        </label>
                        <input id="price" type="text" name="price" class="form-control field-new" maxlength="255" value="{{ $stock->price ?? '' }}">
                    </div>

                    <!-- Qty In Hand -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="qty_in_hand" class="form-label w-full flex flex-col sm:flex-row">
                            Qty In Hand
                        </label>
                        <input id="qty_in_hand" type="text" name="qty_in_hand" class="form-control field-new"
                            maxlength="255" value="{{ $stock->qty_in_hand ?? '' }}">
                    </div>

                    <!-- Qty In Sold -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="qty_in_sold" class="form-label w-full flex flex-col sm:flex-row">
                            Qty In Sold
                        </label>
                        <input id="qty_in_sold" type="text" name="qty_in_sold" class="form-control field-new"
                            maxlength="255" value="{{ $stock->qty_sold ?? '' }}">
                    </div>

                    <!-- Inventory Value -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="inventory_value" class="form-label w-full flex flex-col sm:flex-row">
                            Inventory Value
                        </label>
                        <input id="inventory_value" type="text" name="inventory_value" class="form-control field-new"
                            maxlength="255" value="{{ $stock->inventory_value ?? '' }}">
                    </div>

                    <!-- Sale Value -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="sale_value" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Value
                        </label>
                        <input id="sale_value" type="text" name="sale_value" class="form-control field-new"
                            maxlength="255" value="{{ $stock->sale_value ?? '' }}">
                    </div>

                    <!-- Available Stock -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="available_stock" class="form-label w-full flex flex-col sm:flex-row">
                            Available Stock
                        </label>
                        <input id="available_stock" type="text" name="available_stock" class="form-control field-new"
                            maxlength="255" value="{{ $stock->available_stock ?? '' }}">
                    </div>

            </div>
    </div>
    <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
    <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
    </form>
    <!-- END: Validation Form -->
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSearch = document.getElementById('product_search');
        const productId = document.getElementById('product');
        const dropdown = document.getElementById('product_dropdown');
        const dropdownContent = dropdown.querySelector('.dropdown-content');

        let currentProducts = [];
        let currentIndex = -1;
        let searchTimeout;
        let isSelecting = false;

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

            hideDropdown();

            // Focus on next input (price)
            document.getElementById('price').focus();

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
            }
        });

        function setupEnterNavigation() {
            let currentFieldIndex = 0;

            const formFields = [
                { selector: '#product', type: 'select' },
                { selector: '#price', type: 'input' },
                { selector: '#qty_in_hand', type: 'input' },
                { selector: '#qty_in_sold', type: 'input' },
                { selector: '#inventory_value', type: 'input' },
                { selector: '#sale_value', type: 'input' },
                { selector: '#available_stock', type: 'input' }
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
    });
</script>
@endpush
