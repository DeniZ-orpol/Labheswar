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
            Transfer Stock
        </h2>
        <form action="#" method="POST"
            class="form-updated validate-form box rounded-md mt-5 p-5">
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
                    <label for="branch" class="form-label w-full flex flex-col sm:flex-row">
                        Branch<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="branch" name="branch" class="form-control field-new" onchange="calculateAllTotals()">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value={{$branch->id}}>{{$branch->name}}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="date" class="form-label w-full flex flex-col sm:flex-row">
                        Date
                    </label>
                    <input id="date" type="date" name="date" class="form-control field-new"
                        placeholder="DD/MM or YYYY-MM-DD">
                </div>

                <!-- Chalan No -->
                <div class="input-form col-span-4 mt-3">
                    <label for="chalan_no" class="form-label w-full flex flex-col sm:flex-row">
                        Chalan No
                    </label>
                    <input id="chalan_no" type="text" name="chalan_no" class="form-control field-new"
                        placeholder="Enter Purchase chalan NO" maxlength="255" readonly>
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
                            {{-- <th scope="col" class="required">Expiry</th> --}}
                            <th scope="col" class="required">mrp</th>
                            <th scope="col" class="required">box</th>
                            <th scope="col" class="required">pcs</th>
                            {{-- <th scope="col" class="required">free</th> --}}
                            {{-- <th scope="col" class="required">p.rate</th> --}}
                            {{-- <th scope="col" class="text-end">dis(%)</th> --}}
                            {{-- <th scope="col" class="text-end">dis(₹)</th> --}}
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
                                <select name="product[]" class="form-select text-sm w-full rounded-md hidden-product-select"
                                    style="display: none;" onchange="loadProductDetails(this)">
                                    <option value="">Please Select product</option>
                                    {{-- @foreach ($products as $product)
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
                                    @endforeach --}}
                                </select>
                            </td>

                            <!-- MRP -->
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

                            <!-- Amount -->
                            <td>
                                <input type="number" name="amount[]" class="form-control field-new text-end"
                                    maxlength="255" readonly step="0.01">
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
            {{-- <div class="intro-y grid grid-cols-3 gap-5 mt-5 col-span-12">
                <!-- Item info column -->
                <div class="p-5">
                    <p><strong>Item:</strong> <span id="current-item">-</span></p>
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
            </div> --}}
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
            {{-- <input type="hidden" name="receipt_subtotal" id="receipt-subtotal-hidden">
            <input type="hidden" name="receipt_total_discount" id="receipt-total-discount-hidden">
            <input type="hidden" name="receipt_total_gst_amount" id="receipt-total-gst-amount-hidden">
            <input type="hidden" name="receipt_total_amount" id="receipt-total-amount-hidden"> --}}

            <div>
                <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
            </div>
        </form>

        <!-- Purchase History Section -->
        {{-- <div id="purchase-history-section" class="box rounded-md mt-5 p-5" style="display: none;">
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
        </div> --}}

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
    {{-- <div id="party-modal" class="modal" aria-hidden="true" style="z-index: 50">
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
                            <label for="modal-company-name" class="form-label">Company Name<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-company-name" name="company_name" type="text" class="form-control"
                                placeholder="Enter company name" required>
                        </div>
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
                        <div class="col-span-6">
                            <label for="modal-station" class="form-label">Station<span
                                    style="color: red;margin-left: 3px;">*</span></label>
                            <input id="modal-station" name="station" type="text" class="form-control"
                                placeholder="Enter station" required>
                        </div>
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
                        <div class="col-span-6">
                            <label for="modal-party-email" class="form-label">Email</label>
                            <input id="modal-party-email" name="party_email" type="email" class="form-control"
                                placeholder="Enter Email address">
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
    </div> --}}
@endsection
