@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
        Add Stock Issue
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

    <form action="{{ route('stockissue.store') }}" method="POST">
        @csrf

        <!-- Manufactured Product -->
        <div class="row">
            <div class="column">
                <!--   LEDGER NAME: -->
                <div class="mb-3">
                    <label for="ledger_name" class="form-label">Ledger Name <span style="color: red;margin-left: 3px;"> *</span></label>
                    <div class="search-dropdown">
                        <input id="party_name" type="text" name="party_name_display" class="form-control search-input"
                            placeholder="Search or type party name" autocomplete="off" required>
                        <div class="dropdown-list" id="partyDropdown"></div>
                    </div>
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
                    <label for="issue_no" class="form-label">Stock Issue No<span style="color: red;margin-left: 3px;"> *</span></label>
                    <input type="text" name="issue_no" id="issue_no" class="form-control issue_no"  >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <!--   LEDGER NAME: -->
                <div class="mb-3">
                    <label for="branch" class="form-label">To Branch <span style="color: red;margin-left: 3px;"> *</span></label>
                    <select id="branch" name="to_branch" class="form-control field-new" >
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{$branch->id}}">{{$branch->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="block w-full">
            <table class="w-full mb-4 bg-transparent table-striped mt-4" id="billTbl">
                <thead>
                    <tr class="border-b text-gray-700 uppercase text-sm">
                        <th scope="col" class="required">Product</th>
                        <!-- <th scope="col" class="required">cost</th> -->
                        <th scope="col" class="required">mrp</th>
                        <th scope="col" class="required">Sale Rate</th>
                        <th scope="col" class="required">Pcs</th>
                        <th scope="col" class="required">Amount</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="quote-item-container table-body" id="product-table-body">
                    <tr class="border-b text-gray-700 uppercase text-sm quote-item-row" data-index="0">
                        <th scope="col" class="required w-1/5">
                            <div class="product-search-container" style="position: relative;">
                                <input  type="text" name="product_search[]" class="form-control product-search-input field-new product_search"
                                    placeholder="Type to search products..." autocomplete="off" required>
                                <input type="hidden"  name="product_id[]" class="product_id" required>
                                <div  class="product-dropdown" style="display: none;">
                                    <div class="dropdown-content"></div>
                                </div>  
                            </div>
                        </th>
                        <!-- <th>
                            <input type="number" name="cost[]" class="form-control field-new cost" maxlength="255" readonly>
                        </th> -->
                        <th>
                            <input type="number" name="mrp[]" class="form-control field-new mrp" maxlength="255">
                        </th>
                        <th>
                            <input type="number" name="sale_rate[]" onchange="calculateRowAmount(this)" class="form-control field-new sale_rate" maxlength="255">
                        </th>
                        <th scope="col" class="required">
                            <input type="number" name="qty[]"  class="form-control qty" onchange="calculateRowAmount(this)" required min="0">
                        </th>
                        <th>
                            <input type="number" name="amount[]" class="form-control field-new text-end" maxlength="255" readonly step="0.01">
                        </th>

                        <th scope="col" class="text-end">
                            <button type="button" class="btn btn-danger deleteRowBtn">delete</button>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>                

        <div class="grid grid-cols-1 gap-5">
            <div class="p-5 flex justify-end">
                <div class="text-left">
                    <label for="total-amount" class="form-label w-full flex flex-col sm:flex-row text-lg">
                        TOTAL INVOICE VALUE
                    </label>
                    <input id="total-amount" type="number" step="0.0001" name="total_amount"
                        class="form-control field-new text-lg" placeholder="0.00" maxlength="255">
                </div>

                <div class="input-form col-span-4 mt-3">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <!-- Submit Button -->
                <div class="">
                    <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Add Party Modal -->
    <div id="party-modal" class="modal" aria-hidden="true" style="z-index: 50">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Party</h2>
                </div>
                <!-- END: Modal Header -->

                <form action="{{ route('purchase.party.modalstore') }}" id="party-form" method="POST">
                    @csrf
                    <!-- BEGIN: Modal Body -->
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-6">
                            <label for="modal-party-name" class="form-label">Party Name<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-party-name" name="party_name" type="text" class="form-control"
                                placeholder="Enter party name" required>
                        </div>
                        <div class="col-span-6">
                            <label for="modal-party-email" class="form-label">Email</label>
                            <input id="modal-party-email" name="party_email" type="email" class="form-control"
                                placeholder="Enter Email address">
                        </div>
                        <!-- <div class="col-span-6">
                            <label for="modal-company-name" class="form-label">Company Name<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-company-name" name="company_name" type="text" class="form-control"
                                placeholder="Enter company name" required>
                        </div> -->
                        <div class="col-span-12">
                            <label for="modal-gst-number" class="form-label">Gst No.<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-gst-number" name="gst_number" type="text" class="form-control"
                                placeholder="Enter GST Number" required>
                        </div>
                        <div class="col-span-6">
                            <label for="modal-acc-no" class="form-label">Bank Account Number<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-acc-no" name="acc_no" type="text" class="form-control"
                                placeholder="Enter Bank Account Number" required>
                        </div>
                        <div class="col-span-6">
                            <label for="modal-ifsc-code" class="form-label">IFSC Code<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-ifsc-code" name="ifsc_code" type="text" class="form-control"
                                placeholder="Enter IFSC Code" required>
                        </div>
                        <!-- <div class="col-span-6">
                            <label for="modal-station" class="form-label">Station<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-station" name="station" type="text" class="form-control"
                                placeholder="Enter station" required>
                        </div> -->
                        <div class="col-span-6">
                            <label for="modal-pincode" class="form-label">Pin Code</label>
                            <input id="modal-pincode" name="pincode" type="text" class="form-control"
                                placeholder="Enter Pin code">
                        </div>
                        <div class="col-span-6">
                            <label for="modal-party-phone" class="form-label">Mobile NO.</label>
                            <input id="modal-party-phone" name="party_phone" type="text" class="form-control"
                                placeholder="Enter phone number">
                        </div>
                        <div class="col-span-12">
                            <label for="modal-party-address" class="form-label">Address</label>
                            <textarea id="modal-party-address" name="party_address" class="form-control" placeholder="Enter address"
                                rows="3"></textarea>
                        </div>
                    </div>
                    <!-- END: Modal Body -->

                    <!-- BEGIN: Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" id="cancel-party-modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" class="btn btn-primary w-20">Save</button>
                    </div>
                    <!-- END: Modal Footer -->
                </form>
            </div>
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
        .product-search-container {
            position: relative;
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
        initPartyDropdown();
        // initPartyModal();
        setupEnterNavigation();
        function initProductSearch(productSearchInput) {
            if (productSearchInput.dataset.initialized === 'true') return;
            productSearchInput.dataset.initialized = 'true';
            const container = productSearchInput.closest('.product-search-container');
            const dropdown = container.querySelector('.product-dropdown');
            const dropdownContent = dropdown.querySelector('.dropdown-content');
            const hiddenInput = container.querySelector('input.product_id');
            const row = container.closest('tr');
            const mrpInput = row.querySelector('.mrp');

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
                            selectProduct(currentProducts[currentIndex],currentProducts[currentIndex].product);
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

                const productMrpInput = container.closest('tr').querySelector('.mrp');
                const productsalerateInput = container.closest('tr').querySelector('.sale_rate');
                productMrpInput.value = product.mrp;
                productsalerateInput.value = product.sale_rate_a;
                

                hideDropdown();

                 if (productMrpInput) {
                    productMrpInput.focus();
                }


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
                const tbody = document.querySelector('#billTbl tbody');
                const rows = tbody.querySelectorAll('tr');
                if (rows.length > 1) {
                    const row = e.target.closest('tr');
                    if (row) {
                        row.remove();
                        calculateAllTotals(); // Your existing function
                    }
                } else {
                    alert('At least one row must remain.');
                }
            }
        });

        function setupEnterNavigation() {
            let currentFieldIndex = 0;
            let currentRowIndex = 0;

            // Define field sequence
            const formFields = [{
                    selector: '#party_name',
                    type: 'select'
                },
                {
                    selector: '#date',
                    type: 'input'
                },
                {
                    selector: '#issue_no',
                    type: 'input'
                },
            ];


            const productFields = [
                '.product-search-input',
                'input[name="mrp[]"]',
                'input[name="sale_rate[]"]',
                'input[name="qty[]"]',
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

                const inputs = newRow.querySelectorAll(
                    'input[name="qty[]"], input[name="sale_rate[]"]'
                );
                inputs.forEach(input => {
                    input.setAttribute('onchange', 'calculateRowAmount(this)');
                });

                // Ensure the delete button has the correct onclick handler
                const deleteButton = newRow.querySelector('button[onclick*="removeRow"]');
                if (deleteButton) {
                    deleteButton.setAttribute('onclick', 'removeRow(this)');
                }

                newRow.dataset.finalAmount = 0;
                tableBody.appendChild(newRow);

                // setupNewProductRow(newRow);

                // Update delete button states
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


        // Global function to select party and store ID
        function selectParty(partyName, partyId = null) {
            const input = document.getElementById('party_name');
            const dropdown = document.getElementById('partyDropdown');

            dropdown.classList.remove('show');
            input.value = partyName;

            // Store party ID in hidden field for form submission
            let hiddenPartyIdField = document.getElementById('hidden_party_id');
            if (!hiddenPartyIdField) {
                hiddenPartyIdField = document.createElement('input');
                hiddenPartyIdField.type = 'hidden';
                hiddenPartyIdField.id = 'hidden_party_id';
                hiddenPartyIdField.name = 'ledger'; // This will be the actual field name for backend
                input.parentNode.appendChild(hiddenPartyIdField);

                // Change display field name to avoid conflict
                input.name = 'party_name_display';
            }
            hiddenPartyIdField.value = partyId || '';

            console.log('Party selected:', {
                partyName: partyName,
                partyId: partyId,
                hiddenFieldValue: hiddenPartyIdField.value
            });
        }

        // Function to open party modal
        function openPartyModal(partyName) {
            console.log('Opening party modal with name:', partyName);
            const modal = document.getElementById('party-modal');
            const modalPartyInput = document.getElementById('modal-party-name');
            const dropdown = document.getElementById('partyDropdown');

            // Close dropdown
            dropdown.classList.remove('show');

            // Set party name in modal
            modalPartyInput.value = partyName;

            // Show modal
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.marginTop = '0';
            modal.style.marginLeft = '0';
            modal.classList.add('show');
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');

            // Focus on party name input
            setTimeout(() => {
                modalPartyInput.focus();
            }, 100);
        }

        // Function to close party modal
        function closePartyModal() {
            const modal = document.getElementById('party-modal');
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
        }

        // Initialize Party Modal
        function initPartyModal() {
            const modal = document.getElementById('party-modal');
            const cancelBtn = document.getElementById('cancel-party-modal');
            const form = modal.querySelector('form');
            const modalPartyInput = document.getElementById('modal-party-name');
            const modalPhoneInput = document.getElementById('modal-party-phone');
            const modalAddressInput = document.getElementById('modal-party-address');

            // Cancel button
            cancelBtn.addEventListener('click', closePartyModal);

            // Handle form submission with AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const partyName = modalPartyInput.value.trim();
                const partyPhone = modalPhoneInput.value.trim();
                const partyAddress = modalAddressInput.value.trim();

                if (!partyName) {
                    alert('Party name is required');
                    return;
                }

                // Create form data
                const formElement = document.getElementById("party-form");
                const formData = new FormData(formElement);
                formData.append('party_name', partyName);
                if (partyPhone) formData.append('party_phone', partyPhone);
                if (partyAddress) formData.append('party_address', partyAddress);

                // Add branch_id if needed (for multi-branch systems)
                const branchSelect = document.getElementById('branch');
                if (branchSelect?.value) {
                    formData.append('branch_id', branchSelect.value);
                }

                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
                submitBtn.disabled = true;

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Party creation response:', data);

                        if (data.success) {
                            // Close modal first
                            closePartyModal();

                            // Update party field using global function
                            selectParty(data.data.party_name, data.data.id);

                            // Clear form values
                            form.reset();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to create party'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error creating party: ' + error.message);
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePartyModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closePartyModal();
                }
            });

            // Handle Enter key in modal
            modalPartyInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }

        // Initialize Party Dropdown
        function initPartyDropdown() {
            const input = document.getElementById('party_name');
            const dropdown = document.getElementById('partyDropdown');
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

                        // Always add "Create new" option
                        // if (value) {
                        //     html +=
                        //         `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new party: "${value}"</div>`;
                        // }

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
                                } else if (this.dataset.index !== undefined) {
                                    // Selecting existing party
                                    const index = parseInt(this.dataset.index);
                                    selectParty(currentPartyData[index].ledger,
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

    });
    function calculateRowAmount(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('input[name="qty[]"]')?.value || 0);
        const saleRate = parseFloat(row.querySelector('input[name="sale_rate[]"]')?.value || 0);

        let finalAmount = 0;

        finalAmount = qty * saleRate;
        // Update amount field
        const amountInput = row.querySelector('input[name="amount[]"]');
        if (amountInput) {
            amountInput.value = finalAmount.toFixed(2);
        }

        row.dataset.finalAmount = finalAmount.toFixed(2);

        // Recalculate totals
        calculateAllTotals();
    }

    function calculateAllTotals() {
        const tableBody = document.getElementById('product-table-body');
        const rows = tableBody.querySelectorAll('tr');

        let totalFinalAmount = 0;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]')?.value || 0);
            const sale_rate = parseFloat(row.querySelector('input[name="sale_rate[]"]')?.value || 0);

            const finalAmount = parseFloat(row.dataset.finalAmount || 0);

            totalFinalAmount += finalAmount;
        });


        document.getElementById('total-amount').value = totalFinalAmount.toFixed(2);
    }
</script>
@endpush
