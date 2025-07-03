@extends('app')
<style>
    .table thead tr th {
        padding: 2px !important;
    }

    .table tbody tr td {
        padding: 2px !important;
    }

    .product-table [type='text'],
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

    .create-new {
        color: #007bff;
        font-style: italic;
    }

    /* Product dropdown specific styles */
    .product-dropdown {
        max-height: 300px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .product-dropdown .dropdown-item:last-child {
        border-bottom: none;
    }
</style>
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Update Purchase
        </h2>
        <form action="{{ route('purchase.update', $purchaseReceipt->id) }}" method="POST"
            class="form-updated validate-form box rounded-md mt-5 p-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <!-- Name -->
                {{-- <div class="input-form col-span-8 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Party<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="modal-form-6" name="party_name" class="form-select">
                        <option value="" disabled {{ !$purchaseReceipt->purchase_party_id ? 'selected' : '' }}>Select
                            Party...</option>
                        @foreach ($parties as $party)
                            <option value="{{ $party->id }}"
                                {{ $party->id == $purchaseReceipt->purchase_party_id ? 'selected' : '' }}>
                                {{ $party->party_name }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="input-form col-span-8 mt-3">
                    <label for="party_name" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Party<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="search-dropdown">
                        <input id="party_name" type="text" name="party_name_display" class="form-control search-input"
                            autocomplete="off" value="{{ $purchaseReceipt->purchaseParty->party_name }}" required disabled>
                        <div class="dropdown-list" id="partyDropdown"></div>
                    </div>

                    <input type="hidden" name="party_name" value="{{ $purchaseReceipt->purchase_party_id }}">
                </div>

                <!-- Bill Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="bill_date" class="form-label w-full flex flex-col sm:flex-row">
                        Bill Date
                    </label>
                    <input id="bill_date" type="date" name="bill_date" class="form-control field-new"
                        value="{{ old('bill_date', $purchaseReceipt->bill_date) }}">
                </div>

                <!-- Bill No -->
                <div class="input-form col-span-4 mt-3">
                    <label for="bill_no" class="form-label w-full flex flex-col sm:flex-row">
                        Bill No.
                    </label>
                    <input id="bill_no" type="text" name="bill_no" class="form-control field-new"
                        placeholder="Enter Purchase Bill NO" value="{{ $purchaseReceipt->bill_no }}" maxlength="255">
                </div>

                <!-- Delivery Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="delivery_date" class="form-label w-full flex flex-col sm:flex-row">
                        Delivery Date
                    </label>
                    <input id="delivery_date" type="date" name="delivery_date" class="form-control field-new"
                        value="{{ old('delivery_date', $purchaseReceipt->delivery_date) }}">
                </div>

                <!-- GST ON/OFF -->
                <div class="input-form col-span-4 mt-3">
                    <label for="gst" class="form-label w-full flex flex-col sm:flex-row">
                        GST
                    </label>
                    <select id="gst" name="gst" class="form-control field-new" onchange="calculateAllTotals()">
                        <option value="on" {{ $purchaseReceipt->gst_status == 'on' ? 'selected' : '' }}>ON</option>
                        <option value="off" {{ $purchaseReceipt->gst_status == 'off' ? 'selected' : '' }}>OFF</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 grid-updated mt-12">
                {{-- <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <button type="button" class="btn btn-primary shadow-md mr-2 btn-hover"> + Add Product</button>
                </div> --}}
                <table class="display table intro-y col-span-12 bg-transparent w-full">
                    <thead>
                        <tr class="border-b fs-7 fw-bolder text-gray-700 uppercase text-center">
                            <th scope="col" class="required">Product</th>
                            <th scope="col" class="required">mrp</th>
                            <th scope="col" class="required">box</th>
                            <th scope="col" class="required">pcs</th>
                            <th scope="col" class="required">free</th>
                            <th scope="col" class="required">p.rate</th>
                            <th scope="col" class="text-end">dis(%)</th>
                            <th scope="col" class="text-end">dis(₹)</th>
                            <th scope="col" class="text-end">amount</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        @foreach ($purchaseItems as $index => $item)
                            <tr class="text-center">
                                <!-- Product -->
                                <td class="table__item-desc w-2/5">
                                    <div class="search-dropdown">
                                        <input type="text" name="product_search[]"
                                            class="form-control search-input product-search-input"
                                            placeholder="Search product by name or barcode..."
                                            value="{{ $item->productInfo->product_name }}" autocomplete="off">

                                        <div class="dropdown-list product-dropdown"></div>
                                    </div>

                                    <!-- Hidden select to maintain existing functionality -->
                                    <select name="product[]"
                                        class="form-select text-sm w-full rounded-md hidden-product-select"
                                        style="display: none;" onchange="loadProductDetails(this)">
                                        <option value="">Please Select product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-mrp="{{ $product->mrp ?? 0 }}"
                                                data-name="{{ $product->product_name }}"
                                                data-box-pcs="{{ $product->converse_box ?? 1 }}"
                                                data-sgst="{{ $product->gst / 2 ?? 0 }}"
                                                data-cgst="{{ $product->gst / 2 ?? 0 }}"
                                                data-purchase-rate="{{ $product->purchase_rate ?? 0 }}"
                                                data-sale-rate-a="{{ $product->sale_rate_a ?? 0 }}"
                                                data-sale-rate-b="{{ $product->sale_rate_b ?? 0 }}"
                                                data-sale-rate-c="{{ $product->sale_rate_c ?? 0 }}"
                                                data-barcode="{{ $product->barcode ?? '' }}"
                                                data-category="{{ $product->category_id ?? '' }}"
                                                data-unit-type="{{ $product->unit_types ?? '' }}"
                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- MRP -->
                                <td>
                                    <input type="number" name="mrp[]" class="form-control field-new" maxlength="255"
                                        onchange="calculateRowAmount(this)" value="{{ $item->mrp ?? 0 }}">
                                </td>

                                <!-- Box -->
                                <td>
                                    <input type="number" name="box[]" class="form-control field-new" maxlength="255"
                                        onchange="calculateRowAmount(this)" value="{{ $item->box ?? 0 }}">
                                </td>

                                <!-- Pcs -->
                                <td>
                                    <input type="number" name="pcs[]" class="form-control field-new" maxlength="255"
                                        onchange="calculateRowAmount(this)" value="{{ $item->pcs ?? 0 }}">
                                </td>

                                <!-- Free -->
                                <td>
                                    <input type="number" name="free[]" class="form-control field-new" maxlength="255"
                                        value="{{ $item->free ?? 0 }}">
                                </td>

                                <!-- Purchase Rate -->
                                <td>
                                    <input type="number" name="purchase_rate[]" class="form-control field-new"
                                        maxlength="255" onchange="calculateRowAmount(this)" step="0.01"
                                        value="{{ $item->p_rate ?? 0 }}">
                                </td>

                                <!-- Discount (%) -->
                                <td>
                                    <input type="number" name="discount_percent[]"
                                        class="form-control field-new text-end" maxlength="255"
                                        onchange="calculateRowAmount(this)" step="0.01"
                                        value="{{ $item->discount ?? 0 }}">
                                </td>

                                <!-- Discount (Lumpsum) -->
                                <td>
                                    <input type="number" name="discount_lumpsum[]"
                                        class="form-control field-new text-end" maxlength="255"
                                        onchange="calculateRowAmount(this)" step="0.01"
                                        value="{{ $item->lumpsum ?? 0 }}">
                                </td>

                                <!-- Amount -->
                                <td>
                                    <input type="number" name="amount[]" class="form-control field-new text-end"
                                        maxlength="255" readonly step="0.01" value="{{ $item->amount ?? 0 }}">
                                    <!-- Hidden fields for calculated data -->
                                    <input type="hidden" name="purchase_item_ids[]" value="{{ $item->id ?? '' }}">
                                    <input type="hidden" name="total_pcs[]" class="total-pcs-hidden"
                                        value="{{ $item->total_pcs ?? ($item->box ?? 0) + ($item->pcs ?? 0) }}">
                                    <input type="hidden" name="base_amount[]" class="base-amount-hidden"
                                        value="{{ $item->base_amount ?? 0 }}">
                                    <input type="hidden" name="discount_amount[]" class="discount-amount-hidden"
                                        value="{{ $item->discount_amount ?? 0 }}">
                                    <input type="hidden" name="sgst_rate[]" class="sgst-rate-hidden"
                                        value="{{ $item->sgst_rate ?? 0 }}">
                                    <input type="hidden" name="cgst_rate[]" class="cgst-rate-hidden"
                                        value="{{ $item->cgst_rate ?? 0 }}">
                                    <input type="hidden" name="sgst_amount[]" class="sgst-amount-hidden"
                                        value="{{ $item->sgst_amount ?? 0 }}">
                                    <input type="hidden" name="cgst_amount[]" class="cgst-amount-hidden"
                                        value="{{ $item->cgst_amount ?? 0 }}">
                                    <input type="hidden" name="final_amount[]" class="final-amount-hidden"
                                        value="{{ $item->final_amount ?? $item->amount }}">
                                </td>

                                <!-- Action (Trash Icon) -->
                                <td class="text-center">
                                    <button type="button" onclick="removeRow(this)"
                                        class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-red-100">
                                        <i data-lucide="trash" class="w-5 h-5 text-red-600"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <hr>

            <!-- Item Purchase Information -->
            <div class="intro-y grid grid-cols-3 gap-5 mt-5 col-span-12">
                <!-- Item info column -->
                <div class="p-5">
                    <p><strong>Item:</strong> <span id="current-item">-</span></p>
                    {{-- <p><strong>MRP:</strong> <span id="current-mrp">0.00</span></p> --}}
                    <p><strong>SRate:</strong> <span id="current-srate">0.00</span></p>
                    <p><strong>Date:</strong> {{ $purchaseReceipt->created_at->Format('d/m/Y') }} </p>
                </div>

                <!-- MIDDLE COLUMN -->
                <div class="p-5">
                    <p><strong>MRP Value:</strong> <span id="total-mrp-value">0.00</span></p>
                    <p><strong>Amount:</strong> <span id="total-amount-value">0.00</span></p>
                    <p><strong>SGST:</strong> <span id="total-sgst">0.00</span></p>
                    <p><strong>CGST:</strong> <span id="total-cgst">0.00</span></p>
                    <p><strong>Balance:</strong> <span id="total-balance">0.00</span></p>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="p-5">
                    <p><strong>VALUE OF GOODS:</strong> <span id="value-of-goods">0.00</span></p>
                    <p><strong>DISCOUNT:</strong> <span id="total-discount">0.00</span></p>
                    <p><strong>Total GST:</strong> <span id="total-gst">0.00</span></p>
                    <p><strong>Final Amount:</strong> <span id="final-amount">0.00</span></p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-5">
                <div class="p-5 flex justify-end">
                    <div class="text-left">
                        <label for="total-invoice-value" class="form-label w-full flex flex-col sm:flex-row text-lg">
                            TOTAL INVOICE VALUE
                        </label>
                        <input id="total-invoice-value" type="number" step="0.0001" name="total_invoice_value"
                            class="form-control field-new text-lg" placeholder="0.00" maxlength="255"
                            value="{{ $purchaseReceipt->total_amount }}">
                    </div>

                    <div class="input-form col-span-4 mt-3">
                    </div>
                </div>
            </div>
            {{-- <div class="grid grid-cols-1 gap-5">
                <div class="p-5 row">
                    <div class="column font-medium text-lg">TOTAL INVOICE VALUE</div>
                    <div class="column font-medium text-lg" id="total-invoice-value">
                        {{ number_format($purchaseReceipt->total_amount, 2) }}</div>
                </div>
            </div> --}}

            <!-- Hidden fields for purchase receipt totals -->
            <input type="hidden" name="receipt_subtotal" id="receipt-subtotal-hidden"
                value="{{ $purchaseReceipt->subtotal }}">
            <input type="hidden" name="receipt_total_discount" id="receipt-total-discount-hidden"
                value="{{ $purchaseReceipt->total_discount }}">
            <input type="hidden" name="receipt_total_gst_amount" id="receipt-total-gst-amount-hidden"
                value="{{ $purchaseReceipt->total_gst_amount }}">
            <input type="hidden" name="receipt_total_amount" id="receipt-total-amount-hidden"
                value="{{ $purchaseReceipt->total_amount }}">

            <div>
                <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
            </div>
        </form>

        <!-- Purchase History Section -->
        <div id="purchase-history-section" class="box rounded-md mt-5 p-5" style="display: none;">
            <h3 class="text-lg font-medium mb-4">Recent Purchase History</h3>
            <p class="text-sm text-gray-600 mb-3">Product: <span id="history-product-name">-</span></p>

            <div class="overflow-x-auto">
                <table class="table table-bordered">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-center">Date</th>
                            <th class="text-center">Party Name</th>
                            <th class="text-center">Bill No</th>
                            <th class="text-center">BOX</th>
                            <th class="text-center">PCS</th>
                            <th class="text-center">Rate</th>
                            <th class="text-center">Discount(%)</th>
                            <th class="text-center">Discount(₹)</th>
                            <th class="text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="purchase-history-body">
                        <!-- Purchase history rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            /* Hide number input arrows/spinners */
            input[type="number"]::-webkit-outer-spin-button,
            input[type="number"]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            /* Firefox */
            input[type="number"] {
                -moz-appearance: textfield;
            }
        </style>

        <!-- END: Validation Form -->
        <!-- BEGIN: Success Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Update success!</div>
                <div class="text-slate-500 mt-1"> Purchase has been updated successfully! </div>
            </div>
        </div>
        <!-- END: Success Notification Content -->
        <!-- BEGIN: Failed Notification Content -->
        <div id="failed-notification-content" class="toastify-content hidden flex">
            <i class="text-danger" data-lucide="x-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Update failed!</div>
                <div class="text-slate-500 mt-1"> Please check the form fields. </div>
            </div>
        </div>
        <!-- END: Failed Notification Content -->
    </div>
@endsection

<script>
    // START: Set up Enter navigation
    function setupEnterNavigation() {
        let currentFieldIndex = 0;
        let currentRowIndex = 0;

        // Define field sequence
        const formFields = [{
                selector: '#party_name',
                type: 'select'
            },
            {
                selector: '#bill_date',
                type: 'input'
            },
            {
                selector: '#bill_no',
                type: 'input'
            },
            {
                selector: '#delivery_date',
                type: 'input'
            },
            {
                selector: 'select[name="gst"]',
                type: 'select'
            }
        ];

        const productFields = [
            '.product-search-input', // Changed to use search input instead of select
            'input[name="mrp[]"]',
            'input[name="box[]"]',
            'input[name="pcs[]"]',
            'input[name="free[]"]',
            'input[name="purchase_rate[]"]',
            'input[name="discount_percent[]"]',
            'input[name="discount_lumpsum[]"]'
        ];

        function getCurrentProductRow() {
            const rows = document.querySelectorAll('#product-table-body tr');
            return rows[currentRowIndex] || rows[rows.length - 1];
        }

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

        function handleProductFieldNavigation(e, fieldIndex, row) {
            if (e.key === 'Enter') {
                e.preventDefault();

                if (fieldIndex < productFields.length - 1) {
                    // Move to next field in same row
                    focusField(productFields[fieldIndex + 1], row);
                } else {
                    // Last field in row (discount_lumpsum), always add new row and move to first field
                    addProductRow();
                    currentRowIndex++;
                    const newRow = getCurrentProductRow();
                    setTimeout(() => {
                        focusField(productFields[0], newRow);
                    }, 100);
                }
            } else if (e.key === 'Escape' && fieldIndex === productFields.length - 1) {
                // ESC on last field (discount_lumpsum) moves to total invoice value
                e.preventDefault();
                const totalInvoiceField = document.getElementById('total-invoice-value');
                if (totalInvoiceField) {
                    totalInvoiceField.focus();
                }
            }
        }

        function handleSpecialNavigation(e) {
            if (e.key === 'Enter') {
                const target = e.target;

                // Check if it's a select element that needs special handling
                if (target.tagName === 'SELECT' && target.name === 'gst') {
                    // GST dropdown selected, move to product
                    e.preventDefault();
                    currentRowIndex = 0;
                    const firstRow = getCurrentProductRow();
                    setTimeout(() => {
                        focusField(productFields[0], firstRow);
                    }, 100);
                }

                // Handle total invoice value to submit button navigation
                if (target.id === 'total-invoice-value') {
                    e.preventDefault();
                    // Move focus to submit button
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.focus();
                    }
                }

                // Handle submit button enter key
                if (target.tagName === 'BUTTON' && target.type === 'submit') {
                    e.preventDefault();
                    // Submit the form
                    const form = target.closest('form');
                    if (form) {
                        form.submit();
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

        // Setup product field navigation using event delegation
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

            // Handle special navigation cases
            handleSpecialNavigation(e);
        });

        // Focus on first field when page loads
        setTimeout(() => {
            focusField(formFields[1].selector);
        }, 500);

        // Handle select dropdown closing with enter
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                // When select changes, trigger enter behavior
                const enterEvent = new KeyboardEvent('keydown', {
                    key: 'Enter',
                    code: 'Enter',
                    keyCode: 13,
                    which: 13,
                    bubbles: true
                });
                setTimeout(() => {
                    e.target.dispatchEvent(enterEvent);
                }, 100);
            }
        });
    }
    // END: Set up Enter navigation

    let productRowCounter = 0;
    let allProducts = @json($products);
    let currentProductData = [];
    let productSelectedIndex = -1;

    // Initialize Product Dropdown
    function initProductDropdown() {
        window.allProducts = allProducts;
        const productInputs = document.querySelectorAll('.product-search-input');
        productInputs.forEach(input => {
            setupProductInput(input);
        });
    }

    // Setup individual product input (like party dropdown)
    function setupProductInput(input) {
        const dropdown = input.nextElementSibling;
        const searchUrl = '{{ route('products.search') }}'; // Your search route
        let timeout;
        let selectedIndex = -1;
        let currentData = [];

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const value = this.value.trim();
            selectedIndex = -1;

            if (value.length < 1) {
                dropdown.classList.remove('show');
                currentData = [];
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    // Use the branch authentication trait for product search
                    let url = `${searchUrl}?search=${encodeURIComponent(value)}`;

                    // console.log('Fetching products from:', url);

                    const response = await fetch(url, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    // console.log('Product search response:', data);

                    currentData = data.products || [];
                    window.currentProductData = currentData;

                    // Check if it's an exact barcode match for auto-selection
                    if (data.auto_select && data.exact_match && currentData.length === 1) {
                        // Auto-select the product for barcode scan
                        const product = currentData[0];
                        selectProduct(product.product_name, product.id);
                        return; // Exit early, don't show dropdown
                    }

                    let html = '';

                    // Show existing products
                    currentData.forEach((product, index) => {
                        const productInfo = product.barcode ? ` (${product.barcode})` : '';
                        html +=
                            `<div class="dropdown-item" data-index="${index}" data-product-id="${product.id}">${product.product_name}${productInfo}</div>`;
                    });

                    // Add option to create new product
                    // if (value) {
                    //     html +=
                    //         `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new product: "${value}"</div>`;
                    // }

                    dropdown.innerHTML = html;
                    dropdown.classList.add('show');
                    selectedIndex = -1;

                    // Add click listeners to dropdown items
                    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                        item.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            // if (this.dataset.newValue) {
                            //     openProductModal(this.dataset.newValue);
                            // } else if (this.dataset.index !== undefined) {
                            const index = parseInt(this.dataset.index);
                            selectProduct(currentData[index].product_name,
                                currentData[index].id);
                            // }
                        });
                    });

                } catch (error) {
                    console.error('Product search error:', error);
                    dropdown.classList.remove('show');
                    currentData = [];
                }
            }, 200);
        });

        // Add paste event listener for barcode scanner input
        input.addEventListener('paste', function(e) {
            // Small delay to allow paste to complete
            setTimeout(() => {
                const value = this.value.trim();
                if (value && isLikelyBarcode(value)) {
                    // Trigger the search immediately for pasted barcode
                    this.dispatchEvent(new Event('input'));
                }
            }, 10);
        });

        // Arrow key navigation
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.dropdown-item');

            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
                updateProductHighlight(dropdown, items, selectedIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
                updateProductHighlight(dropdown, items, selectedIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    const index = parseInt(items[selectedIndex].dataset.index);
                    selectProduct(currentData[index].product_name, currentData[index].id);
                    // handleProductDropdownItemClick(items[selectedIndex].product_name, items[selectedIndex].id);
                }
            } else if (e.key === 'Escape') {
                dropdown.classList.remove('show');
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

        // function handleProductDropdownItemClick(item, inputElement) {
        //     dropdown.classList.remove('show');
        //     if (item.dataset.newValue) {
        //         openProductModal(item.dataset.newValue);
        //     } else if (item.dataset.index !== undefined) {
        //         const index = parseInt(item.dataset.index);
        //         selectProduct(currentData[index].product_name, currentData[index].id, inputElement);
        //     }
        // }
    }

    // Helper function to detect if input looks like a barcode
    function isLikelyBarcode(value) {
        // Remove any whitespace
        value = value.trim();

        // Check if it's a numeric string with appropriate length
        if (/^\d{8,13}$/.test(value)) {
            return true;
        }

        // Add other barcode patterns if needed
        return false;
    }

    // Update highlight function
    function updateProductHighlight(dropdown, items, selectedIndex) {
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

    // Select product function
    function selectProduct(productName, productId = null) {
        const activeInput = document.activeElement;
        let input, dropdown;

        // if (inputElement) {
        //     input = inputElement;
        //     dropdown = input.nextElementSibling;
        // } else {
        if (!activeInput || !activeInput.classList.contains('product-search-input')) {
            const visibleDropdown = document.querySelector('.product-dropdown.show');
            if (visibleDropdown) {
                input = visibleDropdown.previousElementSibling;
                dropdown = visibleDropdown;
            } else {
                console.log('Could not find active product input');
                return;
            }
        } else {
            input = activeInput;
            dropdown = input.nextElementSibling;
        }
        // }

        const hiddenSelect = input.closest('td').querySelector('.hidden-product-select');
        const row = input.closest('tr');

        dropdown.classList.remove('show');
        input.value = productName;

        // Find the product data
        let productData = null;

        if (window.currentProductData) {
            productData = window.currentProductData.find(p => p.id == productId);
        }

        if (!productData && window.allProducts) {
            productData = window.allProducts.find(p => p.id == productId);
        }

        // Update hidden select
        const existingOption = hiddenSelect.querySelector(`option[value="${productId}"]`);
        if (existingOption) {
            hiddenSelect.value = productId;

            if (productData) {
                existingOption.setAttribute('data-mrp', productData.mrp || 0);
                existingOption.setAttribute('data-name', productData.product_name);
                existingOption.setAttribute('data-sgst', productData.gst || 0);
                existingOption.setAttribute('data-cgst', productData.gst || 0);
                existingOption.setAttribute('data-purchase-rate', productData.purchase_rate || 0);
                existingOption.setAttribute('data-sale-rate-a', productData.sale_rate_a || 0);
                existingOption.setAttribute('data-sale-rate-b', productData.sale_rate_b || 0);
                existingOption.setAttribute('data-sale-rate-c', productData.sale_rate_c || 0);
                existingOption.setAttribute('data-barcode', productData.barcode || '');
                existingOption.setAttribute('data-category', productData.category_id || '');
                existingOption.setAttribute('data-unit-type', productData.unit_types || '');
                existingOption.setAttribute('data-box-pcs', productData.converse_box || 1);
            }
        } else {
            // Add new option
            const newOption = document.createElement('option');
            newOption.value = productId;
            newOption.textContent = productName;
            newOption.selected = true;

            if (productData) {
                newOption.setAttribute('data-mrp', productData.mrp || 0);
                newOption.setAttribute('data-name', productData.product_name);
                newOption.setAttribute('data-sgst', productData.gst || 0);
                newOption.setAttribute('data-cgst', productData.gst || 0);
                newOption.setAttribute('data-purchase-rate', productData.purchase_rate || 0);
                newOption.setAttribute('data-sale-rate-a', productData.sale_rate_a || 0);
                newOption.setAttribute('data-sale-rate-b', productData.sale_rate_b || 0);
                newOption.setAttribute('data-sale-rate-c', productData.sale_rate_c || 0);
                newOption.setAttribute('data-barcode', productData.barcode || '');
                newOption.setAttribute('data-category', productData.category_id || '');
                newOption.setAttribute('data-unit-type', productData.unit_types || '');
                newOption.setAttribute('data-box-pcs', productData.converse_box || 1);
            } else {
                newOption.setAttribute('data-mrp', 0);
                newOption.setAttribute('data-name', productName);
                newOption.setAttribute('data-sgst', 0);
                newOption.setAttribute('data-cgst', 0);
                newOption.setAttribute('data-purchase-rate', 0);
                newOption.setAttribute('data-sale-rate-a', 0);
                newOption.setAttribute('data-sale-rate-b', 0);
                newOption.setAttribute('data-sale-rate-c', 0);
                newOption.setAttribute('data-barcode', '');
                newOption.setAttribute('data-category', '');
                newOption.setAttribute('data-unit-type', '');
                newOption.setAttribute('data-box-pcs', 1);
            }

            hiddenSelect.appendChild(newOption);
            hiddenSelect.value = productId;
        }

        console.log('Product selected:', {
            productName: productName,
            productId: productId,
            hiddenFieldValue: hiddenSelect.value,
            isBarcodeScan: productData && productData.barcode && input.value.trim() === productData.barcode
        });

        // Trigger existing functionality
        loadProductDetails(hiddenSelect);

        // For barcode scans, auto-focus quantity field and add visual feedback
        const isBarcodeScan = productData && productData.barcode && input.value.trim() === productData.barcode;

        if (isBarcodeScan) {
            // Add visual feedback for successful barcode scan
            input.style.backgroundColor = '#d4edda'; // Light green background
            input.style.borderColor = '#28a745'; // Green border

            // Reset visual feedback after 2 seconds
            setTimeout(() => {
                input.style.backgroundColor = '';
                input.style.borderColor = '';
            }, 2000);

            // Auto-focus MRP field for quick entry
            const mrpField = row.querySelector('input[name="mrp[]"]');
            if (mrpField) {
                setTimeout(() => {
                    mrpField.focus();
                    mrpField.select(); // Select the value for quick editing
                }, 100);
            }
        } else {
            // Move to next field
            const nextField = row.querySelector('input[name="mrp[]"]');
            if (nextField) {
                setTimeout(() => {
                    nextField.focus();
                }, 100);
            }
        }
    }

    // Setup new product rows
    function setupNewProductRow(row) {
        const productInput = row.querySelector('.product-search-input');
        if (productInput) {
            setupProductInput(productInput);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addProductBtn = document.querySelector('.btn.btn-primary.shadow-md.mr-2.btn-hover');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addProductRow();
            });
        }

        // Set current date for display
        const today = new Date();
        const todayDisplay = today.toLocaleDateString();
        // document.getElementById('current-date').textContent = todayDisplay;

        // Initialize calculations with existing data
        initializeExistingData();
        calculateAllTotals();

        // Disable delete button if only one row exists initially
        updateDeleteButtonStates();
        // Enter navigation setup call
        setupEnterNavigation();

        // Initialize dropdowns - ADD THESE LINES
        initProductDropdown();
        // initPartyDropdown();
        // initPartyModal();
    });

    function initializeExistingData() {
        const tableBody = document.getElementById('product-table-body');
        const rows = tableBody.querySelectorAll('tr');

        rows.forEach(row => {
            // Setup product input for existing rows
            const productInput = row.querySelector('.product-search-input');
            if (productInput) {
                setupProductInput(productInput);
            }

            // Trigger calculation for each existing row
            const firstInput = row.querySelector('input[name="purchase_rate[]"]');
            if (firstInput) {
                calculateRowAmount(firstInput);
            }

            // Load product details for selected product
            const productSelect = row.querySelector('select[name="product[]"]');
            if (productSelect && productSelect.value) {
                loadProductDetails(productSelect);
            }
        });
    }

    function addProductRow() {
        const tableBody = document.getElementById('product-table-body');
        const existingRow = tableBody.querySelector('tr');
        const newRow = existingRow.cloneNode(true);

        // Clear all input values
        newRow.querySelectorAll('input').forEach(input => {
            if (input.name === 'purchase_item_ids[]') {
                input.value = ''; // Clear item ID for new row
            } else {
                input.value = '';
            }
        });
        newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        // Clear product search input
        const productSearchInput = newRow.querySelector('.product-search-input');
        if (productSearchInput) {
            productSearchInput.value = '';
        }

        // Update event handlers for new row
        const productSelect = newRow.querySelector('select[name="product[]"]');
        productSelect.setAttribute('onchange', 'loadProductDetails(this)');

        const inputs = newRow.querySelectorAll(
            'input[name="mrp[]"], input[name="box[]"], input[name="pcs[]"], input[name="purchase_rate[]"], input[name="discount_percent[]"], input[name="discount_lumpsum[]"]'
        );
        inputs.forEach(input => {
            input.setAttribute('onchange', 'calculateRowAmount(this)');
        });

        tableBody.appendChild(newRow);

        // Setup product input for the new row
        setupNewProductRow(newRow);

        updateDeleteButtonStates();

        // Focus on the new product input
        setTimeout(() => {
            const newProductInput = newRow.querySelector('.product-search-input');
            if (newProductInput) {
                newProductInput.focus();
            }
        }, 100);
    }

    function removeRow(button) {
        const row = button.closest('tr');
        const tableBody = row.closest('tbody');

        if (tableBody.children.length > 1) {
            row.remove();
            calculateAllTotals();
            updateDeleteButtonStates();
        }
    }

    function updateDeleteButtonStates() {
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

    function loadProductDetails(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const productName = selectedOption.getAttribute('data-name') || '-';
        const productMrp = selectedOption.getAttribute('data-mrp') || '0.00';
        const productPurchaseRate = selectedOption.getAttribute('data-purchase-rate') || '0.00';
        const productSaleRateA = selectedOption.getAttribute('data-sale-rate-a') || '0.00';
        const productSaleRateB = selectedOption.getAttribute('data-sale-rate-b') || '0.00';
        const productSaleRateC = selectedOption.getAttribute('data-sale-rate-c') || '0.00';
        const sgstRate = selectedOption.getAttribute('data-sgst') || '0';
        const cgstRate = selectedOption.getAttribute('data-cgst') || '0';
        const boxToPcs = selectedOption.getAttribute('data-box-pcs') || '1';
        const barcode = selectedOption.getAttribute('data-barcode') || '';
        const unitType = selectedOption.getAttribute('data-unit-type') || '';

        // Update current item details
        document.getElementById('current-item').textContent = productName;
        document.getElementById('current-srate').textContent = productSaleRateA;
        // document.getElementById('current-srate').textContent = " Rate1 : " + productSaleRateA + " Rate2 : " + productSaleRateB + " Rate3 : " + productSaleRateC;
        // document.getElementById('current-mrp').textContent = parseFloat(productMrp).toFixed(2);

        // Auto-fill purchase rate and MRP if available and field is empty
        const row = selectElement.closest('tr');
        const purchaseRateInput = row.querySelector('input[name="purchase_rate[]"]');
        const mrpInput = row.querySelector('input[name="mrp[]"]');

        if (purchaseRateInput && productPurchaseRate > 0 && !purchaseRateInput.value) {
            purchaseRateInput.value = parseFloat(productPurchaseRate).toFixed(2);
            calculateRowAmount(purchaseRateInput);
        }
        if (mrpInput && productMrp > 0 && !mrpInput.value) {
            mrpInput.value = parseFloat(productMrp).toFixed(2);
            calculateRowAmount(mrpInput);
        }

        // Log product details for debugging
        console.log('Selected Product Details:', {
            name: productName,
            mrp: productMrp,
            purchaseRate: productPurchaseRate,
            sgstRate: sgstRate,
            cgstRate: cgstRate,
            boxToPcs: boxToPcs,
            barcode: barcode,
            unitType: unitType
        });

        calculateAllTotals();
    }

    function calculateRowAmount(input) {
        const row = input.closest('tr');
        const box = parseFloat(row.querySelector('input[name="box[]"]')?.value || 0);
        const pcs = parseFloat(row.querySelector('input[name="pcs[]"]')?.value || 0);
        const purchaseRate = parseFloat(row.querySelector('input[name="purchase_rate[]"]')?.value || 0);
        const discountPercent = parseFloat(row.querySelector('input[name="discount_percent[]"]')?.value || 0);
        const discountLumpsum = parseFloat(row.querySelector('input[name="discount_lumpsum[]"]')?.value || 0);

        // Get product details including SGST and CGST rates
        const hiddenSelect = row.querySelector('.hidden-product-select');
        const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
        const boxToPcs = parseFloat(selectedOption.getAttribute('data-box-pcs') || 1);
        const sgstRate = parseFloat(selectedOption.getAttribute('data-sgst') || 0);
        const cgstRate = parseFloat(selectedOption.getAttribute('data-cgst') || 0);

        // Calculate total pieces: (box * conversion ratio) + individual pcs
        // box: total no of box to purchase
        // boxToPcs: conversion ratio of box
        // pcs: individual pieces to purchase
        const totalPcs = (box * boxToPcs) + pcs;

        // Calculate base amount: total pieces * purchase rate
        let baseAmount = totalPcs * purchaseRate;

        // Apply percentage discount
        let percentDiscountAmount = 0;
        if (discountPercent > 0) {
            percentDiscountAmount = baseAmount * (discountPercent / 100);
        }

        // Apply lumpsum discount
        let totalDiscountAmount = percentDiscountAmount + discountLumpsum;
        let amountAfterDiscount = baseAmount - totalDiscountAmount;

        // Calculate SGST and CGST amounts (use separate rates from product)
        const gstSelect = document.querySelector('select[name="gst"]');
        let sgstAmount = 0;
        let cgstAmount = 0;
        let totalGstAmount = 0;
        let finalAmount = amountAfterDiscount;

        if (gstSelect && gstSelect.value === 'on') {
            if (sgstRate > 0) {
                sgstAmount = amountAfterDiscount * (sgstRate / 100);
            }
            if (cgstRate > 0) {
                cgstAmount = amountAfterDiscount * (cgstRate / 100);
            }
            totalGstAmount = sgstAmount + cgstAmount;
            finalAmount = amountAfterDiscount + totalGstAmount;
        }

        // Update amount field
        const amountInput = row.querySelector('input[name="amount[]"]');
        if (amountInput) {
            amountInput.value = finalAmount.toFixed(2);
        }

        // Update hidden fields for backend submission
        row.querySelector('.total-pcs-hidden').value = totalPcs;
        row.querySelector('.base-amount-hidden').value = baseAmount.toFixed(2);
        row.querySelector('.discount-amount-hidden').value = totalDiscountAmount.toFixed(2);
        row.querySelector('.sgst-rate-hidden').value = sgstRate.toFixed(2);
        row.querySelector('.cgst-rate-hidden').value = cgstRate.toFixed(2);
        row.querySelector('.sgst-amount-hidden').value = sgstAmount.toFixed(2);
        row.querySelector('.cgst-amount-hidden').value = cgstAmount.toFixed(2);
        row.querySelector('.final-amount-hidden').value = finalAmount.toFixed(2);

        // Store calculation data in row for summary
        row.dataset.totalPcs = totalPcs.toFixed(0);
        row.dataset.baseAmount = baseAmount.toFixed(2);
        row.dataset.discountAmount = totalDiscountAmount.toFixed(2);
        row.dataset.sgstAmount = sgstAmount.toFixed(2);
        row.dataset.cgstAmount = cgstAmount.toFixed(2);
        row.dataset.totalGstAmount = totalGstAmount.toFixed(2);
        row.dataset.finalAmount = finalAmount.toFixed(2);

        // Recalculate totals
        calculateAllTotals();
    }

    function calculateAllTotals() {
        const tableBody = document.getElementById('product-table-body');
        const rows = tableBody.querySelectorAll('tr');

        let totalMrpValue = 0;
        let totalBaseAmount = 0;
        let totalDiscountAmount = 0;
        let totalSgstAmount = 0;
        let totalCgstAmount = 0;
        let totalGstAmount = 0;
        let totalFinalAmount = 0;
        let totalQuantity = 0;

        rows.forEach(row => {
            // Get product MRP and box conversion
            const productSelect = row.querySelector('select[name="product[]"]');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const boxToPcs = parseFloat(selectedOption.getAttribute('data-box-pcs') || 1);

            // Get quantities
            const mrp = parseFloat(row.querySelector('input[name="mrp[]"]')?.value || 0);
            const box = parseFloat(row.querySelector('input[name="box[]"]')?.value || 0);
            const pcs = parseFloat(row.querySelector('input[name="pcs[]"]')?.value || 0);
            const free = parseFloat(row.querySelector('input[name="free[]"]')?.value || 0);

            // Calculate total pieces including box conversion
            const totalPcs = (box * boxToPcs) + pcs;
            const totalWithFree = totalPcs + free;

            // Calculate MRP value (for total pieces including free)
            totalMrpValue += mrp * totalWithFree;
            totalQuantity += totalPcs; // Don't include free in paid quantity

            // Get calculated amounts from row data
            const baseAmount = parseFloat(row.dataset.baseAmount || 0);
            const discountAmount = parseFloat(row.dataset.discountAmount || 0);
            const sgstAmount = parseFloat(row.dataset.sgstAmount || 0);
            const cgstAmount = parseFloat(row.dataset.cgstAmount || 0);
            const gstAmount = parseFloat(row.dataset.totalGstAmount || 0);
            const finalAmount = parseFloat(row.dataset.finalAmount || 0);

            totalBaseAmount += baseAmount;
            totalDiscountAmount += discountAmount;
            totalSgstAmount += sgstAmount;
            totalCgstAmount += cgstAmount;
            totalGstAmount += gstAmount;
            totalFinalAmount += finalAmount;
        });

        // Update summary displays
        document.getElementById('total-mrp-value').textContent = totalMrpValue.toFixed(2);
        document.getElementById('total-amount-value').textContent = totalBaseAmount.toFixed(2);

        // Show SGST/CGST amounts
        document.getElementById('total-sgst').textContent = totalSgstAmount.toFixed(2);
        document.getElementById('total-cgst').textContent = totalCgstAmount.toFixed(2);

        document.getElementById('total-balance').textContent = Math.round(totalFinalAmount - totalBaseAmount).toFixed(
            0);

        document.getElementById('value-of-goods').textContent = totalBaseAmount.toFixed(2);
        document.getElementById('total-discount').textContent = totalDiscountAmount.toFixed(2);
        document.getElementById('total-gst').textContent = totalGstAmount.toFixed(2);
        document.getElementById('final-amount').textContent = totalFinalAmount.toFixed(2);

        document.getElementById('total-invoice-value').value = totalFinalAmount.toFixed(2);

        // Update S.Rate (average rate per piece)
        const averageRate = totalQuantity > 0 ? (totalBaseAmount / totalQuantity) : 0;
        // document.getElementById('current-srate').textContent = averageRate.toFixed(2);

        // Update hidden fields for purchase_receipt table
        document.getElementById('receipt-subtotal-hidden').value = totalBaseAmount.toFixed(2);
        document.getElementById('receipt-total-discount-hidden').value = totalDiscountAmount.toFixed(2);
        document.getElementById('receipt-total-gst-amount-hidden').value = totalGstAmount.toFixed(2);
        document.getElementById('receipt-total-amount-hidden').value = totalFinalAmount.toFixed(2);
    }
</script>
