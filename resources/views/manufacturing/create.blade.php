@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
            Add Many To One
        </h2>

    <form action="{{ route('manufacturing.store') }}" method="POST">
        @csrf

        <!-- Manufactured Product -->
        <div class="row">
            <div class="column">
                <!--   LEDGER NAME: -->
                <div class="mb-3">
                    <label for="ledger_name" class="form-label">Ledger Name <span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="text" name="ledger_name" id="ledger_name" class="form-control ledger_name" required>
                </div>
            </div>
            <div class="column">
                <!--  date -->
                <div class="mb-3">
                    <label for="date" class="form-label">Date<span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="date" name="date" id="date" class="form-control date" required >
                </div>
            </div>
            <div class="column">
                <!--  date -->
                <div class="mb-3">
                    <label for="entry_no" class="form-label">Entry No<span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="text" name="entry_no" id="entry_no" class="form-control entry_no" required >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <div class="input-form col-span-3 ">
                    <label for="product_search" class="form-label w-full flex flex-col sm:flex-row">
                        Product<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="product-search-container" style="position: relative;">
                        <input id="product_search" type="text" name="product_search" class="form-control field-new product_search"
                            placeholder="Type to search products..." autocomplete="off" required>
                        <input type="hidden" id="product_id" name="product_id" class="product_id" required>
                        <div id="product_dropdown_0" class="product-dropdown" style="display: none;">
                            <div class="dropdown-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column">
                <!--  Quantity -->
                <div class="mb-3">
                    <label for="qty" class="form-label">Quantity<span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="number" name="qty" id="qty_0" class="form-control qty" required min="0">
                </div>
            </div>
        </div>
        <div class="row float-right">
            <div class="">
                <button type="button" id="addRowBtn" class="btn btn-primary float-right">+ Add</button>
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
                <tbody class="quote-item-container table-body">
                    <tr class="border-b text-gray-700 uppercase text-sm quote-item-row" data-index="0">
                        <th scope="col" class="required">
                            <div class="product-search-container" style="position: relative;">
                                <input id="ingredient_product_search_0" type="text" name="product_search[]" class="form-control field-new product_search"
                                    placeholder="Type to search products..." autocomplete="off" required>
                                <input type="hidden" id="ingredient_product_id_0" name="ingredient_product_id[]" class="product_id" required>
                                <div id="ingredient_product_dropdown_0" class="product-dropdown" style="display: none;">
                                    <div class="dropdown-content"></div>
                                </div>
                            </div>
                        </th>
                        <th scope="col" class="required">
                            <input type="number" name="ingredient_qty[]" id="ingredient_qty_0" class="form-control qty" required min="0">
                        </th>
                        <th scope="col" class="text-end">
                            <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>                

        <div class="row">
            <div class="column">
                <!-- Submit Button -->
                <div class="">
                    <button type="submit" class="btn btn-primary">Save</button>
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
        var rowIndex = 0;
        function initProductSearch(productSearchInput) {
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


        document.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('product_search')) {
            initProductSearch(e.target);
        }
    });
        // Initialize product search for the first row
        // initProductSearch(rowIndex);

        // Add new row on clicking add button
        document.getElementById('addRowBtn').addEventListener('click', function() {
            rowIndex++;
            const tbody = document.querySelector('#billTbl tbody');
            const newRow = document.createElement('tr');
            newRow.classList.add('border-b', 'text-gray-700', 'uppercase', 'text-sm', 'quote-item-row');
            newRow.dataset.index = rowIndex;

            newRow.innerHTML = `
                <th scope="col" class="required">
                    <div class="product-search-container" style="position: relative;">
                        <input id="ingredient_product_search_${rowIndex}" type="text" name="product_search[]" class="form-control field-new product_search"
                            placeholder="Type to search products..." autocomplete="off" required>
                        <input type="hidden" id="ingredient_product_id_${rowIndex}" name="ingredient_product_id[]" class="product_id" required>
                        <div id="ingredient_product_dropdown_${rowIndex}" class="product-dropdown" style="display: none;">
                            <div class="dropdown-content"></div>
                        </div>
                    </div>
                </th>
                <th scope="col" class="required">
                    <input type="number" name="ingredient_qty[]" id="ingredient_qty_${rowIndex}" class="form-control qty" required min="0">
                </th>
                <th scope="col" class="text-end">
                    <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                </th>
            `;

            tbody.appendChild(newRow);

            // Initialize product search for the new row
            initProductSearch(rowIndex);
        });

        // Delete row on clicking delete button
        document.querySelector('#billTbl tbody').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('deleteRowBtn')) {
                const row = e.target.closest('tr');
                if (row) {
                    row.remove();
                }
            }
        });
    });
</script>
@endpush
