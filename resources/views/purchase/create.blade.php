@extends('app')
<style>
    .table thead tr th {
        padding: 2px !important;
    }

    .table tbody tr td {
        padding: 2px !important;
    }

    .side-nav {
        width: 105px !important;
    }

    .side-nav .side-menu .side-menu__title .side-menu__sub-icon {
        display: none !important;
    }

    .side-nav .side-menu .side-menu__title {
        display: none !important;
    }

    [type='text'],
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

    .no-results {
        padding: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }
</style>
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            New Purchase
        </h2>
        <form action="{{ route('purchase.store') }}" method="POST"
            class="form-updated validate-form box rounded-md mt-5 p-5" id="purchase-form">
            @csrf <!-- CSRF token for security -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <!-- Name -->
                {{-- <div class="input-form col-span-8 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Party<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="modal-form-6" name="party_name" class="form-select">
                        <option value="">Select Party...</option>
                        @foreach ($parties as $party)
                            <option value="{{ $party->id }} "> {{ $party->party_name }} </option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="input-form col-span-8 mt-3">
                    <label for="party_name" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Party<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <div class="search-dropdown">
                        <input id="party_name" type="text" name="party_name_display" class="form-control search-input"
                            placeholder="Search or type party name" autocomplete="off">
                        <div class="dropdown-list" id="partyDropdown"></div>
                    </div>
                </div>

                <!-- Bill Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="bill_date" class="form-label w-full flex flex-col sm:flex-row">
                        Bill Date
                    </label>
                    <input id="bill_date" type="date" name="bill_date" class="form-control field-new"
                        placeholder="DD/MM or YYYY-MM-DD">
                </div>

                <!-- Bill No -->
                <div class="input-form col-span-4 mt-3">
                    <label for="bill_no" class="form-label w-full flex flex-col sm:flex-row">
                        Bill No.
                    </label>
                    <input id="bill_no" type="text" name="bill_no" class="form-control field-new"
                        placeholder="Enter Purchase Bill NO" maxlength="255">
                </div>

                <!-- Delivery Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="delivery_date" class="form-label w-full flex flex-col sm:flex-row">
                        Delivery Date
                    </label>
                    <input id="delivery_date" type="date" name="delivery_date" class="form-control field-new">
                </div>

                <!-- GST ON/OFF -->
                <div class="input-form col-span-4 mt-3">
                    <label for="gst" class="form-label w-full flex flex-col sm:flex-row">
                        GST
                    </label>
                    <select id="gst" name="gst" class="form-control field-new" onchange="calculateAllTotals()">
                        <option value="on" selected>ON</option>
                        <option value="off">OFF</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 grid-updated mt-12 custome_scroll" style="overflow-x:auto;">
                {{-- <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <button type="button" class="btn btn-primary shadow-md mr-2 btn-hover"> + Add Product</button>
                </div> --}}
                <table class="display table intro-y col-span-12 bg-transparent product-table" style="min-width:1400px;">
                    <thead>
                        <tr class="border-b fs-7 fw-bolder text-gray-700 uppercase text-center">
                            <th scope="col" class="required">Product</th>
                            <th scope="col" class="required">Expiry</th>
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
                        <tr class="text-center">
                            <!-- Product -->
                            <td class="table__item-desc w-1/5">
                                <div class="search-dropdown">
                                    <input type="text" name="product_search[]"
                                        class="form-control search-input product-search-input"
                                        placeholder="Search product by name or barcode..." autocomplete="off">
                                    {{-- onkeyup="searchProducts(this)" onfocus="showProductDropdown(this)"> --}}
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
                                            data-unit-type="{{ $product->unit_types ?? '' }}">
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Expiry Date -->
                            <td>
                                <input type="date" name="expiry_date[]" placeholder="DD-MM-YYYY"
                                    class="form-control field-new" maxlength="255">
                            </td>

                            <!-- Box -->
                            <td>
                                <input type="number" name="mrp[]" class="form-control field-new" maxlength="255"
                                    onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Box -->
                            <td>
                                <input type="number" name="box[]" class="form-control field-new" maxlength="255"
                                    onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Pcs -->
                            <td>
                                <input type="number" name="pcs[]" class="form-control field-new" maxlength="255"
                                    onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Free -->
                            <td>
                                <input type="number" name="free[]" class="form-control field-new" maxlength="255">
                            </td>

                            <!-- Purchase Rate -->
                            <td>
                                <input type="number" name="purchase_rate[]" class="form-control field-new"
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.001">
                            </td>

                            <!-- Discount (%) -->
                            <td>
                                <input type="number" name="discount_percent[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.001">
                            </td>

                            <!-- Discount (Lumpsum) -->
                            <td>
                                <input type="number" name="discount_lumpsum[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.001">
                            </td>

                            <!-- Amount -->
                            <td>
                                <input type="number" name="amount[]" class="form-control field-new text-end"
                                    maxlength="255" readonly step="0.001">
                                <!-- Hidden fields for calculated data -->
                                <input type="hidden" name="total_pcs[]" class="total-pcs-hidden">
                                <input type="hidden" name="base_amount[]" class="base-amount-hidden">
                                <input type="hidden" name="discount_amount[]" class="discount-amount-hidden">
                                <input type="hidden" name="sgst_rate[]" class="sgst-rate-hidden">
                                <input type="hidden" name="cgst_rate[]" class="cgst-rate-hidden">
                                <input type="hidden" name="sgst_amount[]" class="sgst-amount-hidden">
                                <input type="hidden" name="cgst_amount[]" class="cgst-amount-hidden">
                                <input type="hidden" name="final_amount[]" class="final-amount-hidden">
                            </td>

                            <!-- Action (Trash Icon) -->
                            <td class="text-center">
                                <button type="button" onclick="removeRow(this)"
                                    class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-red-100">
                                    <i data-lucide="trash" class="w-5 h-5 text-red-600"></i>
                                </button>
                            </td>
                        </tr>
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
                    <p><strong>Date:</strong> <span id="current-date">-</span></p>
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
                        {{-- <div class="column font-medium text-lg">TOTAL INVOICE VALUE</div>
                        <div class="column font-medium text-lg" id="total-invoice-value">0.00</div> --}}
                        <label for="total-invoice-value" class="form-label w-full flex flex-col sm:flex-row text-lg">
                            TOTAL INVOICE VALUE
                        </label>
                        <input id="total-invoice-value" type="number" step="0.0001" name="total_invoice_value"
                            class="form-control field-new text-lg" placeholder="0.00" maxlength="255">
                    </div>

                    <div class="input-form col-span-4 mt-3">
                    </div>
                </div>
            </div>

            <!-- Hidden fields for purchase receipt totals -->
            <input type="hidden" name="receipt_subtotal" id="receipt-subtotal-hidden">
            <input type="hidden" name="receipt_total_discount" id="receipt-total-discount-hidden">
            <input type="hidden" name="receipt_total_gst_amount" id="receipt-total-gst-amount-hidden">
            <input type="hidden" name="receipt_total_amount" id="receipt-total-amount-hidden">

            <div>
                <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
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
        {{-- <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration success!</div>
                <div class="text-slate-500 mt-1"> Please check your e-mail for further info! </div>
            </div>
        </div> --}}
        <!-- END: Success Notification Content -->
        <!-- BEGIN: Failed Notification Content -->
        {{-- <div id="failed-notification-content" class="toastify-content hidden flex">
            <i class="text-danger" data-lucide="x-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration failed!</div>
                <div class="text-slate-500 mt-1"> Please check the fileld form. </div>
            </div>
        </div> --}}
        <!-- END: Failed Notification Content -->
    </div>

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
                            <label for="modal-gst-number" class="form-label">Gst No.</label>
                            <input id="modal-gst-number" name="gst_number" type="text" class="form-control"
                                placeholder="Enter GST Number">
                        </div>
                        <div class="col-span-6">
                            <label for="modal-acc-no" class="form-label">Bank Account Number</label>
                            <input id="modal-acc-no" name="acc_no" type="text" class="form-control"
                                placeholder="Enter Bank Account Number">
                        </div>
                        <div class="col-span-6">
                            <label for="modal-ifsc-code" class="form-label">IFSC Code</label>
                            <input id="modal-ifsc-code" name="ifsc_code" type="text" class="form-control"
                                placeholder="Enter IFSC Code">
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

    <!-- Add Product Modal -->
    <div id="product-modal" class="modal" aria-hidden="true" style="z-index: 50">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Product</h2>
                </div>
                <!-- END: Modal Header -->

                <form action="{{ route('products.modalstore') }}" id="product-form" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <!-- BEGIN: Modal Body -->
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-4">
                            <label for="modal-product-name" class="form-label">Product Name<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-product-name" name="product_name" type="text" class="form-control"
                                placeholder="Enter product name" required>
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-barcode" class="form-label">Barcode</label>
                            <input id="modal-product-barcode" name="product_barcode" type="text" class="form-control"
                                placeholder="Enter barcode">
                        </div>
                        <div class="col-span-4">
                            <label for="unit_type" class="form-label w-full flex flex-col sm:flex-row">
                                Unit Type<p style="color: red;margin-left: 3px;"> *</p>
                            </label>
                            <select id="unit_type" name="unit_type" class="form-control field-new" required>
                                <option value="" selected>Choose...</option>
                                <option value="PCS">PCS</option>
                                <option value="KG">KG</option>
                                <option value="LITER">LITER</option>
                                <option value="BOX">BOX</option>
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-hsn" class="form-label">HSN Code</label>
                            <div class="search-dropdown">
                                <input id="modal-product-hsn" type="text" name="hsn"
                                    class="form-control field-new search-input" placeholder="Search or type HSN code"
                                    autocomplete="off">
                                <div class="dropdown-list" id="hsnDropdown"></div>
                            </div>
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-mrp" class="form-label">MRP</label>
                            <input id="modal-product-mrp" name="mrp" type="number" step="0.01"
                                class="form-control" placeholder="Enter MRP">
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-purchase-rate" class="form-label">Purchase Rate</label>
                            <input id="modal-product-purchase-rate" name="purchase_rate" type="number" step="0.01"
                                class="form-control" placeholder="Enter purchase rate">
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-sale-rate-a" class="form-label">Sale Rate A</label>
                            <input id="modal-product-sale-rate-a" name="sale_rate_a" type="number" step="0.01"
                                class="form-control" placeholder="Enter sale rate A">
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-sale-rate-b" class="form-label">Sale Rate B</label>
                            <input id="modal-product-sale-rate-b" name="sale_rate_b" type="number" step="0.01"
                                class="form-control" placeholder="Enter sale rate B">
                        </div>
                        <div class="col-span-4">
                            <label for="modal-product-sale-rate-c" class="form-label">Sale Rate C</label>
                            <input id="modal-product-sale-rate-c" name="sale_rate_c" type="number" step="0.01"
                                class="form-control" placeholder="Enter sale rate C">
                        </div>
                        <div class="col-span-3">
                            <label for="modal-product-converse-carton" class="form-label">Converse Carton</label>
                            <input id="modal-product-converse-carton" name="converse_carton" type="number"
                                class="form-control">
                        </div>
                        <div class="col-span-3">
                            <label for="modal-product-carton-barcode" class="form-label">Carton Barcode</label>
                            <input id="modal-product-carton-barcode" name="carton_barcode" type="number"
                                class="form-control">
                        </div>
                        <div class="col-span-3">
                            <label for="modal-product-converse-box" class="form-label">Converse Box</label>
                            <input id="modal-product-converse-box" name="converse_box" type="number"
                                class="form-control">
                        </div>
                        <div class="col-span-3">
                            <label for="modal-product-box-barcode" class="form-label">Box Barcode</label>
                            <input id="modal-product-box-barcode" name="box_barcode" type="number"
                                class="form-control">
                        </div>
                        {{-- <div class="col-span-6">
                            <label for="modal-product-unit-type" class="form-label">Unit Type</label>
                            <input id="modal-product-unit-type" name="unit_type" type="text" class="form-control"
                                placeholder="Enter unit type">
                        </div> --}}
                        <div class="col-span-4">
                            <label for="modal-product-negative-billing" class="form-label">Negative Billing</label>
                            <select id="modal-product-negative-billing" name="negative_billing" class="form-select">
                                <option value="NO" selected>No</option>
                                <option value="YES">Yes</option>
                            </select>
                        </div>
                    </div>
                    <!-- END: Modal Body -->

                    <!-- BEGIN: Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" id="cancel-product-modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="button" id="save-product-btn" class="btn btn-primary w-20">Save</button>
                    </div>
                    <!-- END: Modal Footer -->
                </form>
            </div>
        </div>
    </div>

    <script>
        // Product Modal JS
        function openProductModal(productName) {
            const modal = document.getElementById('product-modal');
            const modalProductInput = document.getElementById('modal-product-name');
            const dropdown = document.querySelector('.product-dropdown');

            dropdown.classList.remove('show');

            // Mark the active input as creating a product
            const activeInput = document.activeElement;
            if (activeInput && activeInput.classList.contains('product-search-input')) {
                activeInput.dataset.creatingProduct = 'true';
            }

            modalProductInput.value = productName;

            // Show modal
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.marginTop = '40';
            modal.style.marginLeft = '0';
            modal.classList.add('show');
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');

            // Focus on party name input
            setTimeout(() => {
                modalProductInput.focus();
            }, 100);
        }

        function closeProductModal() {
            const modal = document.getElementById('product-modal');
            modal.classList.remove('show');
            modal.classList.add('hide');
            // modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
        }

        document.getElementById('cancel-product-modal').addEventListener('click', closeProductModal);

        document.getElementById('save-product-btn').addEventListener('click', function(e) {
            e.preventDefault();

            // Trigger the form submission event
            const form = document.getElementById('product-form');
            const submitEvent = new Event('submit');
            form.dispatchEvent(submitEvent);
        });

        // Optional: AJAX form submission for product modal
        document.getElementById('product-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert('Product created successfully!');
                        closeProductModal();
                        console.log(data.data);

                        // Get the newly created product data
                        const newProduct = data.data;

                        // Find the active product input (the one that triggered the modal)
                        const activeInput = document.querySelector('.product-search-input:focus') ||
                            document.querySelector('.product-search-input[data-creating-product="true"]');

                        if (activeInput) {
                            const row = activeInput.closest('tr');
                            const hiddenSelect = row.querySelector('.hidden-product-select');

                            // Add the new product to the hidden select with all required data attributes
                            const newOption = document.createElement('option');
                            newOption.value = newProduct.id;
                            newOption.textContent = newProduct.product_name;
                            newOption.selected = true;

                            // Set all data attributes from the new product
                            newOption.setAttribute('data-mrp', newProduct.mrp || 0);
                            newOption.setAttribute('data-name', newProduct.product_name);
                            newOption.setAttribute('data-box-pcs', newProduct.converse_box || 1);
                            newOption.setAttribute('data-purchase-rate', newProduct.purchase_rate || 0);
                            newOption.setAttribute('data-sale-rate-a', newProduct.sale_rate_a || 0);
                            newOption.setAttribute('data-sale-rate-b', newProduct.sale_rate_b || 0);
                            newOption.setAttribute('data-sale-rate-c', newProduct.sale_rate_c || 0);
                            newOption.setAttribute('data-barcode', newProduct.barcode || '');
                            newOption.setAttribute('data-category', newProduct.category_id || '');
                            newOption.setAttribute('data-unit-type', newProduct.unit_types || '');

                            // Set GST rates if HSN code data is available
                            if (newProduct.hsn_code && newProduct.hsn_code.gst) {
                                const gstRate = newProduct.hsn_code.gst;
                                newOption.setAttribute('data-sgst', gstRate / 2 || 0);
                                newOption.setAttribute('data-cgst', gstRate / 2 || 0);
                            } else {
                                newOption.setAttribute('data-sgst', 0);
                                newOption.setAttribute('data-cgst', 0);
                            }

                            // Clear existing options and add the new one
                            hiddenSelect.innerHTML = '';
                            hiddenSelect.appendChild(newOption);

                            // Update the search input with product name
                            activeInput.value = newProduct.product_name;

                            // Remove the creating product flag
                            delete activeInput.dataset.creatingProduct;

                            // Trigger loadProductDetails to populate the row
                            loadProductDetails(hiddenSelect);

                            // Move focus to the next field (expiry date)
                            setTimeout(() => {
                                const expiryField = row.querySelector('input[name="expiry_date[]"]');
                                if (expiryField) {
                                    expiryField.focus();
                                }
                            }, 100);

                            console.log('New product created and selected:', newProduct);
                        }

                        // Clear form values
                        form.reset();

                        // Optionally, update product dropdown or reload page
                        // location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to create product'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating product: ' + error.message);
                });
        });

        // Close modal on clicking outside
        document.getElementById('product-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('product-modal');
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeProductModal();
            }
        });
    </script>
@endsection

<script>
    // Validation Form JS
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('purchase-form');

        form.addEventListener('submit', e => {
            e.preventDefault();
            let isValid = true;
            const partyName = document.getElementById('party_name');
            const billNo = document.getElementById('bill_no');
            const existingError = partyName.nextElementSibling;

            // Remove previous error
            partyName.classList.remove('border-red-500', 'border-green-500');
            if (existingError && existingError.classList.contains('purchase-error')) {
                existingError.remove();
            }
            if (!partyName.value.trim()) {
                partyName.classList.add('border-red-500');
                partyName.insertAdjacentHTML('afterend',
                    `<span class="text-red-500 text-sm purchase-error">Party name is required.</span>`
                );
                isValid = false;
            }

            // Remove previous error
            billNo.classList.remove('border-red-500', 'border-green-500');
            if (existingError && existingError.classList.contains('purchase-error')) {
                existingError.remove();
            }
            if (!billNo.value.trim()) {
                billNo.classList.add('border-red-500');
                billNo.insertAdjacentHTML('afterend',
                    `<span class="text-red-500 text-sm purchase-error">Bill No is required.</span>`
                );
                isValid = false;
            }

            const productInputs = document.querySelectorAll('input[name="product_search[]"]');
            const rateInputs = document.querySelectorAll('input[name="purchase_rate[]"]');

            // Only clear and validate existing visible inputs
            [...productInputs].forEach(input => {
                const errorEl = input.nextElementSibling;
                input.classList.remove('border-red-500', 'border-green-500');
                if (errorEl && errorEl.classList.contains('purchase-error')) errorEl.remove();

                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    input.insertAdjacentHTML('afterend',
                        `<span class="text-red-500 text-sm purchase-error">Product is required.</span>`
                    );
                    isValid = false;
                } else {
                    input.classList.add('border-green-500');
                }
            });

            [...rateInputs].forEach(input => {
                const errorEl = input.nextElementSibling;
                input.classList.remove('border-red-500', 'border-green-500');
                if (errorEl && errorEl.classList.contains('purchase-error')) errorEl.remove();

                const val = parseFloat(input.value);
                if (isNaN(val) || val <= 0) {
                    input.classList.add('border-red-500');
                    input.insertAdjacentHTML('afterend',
                        `<span class="text-red-500 text-sm purchase-error">Purchase rate is required.</span>`
                    );
                    isValid = false;
                } else {
                    input.classList.add('border-green-500');
                }
            });


            if (isValid) form.submit();
        });
    });


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
            'input[name="expiry_date[]"]',
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
                        const productNameLoaded = currentItemElement && currentItemElement.textContent &&
                            currentItemElement.textContent !== '-';

                        if (productNameLoaded) {
                            // Product details are loaded, proceed to next field
                            focusField(productFields[fieldIndex + 1], row);
                        } else {
                            // Product selected but name not displayed yet, wait
                            console.log('Product selected but name not displayed, waiting...');
                            setTimeout(() => {
                                // Try again after a delay
                                const nameStillLoading = currentItemElement && currentItemElement.textContent &&
                                    currentItemElement.textContent !== '-';
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
            focusField(formFields[0].selector);
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

    // ==================================

    // Initialize Product Dropdown (similar to initPartyDropdown)
    function initProductDropdown() {
        // Store allProducts globally for the selectProduct function
        window.allProducts = allProducts;

        // Get all product search inputs (for multiple rows)
        const productInputs = document.querySelectorAll('.product-search-input');

        productInputs.forEach(input => {
            setupProductInput(input);
        });
    }

    // HSN code dropdown
    function initHsnDropdown() {
        const input = document.getElementById('modal-product-hsn');
        const dropdown = document.getElementById('hsnDropdown');
        const searchUrl = '{{ route('hsn.search') }}';
        let timeout;
        let selectedIndex = -1;
        let currentHsnData = []; // Store current search results

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const value = this.value.trim();
            selectedIndex = -1;

            if (value.length < 1) {
                dropdown.classList.remove('show');
                currentHsnData = [];
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    let url = `${searchUrl}?search=${value}`;

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
                    currentHsnData = data.hsn_codes || [];

                    let html = '';

                    // Show existing HSN codes first
                    currentHsnData.forEach((item, index) => {
                        // Show HSN code with GST info for better identification
                        const gstInfo = item.gst ?
                            // ` (GST: ${typeof item.gst === 'string' ? JSON.parse(item.gst).gst || 'N/A' : item.gst.gst || 'N/A'}%)` :
                            ` (GST: ${item.gst}%)` :
                            '';
                        html +=
                            `<div class="dropdown-item" data-index="${index}">${item.hsn_code}${gstInfo}</div>`;
                    });

                    // ALWAYS add "Create new" option - regardless of existing entries
                    if (value && currentHsnData.length ==
                        0) { // Only show if user has typed something
                        html +=
                            `<div class="no-results">HSN Code not found</div>`;
                    }

                    dropdown.innerHTML = html;
                    dropdown.classList.add('show');
                    selectedIndex = -1;

                    // Add click listeners to dropdown items
                    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                        item.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            if (this.dataset.newValue) {
                                // Creating new HSN code
                                openHsnModal(this.dataset.newValue);
                            } else if (this.dataset.index !== undefined) {
                                // Selecting existing HSN code
                                const index = parseInt(this.dataset.index);
                                selectHsnCode(currentHsnData[index].hsn_code,
                                    currentHsnData[index].gst, currentHsnData[
                                        index].id);
                            }
                        });
                    });

                } catch (error) {
                    console.error('HSN search error:', error);
                    dropdown.classList.remove('show');
                    currentHsnData = [];
                }
            }, 200);
        });

        // Arrow key navigation
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
                    handleHsnDropdownItemClick(items[selectedIndex]);
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

        // Handle dropdown item selection
        function handleHsnDropdownItemClick(item) {
            dropdown.classList.remove('show');

            if (item.dataset.newValue) {
                openHsnModal(item.dataset.newValue);
            } else if (item.dataset.index !== undefined) {
                const index = parseInt(item.dataset.index);
                selectHsnCode(currentHsnData[index].hsn_code, currentHsnData[index].gst, currentHsnData[index].id);
            }
        }

        function updateHighlight(dropdown, items, selectedIndex) {
            items.forEach((item, index) => {
                item.style.backgroundColor = index === selectedIndex ? '#e9ecef' : '';
            });

            // Auto-scroll within dropdown only
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

    function selectHsnCode(hsnCode, gstData, hsnId = null) {
        const input = document.getElementById('modal-product-hsn');
        const dropdown = document.getElementById('hsnDropdown');

        dropdown.classList.remove('show');

        let displayText = hsnCode;

        if (gstData) {
            try {
                const gst = typeof gstData === 'string' ? JSON.parse(gstData) : gstData;

                const sgst = gst / 2 || 0;
                const cgst = gst / 2 || 0;
                const igst = gst || 0;

                displayText += ` (SGST: ${sgst}%, CGST: ${cgst}%, IGST: ${igst}%)`;

            } catch (e) {
                console.error('Error parsing GST data:', e);
                clearGstFields();
            }
        } else {
            clearGstFields();
        }

        // Set display value to input field
        input.value = displayText;

        // Store hidden actual HSN code for form submission
        let hiddenHsnField = document.getElementById('hidden_hsn_code');
        if (!hiddenHsnField) {
            hiddenHsnField = document.createElement('input');
            hiddenHsnField.type = 'hidden';
            hiddenHsnField.id = 'hidden_hsn_code';
            hiddenHsnField.name = 'hsn_code';
            input.parentNode.appendChild(hiddenHsnField);

            // Prevent visible field from being submitted
            input.name = 'hsn_code_display';
        }
        hiddenHsnField.value = hsnCode;

        // Store HSN ID for backend submission
        let hiddenHsnIdField = document.getElementById('hidden_hsn_id');
        if (!hiddenHsnIdField) {
            hiddenHsnIdField = document.createElement('input');
            hiddenHsnIdField.type = 'hidden';
            hiddenHsnIdField.id = 'hidden_hsn_id';
            hiddenHsnIdField.name = 'hsn_code_id';
            input.parentNode.appendChild(hiddenHsnIdField);
        }
        hiddenHsnIdField.value = hsnId || '';
    }

    // Setup individual product input (like party dropdown)
    function setupProductInput(input) {
        const dropdown = input.nextElementSibling;
        const searchUrl = '{{ route('products.search') }}'; // Your search route
        let timeout;
        let selectedIndex = -1;
        let currentData = [];
        let isProcessingBarcodeScan = false;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const value = this.value.trim();
            selectedIndex = -1;

            if (value.length < 1) {
                dropdown.classList.remove('show');
                const scroll_container = document.querySelector('.custome_scroll');
                if (scroll_container) scroll_container.style.overflowX = 'auto';
                currentData = [];
                return;
            }

            // Check if this looks like a barcode scan
            const isLikelyBarcodeValue = isLikelyBarcode(value);

            timeout = setTimeout(async () => {
                try {
                    let url = `${searchUrl}?search=${encodeURIComponent(value)}`;

                    // Add branch_id if needed
                    const branchSelect = document.getElementById('branch');
                    if (branchSelect && branchSelect.value) {
                        url += `&branch_id=${encodeURIComponent(branchSelect.value)}`;
                    }

                    // console.log('Fetching products from:', url);

                    const response = await fetch(url, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    // console.log('Product search response:', data);

                    currentData = data.products || [];
                    window.currentProductData = currentData;

                    // Check if it's an exact barcode match for auto-selection
                    if (data.auto_select && data.exact_match && currentData.length === 1) {
                        const product = currentData[0];

                        // Set processing flag to prevent navigation
                        isProcessingBarcodeScan = true;

                        // Mark this input as processing barcode
                        input.dataset.processingBarcode = 'true';

                        // console.log('Auto-selecting product for barcode scan:', product);

                        // Auto-select the product for barcode scan
                        setTimeout(() => {
                            selectProduct(product.product_name, product.id, true);
                            // Reset processing flag after selection completes
                            setTimeout(() => {
                                isProcessingBarcodeScan = false;
                                delete input.dataset.processingBarcode;
                            }, 300);
                        }, 50);

                        return; // Exit early, don't show dropdown
                    }

                    let html = '';

                    // Show existing products
                    currentData.forEach((product, index) => {
                        const productInfo = product.barcode ? ` (${product.barcode})` : '';
                        html +=
                            `<div class="dropdown-item" data-index="${index}" data-product-id="${product.id}">${product.product_name}${productInfo}</div>`;
                    });

                    if (value) {
                        html +=
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new Product: "${value}"</div>`;
                    }

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
                                openProductModal(this.dataset.newValue);
                            } else if (this.dataset.index !== undefined) {
                                const index = parseInt(this.dataset.index);
                                selectProduct(currentData[index].product_name,
                                    currentData[index].id);
                            }
                        });
                    });

                } catch (error) {
                    console.error('Product search error:', error);
                    dropdown.classList.remove('show');
                    const scroll_container = document.querySelector('.custome_scroll');
                    if (scroll_container) scroll_container.style.overflowX = 'auto';
                    currentData = [];
                }
            }, isLikelyBarcodeValue ? 100 : 200);
        });

        // Add paste event listener for barcode scanner input
        input.addEventListener('paste', function(e) {
            setTimeout(() => {
                const value = this.value.trim();
                if (value && isLikelyBarcode(value)) {
                    isProcessingBarcodeScan = true;
                    input.dataset.processingBarcode = 'true';
                    this.dispatchEvent(new Event('input'));
                }
            }, 10);
        });

        // Arrow key navigation
        input.addEventListener('keydown', function(e) {
            // Don't process navigation if barcode is being processed
            if (isProcessingBarcodeScan || input.dataset.processingBarcode === 'true') {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
                return;
            }

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
                if (items[selectedIndex].dataset.newValue) {
                    openProductModal(items[selectedIndex].dataset.newValue);
                } else if (items[selectedIndex].dataset.index !== undefined) {
                    const index = parseInt(items[selectedIndex].dataset.index);
                    selectProduct(currentData[index].product_name, currentData[index].id);
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
                const scroll_container = document.querySelector('.custome_scroll');
                if (scroll_container) scroll_container.style.overflowX = 'auto';
                selectedIndex = -1;
            }
        });
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

    // Update highlight function (same as party dropdown) - make it global
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

    // Select product function (like selectParty)
    function selectProduct(productName, productId = null, isBarcodeScan = false) {
        const activeInput = document.activeElement;
        let input, dropdown;

        console.log('productName', productName);
        console.log('productId', productId);
        console.log('activeInput', activeInput);

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

        const hiddenSelect = input.closest('td').querySelector('.hidden-product-select');
        const row = input.closest('tr');

        // Close dropdown
        dropdown.classList.remove('show');
        const scroll_container = document.querySelector('.custome_scroll');
        if (scroll_container) scroll_container.style.overflowX = 'auto';

        // Update search input
        input.value = productName;

        // Find the product data
        let productData = null;

        if (window.currentProductData) {
            productData = window.currentProductData.find(p => p.id == productId);
        }

        if (!productData && window.allProducts) {
            productData = window.allProducts.find(p => p.id == productId);
        }

        // Check if we have valid product data
        if (!productData || !productId) {
            // console.log('No valid product data found');
            input.value = '';
            delete input.dataset.processingBarcode;
            setTimeout(() => {
                input.focus();
            }, 100);
            return;
        }

        // Update hidden select with product data
        hiddenSelect.innerHTML = '';
        const newOption = document.createElement('option');
        newOption.value = productId;
        newOption.textContent = productName;
        newOption.selected = true;

        // Set all data attributes
        newOption.setAttribute('data-mrp', productData.mrp || 0);
        newOption.setAttribute('data-name', productData.product_name);
        newOption.setAttribute('data-sgst', productData.hsn_code.gst / 2 || 0);
        newOption.setAttribute('data-cgst', productData.hsn_code.gst / 2 || 0);
        newOption.setAttribute('data-purchase-rate', productData.purchase_rate || 0);
        newOption.setAttribute('data-sale-rate-a', productData.sale_rate_a || 0);
        newOption.setAttribute('data-sale-rate-b', productData.sale_rate_b || 0);
        newOption.setAttribute('data-sale-rate-c', productData.sale_rate_c || 0);
        newOption.setAttribute('data-barcode', productData.barcode || '');
        newOption.setAttribute('data-category', productData.category_id || '');
        newOption.setAttribute('data-unit-type', productData.unit_types || '');
        newOption.setAttribute('data-box-pcs', productData.box_pcs || productData.converse_box || 1);

        hiddenSelect.appendChild(newOption);

        const detectedBarcodeScan = isBarcodeScan || (productData.barcode && input.value.trim() === productData
            .barcode);

        console.log('Product selected:', {
            productName: productName,
            productId: productId,
            hiddenFieldValue: hiddenSelect.value,
            isBarcodeScan: detectedBarcodeScan
        });

        // Mark that product details are being loaded
        input.dataset.loadingProductDetails = 'true';

        try {
            // Load product details
            loadProductDetails(hiddenSelect);

            // Wait a moment for product details to load, then move focus
            setTimeout(() => {
                // Verify that product details were loaded by checking if product name is set and hidden select has value
                const productNameSet = hiddenSelect.value && hiddenSelect.options[hiddenSelect.selectedIndex];
                const currentItemElement = document.getElementById('current-item');
                const productNameDisplayed = currentItemElement && currentItemElement.textContent &&
                    currentItemElement.textContent !== '-';

                const productDetailsLoaded = productNameSet && productNameDisplayed;

                console.log('Product details loading check:', {
                    hiddenSelectValue: hiddenSelect.value,
                    productNameDisplayed: currentItemElement ? currentItemElement.textContent :
                        'no current-item element',
                    productDetailsLoaded: productDetailsLoaded
                });

                // Clear loading flags
                delete input.dataset.loadingProductDetails;
                delete input.dataset.processingBarcode;

                if (productDetailsLoaded) {
                    if (detectedBarcodeScan) {
                        // Visual feedback for barcode scan
                        input.style.backgroundColor = '#d4edda';
                        input.style.borderColor = '#28a745';

                        setTimeout(() => {
                            input.style.backgroundColor = '';
                            input.style.borderColor = '';
                        }, 2000);

                        // Move to expiry date field for barcode scans
                        const expiryField = row.querySelector('input[name="expiry_date[]"]');
                        if (expiryField) {
                            setTimeout(() => {
                                expiryField.focus();
                                expiryField.select();
                                setDefaultExpiryDate(expiryField);

                            }, 100);
                        }
                    } else {
                        // Move to expiry date field for manual selections
                        const nextField = row.querySelector('input[name="expiry_date[]"]');
                        if (nextField) {
                            setTimeout(() => {
                                nextField.focus();
                                setDefaultExpiryDate(nextField);
                            }, 100);
                        }
                    }
                } else {
                    console.log('Product details not loaded properly, staying on current input');
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }
            }, 200); // Give more time for loadProductDetails to complete

        } catch (error) {
            console.error('Error loading product details:', error);
            delete input.dataset.loadingProductDetails;
            delete input.dataset.processingBarcode;
            setTimeout(() => {
                input.focus();
            }, 100);
        }
    }

    // Function to setup new product rows (for when you add new rows)
    function setupNewProductRow(row) {
        const productInput = row.querySelector('.product-search-input');
        if (productInput) {
            setupProductInput(productInput);
        }
    }

    // ==================================

    document.addEventListener('DOMContentLoaded', function() {
        const addProductBtn = document.querySelector('.btn.btn-primary.shadow-md.mr-2.btn-hover');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addProductRow();
            });
        }

        // Set current date for delivery date
        const today = new Date();
        const todayString = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
        document.getElementById('delivery_date').value = todayString;
        document.getElementById('bill_date').value = todayString;

        // Set current date for display
        const todayDisplay = today.toLocaleDateString();
        document.getElementById('current-date').textContent = todayDisplay;

        // Initialize calculations
        calculateAllTotals();

        // Enter navigation setup call
        setupEnterNavigation();
        // Delete product row
        updateDeleteButtons();
        // Initialize Party dropdown
        initPartyDropdown();
        initPartyModal();

        initProductDropdown();
        initHsnDropdown();

    });

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
            hiddenPartyIdField.name = 'party_name'; // This will be the actual field name for backend
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
        // console.log('Opening party modal with name:', partyName);
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
        modal.style.zIndex = '50';
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
        modal.classList.add('hide');
        // modal.style.display = 'none';
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
                    // console.log('Party creation response:', data);

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
                    if (value) {
                        html +=
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new party: "${value}"</div>`;
                    }

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
                                selectParty(currentPartyData[index].party_name,
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


    // Add new product row
    function addProductRow() {
        const tableBody = document.getElementById('product-table-body');
        const existingRow = tableBody.querySelector('tr');
        const newRow = existingRow.cloneNode(true);

        // Clear all input values
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        // Remove validation classes
        newRow.querySelectorAll('input, select').forEach(el => {
            el.classList.remove('border-red-500', 'border-green-500');
        });

        // Remove error messages
        newRow.querySelectorAll('.purchase-error').forEach(el => el.remove());

        // Update event handlers for new row
        const productSearchInput = newRow.querySelector('.product-search-input');
        if (productSearchInput) {
            productSearchInput.setAttribute('onkeyup', 'searchProducts(this)');
            // productSearchInput.setAttribute('onfocus', 'showProductDropdown(this)');
        }

        const hiddenSelect = newRow.querySelector('.hidden-product-select');
        if (hiddenSelect) {
            hiddenSelect.setAttribute('onchange', 'loadProductDetails(this)');
        }

        const inputs = newRow.querySelectorAll(
            'input[name="expiry_date[]"], input[name="mrp[]"], input[name="box[]"], input[name="pcs[]"], input[name="purchase_rate[]"], input[name="discount_percent[]"], input[name="discount_lumpsum[]"]'
        );
        inputs.forEach(input => {
            input.setAttribute('onchange', 'calculateRowAmount(this)');
        });

        // Ensure the delete button has the correct onclick handler
        const deleteButton = newRow.querySelector('button[onclick*="removeRow"]');
        if (deleteButton) {
            deleteButton.setAttribute('onclick', 'removeRow(this)');
        }

        tableBody.appendChild(newRow);

        setupNewProductRow(newRow);

        // Update delete button states
        updateDeleteButtons();
        // const expiryInput = newRow.querySelector('input[name="expiry_date[]"]');
        // setDefaultExpiryDate(expiryInput);
    }

    function setDefaultExpiryDate(input) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
        const defaultDate = `${yyyy}-${mm}-01`; // e.g., "2025-08-01"
        input.value = defaultDate;
    }

    function removeRow(button) {
        const row = button.closest('tr');
        const tableBody = row.closest('tbody');
        const allRows = tableBody.querySelectorAll('tr');

        if (allRows.length > 1) {
            row.remove();
            calculateAllTotals();

            // Update currentRowIndex if needed
            const remainingRows = tableBody.querySelectorAll('tr');
            if (currentRowIndex >= remainingRows.length) {
                currentRowIndex = remainingRows.length - 1;
            }

            // Re-enable/disable delete buttons based on row count
            updateDeleteButtons();

            // Hide purchase history if no product is selected in any row
            const hasSelectedProducts = Array.from(remainingRows).some(row => {
                const hiddenSelect = row.querySelector('.hidden-product-select');
                return hiddenSelect && hiddenSelect.value;
            });

            if (!hasSelectedProducts) {
                hidePurchaseHistory();
            }
        } else {
            alert('At least one product row is required.');
        }
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
        const productId = selectedOption.value;

        // Update current item details
        document.getElementById('current-item').textContent = productName;
        document.getElementById('current-srate').textContent = productSaleRateA;
        // document.getElementById('current-mrp').textContent = parseFloat(productMrp).toFixed(2);

        // Auto-fill purchase rate if available
        const row = selectElement.closest('tr');
        const purchaseRateInput = row.querySelector('input[name="purchase_rate[]"]');
        const mrpInput = row.querySelector('input[name="mrp[]"]');

        if (purchaseRateInput && productPurchaseRate > 0) {
            purchaseRateInput.value = parseFloat(productPurchaseRate).toFixed(2);
        }
        if (mrpInput && productMrp > 0 && !mrpInput.value) {
            mrpInput.value = parseFloat(productMrp).toFixed(2);
        }

        // Load purchase history for this product
        if (productId && productName !== '-') {
            loadPurchaseHistory(productId, productName);
        } else {
            hidePurchaseHistory();
        }

        calculateAllTotals();
    }

    // Function to load purchase history for selected product
    function loadPurchaseHistory(productId, productName) {
        // Show the purchase history section
        const historySection = document.getElementById('purchase-history-section');
        const historyProductName = document.getElementById('history-product-name');
        const historyBody = document.getElementById('purchase-history-body');

        historySection.style.display = 'block';
        historyProductName.textContent = productName;

        // Show loading state
        historyBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';

        // Make API call to get purchase history
        fetch(`{{ route('purchase.history') }}?product_id=${productId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.history && data.history.length > 0) {
                    let historyHtml = '';
                    data.history.forEach(purchase => {
                        const date = new Date(purchase.bill_date).toLocaleDateString('en-GB');
                        // const qty = purchase.pcs || (purchase.box + purchase.pcs);
                        const rate = parseFloat(purchase.p_rate || 0).toFixed(2);
                        const amount = parseFloat(purchase.amount || 0).toFixed(2);
                        const discount = parseFloat(purchase.discount || 0).toFixed(2);
                        const discountRs = purchase.lumpsum || 0;

                        historyHtml += `
                        <tr>
                            <td class="text-center">${date}</td>
                            <td class="text-center">${purchase.party_name || '-'}</td>
                            <td class="text-center">${purchase.bill_no || '-'}</td>
                            <td class="text-center">${purchase.box}</td>
                            <td class="text-center">${purchase.pcs}</td>
                            <td class="text-center">₹${rate}</td>
                            <td class="text-center">${discount}%</td>
                            <td class="text-center">₹${discountRs}</td>
                            <td class="text-center">₹${amount}</td>
                        </tr>
                    `;
                    });
                    historyBody.innerHTML = historyHtml;
                } else {
                    historyBody.innerHTML =
                        '<tr><td colspan="9" class="text-center text-gray-500">No recent purchase history found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading purchase history:', error);
                historyBody.innerHTML =
                    '<tr><td colspan="9" class="text-center text-red-500">Error loading purchase history</td></tr>';
            });
    }

    // Function to hide purchase history section
    function hidePurchaseHistory() {
        const historySection = document.getElementById('purchase-history-section');
        historySection.style.display = 'none';
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
            const hiddenSelect = row.querySelector('.hidden-product-select');
            const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
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
