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
            /* Adjust as needed */
            /* background-color: #f2f2f2; */
            /* padding: 10px; */
            /* border: 1px solid #ddd; */
            box-sizing: border-box;
        }

        .custom-dropzone {
            border: 2px dashed #1abc9c;
            border-radius: 12px;
            height: 300px;
            width: 100%;
            position: relative;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .custom-dropzone:hover {
            border-color: #16a085;
        }

        .custom-dropzone input[type="file"] {
            opacity: 0;
            position: absolute;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }

        .custom-dropzone span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #bbb;
            font-size: 16px;
            pointer-events: none;
        }

        /* Company Search Dropdown Styles */
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
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Product
        </h2>
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
            class="form-updated validate-form">
            @csrf <!-- CSRF token for security -->
            <div class="row">
                <div class="column p-5">
                    {{-- <div class="grid grid-cols-12 gap-2 grid-updated"> --}}

                    <!-- barcode -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="barcode" class="form-label w-full flex flex-col sm:flex-row">
                            Barcode<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="barcode" type="text" name="product_barcode" class="form-control field-new" required>
                    </div>

                    <!-- Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_name" class="form-label w-full flex flex-col sm:flex-row">
                            Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="product_name" type="text" name="product_name" class="form-control field-new"
                            placeholder="Enter Product name" required maxlength="255">
                    </div>

                    <!-- search option -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="search_option" class="form-label w-full flex flex-col sm:flex-row">
                            Search Option
                        </label>
                        <input id="search_option" type="text" name="search_option" class="form-control field-new">
                    </div>

                    <!-- Unit Types -->
                    <div class="input-form col-span-3 mt-3">
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

                    <!-- Company -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_company" class="form-label w-full flex flex-col sm:flex-row">
                            Company
                        </label>
                        <input id="product_company" type="text" name="product_company" class="form-control field-new">
                    </div> --}}
                    <!-- Company with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_company" class="form-label w-full flex flex-col sm:flex-row">
                            Company
                        </label>
                        <div class="search-dropdown">
                            <input id="product_company" type="text" name="product_company"
                                class="form-control field-new search-input" placeholder="Search or type company name"
                                autocomplete="off">
                            <div class="dropdown-list" id="companyDropdown"></div>
                        </div>
                    </div>

                    <!-- category -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_category" class="form-label w-full flex flex-col sm:flex-row">
                            category
                        </label>
                        <input id="product_category" type="text" name="product_category" class="form-control field-new">
                    </div> --}}
                    <!-- Category with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_category" class="form-label w-full flex flex-col sm:flex-row">
                            Category
                        </label>
                        <div class="search-dropdown">
                            <input id="product_category" type="text" name="product_category"
                                class="form-control field-new search-input" placeholder="Search or type category"
                                autocomplete="off">
                            <div class="dropdown-list" id="categoryDropdown"></div>
                        </div>
                    </div>

                    <!-- HSN code -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="hsn_code" class="form-label w-full flex flex-col sm:flex-row">
                            HSN Code
                        </label>
                        <input id="hsn_code" type="text" name="hsn_code" class="form-control field-new">
                    </div> --}}
                    <!-- HSN Code with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="hsn_code" class="form-label w-full flex flex-col sm:flex-row">
                            HSN Code
                        </label>
                        <div class="search-dropdown">
                            <input id="hsn_code" type="text" name="hsn_code" class="form-control field-new search-input"
                                placeholder="Search or type HSN code" autocomplete="off">
                            <div class="dropdown-list" id="hsnDropdown"></div>
                        </div>
                    </div>

                    <!-- sgst -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_sgst" class="form-label w-full flex flex-col sm:flex-row">
                            SGST
                        </label>
                        <input id="product_sgst" type="number" step="0.01" name="sgst"
                            class="form-control field-new">
                    </div> --}}

                    <!-- CGST -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_cgst" class="form-label w-full flex flex-col sm:flex-row">
                            CGST
                        </label>
                        <input id="product_cgst" type="number" step="0.01" name="cgst"
                            class="form-control field-new">
                    </div> --}}

                    <!-- IGST  -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_igst" class="form-label w-full flex flex-col sm:flex-row">
                            IGST
                        </label>
                        <input id="product_igst" type="number" step="0.01" name="igst"
                            class="form-control field-new">
                    </div> --}}

                    <!-- CESS -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_cess" class="form-label w-full flex flex-col sm:flex-row">
                            CESS
                        </label>
                        <input id="product_cess" type="number" step="0.01" name="cess"
                            class="form-control field-new">
                    </div>

                    <!-- MRP -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_mrp" class="form-label w-full flex flex-col sm:flex-row">
                            MRP
                        </label>
                        <input id="product_mrp" type="number" name="mrp" step="0.01" class="form-control field-new">
                    </div>

                    <!-- Purchase rate -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_purchase_rate" class="form-label w-full flex flex-col sm:flex-row">
                            Purchase Rate
                        </label>
                        <input id="product_purchase_rate" type="number" step="0.0001" name="purchase_rate"
                            class="form-control field-new">
                    </div>

                    <!-- Sale rate A -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_a" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate A
                        </label>
                        <input id="product_sale_rate_a" type="number" step="0.01" name="sale_rate_a"
                            class="form-control field-new">
                    </div>

                    <!-- Sale rate B -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_b" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate B
                        </label>
                        <input id="product_sale_rate_b" type="number" step="0.01" name="sale_rate_b"
                            class="form-control field-new">
                    </div>

                    <!-- Sale rate C -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_c" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate C
                        </label>
                        <input id="product_sale_rate_c" type="number" step="0.01" name="sale_rate_c"
                            class="form-control field-new">
                    </div>

                    <!-- Carton -->
                    <div class="row pt-5">
                        <!-- Converse carton -->
                        <div class="column pr-5">
                            <div class="input-form col-span-3">
                                <label for="converse_carton" class="form-label w-full flex flex-col sm:flex-row">
                                    Converse Carton
                                </label>
                                <input id="converse_carton" type="number" name="converse_carton"
                                    class="form-control field-new">
                            </div>
                        </div>
                        <!-- Carton barcode -->
                        <div class="column">
                            <div class="input-form col-span-3">
                                <label for="carton_barcode" class="form-label w-full flex flex-col sm:flex-row">
                                    Carton Barcode
                                </label>
                                <input id="carton_barcode" type="number" name="carton_barcode"
                                    class="form-control field-new">
                            </div>
                        </div>
                    </div>

                    <!-- BOX -->
                    <div class="row">
                        <!-- Converse BOX -->
                        <div class="column pr-5">
                            <div class="input-form col-span-3">
                                <label for="converse_box" class="form-label w-full flex flex-col sm:flex-row">
                                    Converse Box
                                </label>
                                <input id="converse_box" type="number" name="converse_box"
                                    class="form-control field-new">
                            </div>
                        </div>
                        <!-- BOX barcode -->
                        <div class="column">
                            <div class="input-form col-span-3">
                                <label for="box_barcode" class="form-label w-full flex flex-col sm:flex-row">
                                    Box Barcode
                                </label>
                                <input id="box_barcode" type="number" name="box_barcode"
                                    class="form-control field-new">
                            </div>
                        </div>
                    </div>


                    <!-- Converse pcs -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="converse_pcs" class="form-label w-full flex flex-col sm:flex-row">
                            Converse PCS
                        </label>
                        <input id="converse_pcs" type="number" name="converse_pcs" class="form-control field-new">
                    </div> --}}

                    <!-- Negative Billing -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="negative_billing" class="form-label w-full flex flex-col sm:flex-row">
                            Negative Billing
                        </label>
                        <select id="negative_billing" name="negative_billing" class="form-control field-new">
                            <option value="NO" selected>NO</option>
                            <option value="YES">YES</option>
                            {{-- @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach --}}
                        </select>
                    </div>

                    <!-- Min quantity -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="min_qty" class="form-label w-full flex flex-col sm:flex-row">
                            Minimum Quantity
                        </label>
                        <input id="min_qty" type="number" name="min_qty" class="form-control field-new">
                    </div>

                    <!-- Reorder quantity -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="reorder_qty" class="form-label w-full flex flex-col sm:flex-row">
                            Reorder Quantity
                        </label>
                        <input id="reorder_qty" type="number" name="reorder_qty" class="form-control field-new">
                    </div>

                    <!-- Discount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="negative_billing" class="form-label w-full flex flex-col sm:flex-row">
                            Discount
                        </label>
                        <select id="discount" name="discount" class="form-control field-new">
                            <option value="" selected>Choose...</option>
                            <option value="applicable">Applicable</option>
                            <option value="not_applicable">Not Applicable</option>
                        </select>
                    </div>

                    <!-- Max Discount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="max_discount" class="form-label w-full flex flex-col sm:flex-row">
                            Max Discount (%)
                        </label>
                        <input id="max_discount" type="number" step="0.0001" name="max_discount"
                            class="form-control field-new">
                    </div>

                    <!-- Discount Scheme -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="discount_scheme" class="form-label w-full flex flex-col sm:flex-row">
                            Discount Scheme
                        </label>
                        <input id="discount_scheme" type="text" name="discount_scheme"
                            class="form-control field-new">
                    </div>

                    <!-- Bonus Use -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="bonus_use" class="form-label w-full flex flex-col sm:flex-row">
                            Bonus Use
                        </label>
                        <select id="bonus_use" name="bonus_use" class="form-control field-new">
                            <option value="no" selected>NO</option>
                            <option value="yes">YES</option>
                        </select>
                        {{-- <input id="bonus_use" type="text" name="bonus_use" class="form-control field-new"> --}}
                    </div>

                    <!-- Submit Button -->
                </div>

                <div class="column">
                    <!-- Loose quantity decimal button -->
                    <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="decimal_btn" class="form-label w-full flex flex-col sm:flex-row">
                            Decimal
                        </label>
                        <input id="decimal_btn" type="checkbox" name="decimal_btn" class="form-check-input mr-0 ml-3">
                    </div>

                    <!-- Sale online toggle -->
                    <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="sale_online" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Online
                        </label>
                        <input id="sale_online" type="checkbox" name="sale_online" class="form-check-input mr-0 ml-3">
                    </div>

                    <!-- GST active toggle -->
                    {{-- <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="gst_active" class="form-label w-full flex flex-col sm:flex-row">
                            GST
                        </label>
                        <input id="gst_active" type="checkbox" name="gst_active" class="form-check-input mr-0 ml-3">
                    </div> --}}

                    <!-- Product Image -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="fileInput" class="form-label w-full flex flex-col sm:flex-row">
                            Product Image
                        </label>
                        <div class="input-form col-span-3 mt-3"
                            style="position: relative; border: 2px dashed #ccc; border-radius: 8px; padding: 50px 40px; text-align: center; background-color: #f9f9f9; cursor: pointer;">
                            <div class="fallback">
                                <input name="product_image" type="file" id="fileInput" accept="image/*"
                                    style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 1;"
                                    onchange="previewImage(this)" />
                            </div>
                            <div id="uploadMessage" style="color: #666; font-size: 16px; pointer-events: none;">
                                Drop Product image file here or click to upload.
                            </div>
                            <div id="imagePreview" style="display: none; max-width: 300px; margin: 0 auto;">
                                <img id="previewImg"
                                    style="width: 100%; height: auto; border-radius: 8px; margin-top: 10px;" />
                                <div style="margin-top: 10px; font-size: 14px; color: #666;">
                                    <span id="fileName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- </form> --}}
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        {{-- </div> --}}
        <!-- END: Validation Form -->
        <!-- BEGIN: Success Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration success!</div>
                <div class="text-slate-500 mt-1"> Please check your e-mail for further info! </div>
            </div>
        </div>
        <!-- END: Success Notification Content -->
        <!-- BEGIN: Failed Notification Content -->
        <div id="failed-notification-content" class="toastify-content hidden flex">
            <i class="text-danger" data-lucide="x-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Registration failed!</div>
                <div class="text-slate-500 mt-1"> Please check the fileld form. </div>
            </div>
        </div>
        <!-- END: Failed Notification Content -->
    </div>

    <!-- BEGIN: HSN Modal -->
    <div id="hsn-modal" class="modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New HSN Code</h2>
                </div>
                <!-- END: Modal Header -->

                <form action="{{ route('hsn_codes.modalstore') }}" id="hsn-form" method="POST">
                    @csrf
                    <!-- BEGIN: Modal Body -->
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12">
                            <label for="modal-hsn-code" class="form-label">HSN Code</label>
                            <input id="modal-hsn-code" name="hsn_code" type="text" class="form-control bg-gray-100"
                                readonly>
                        </div>
                        <div class="col-span-12">
                            <label for="modal-gst" class="form-label">GST (%)</label>
                            <input id="modal-gst" name="gst" type="number" step="0.01" class="form-control"
                                placeholder="Enter GST percentage" required>
                        </div>
                        <div class="col-span-12">
                            <label for="modal-short-name" class="form-label">Short Name</label>
                            <input id="modal-short-name" name="short_name" type="text"
                                class="form-control bg-gray-100">
                        </div>
                    </div>
                    <!-- END: Modal Body -->

                    <!-- BEGIN: Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" id="cancel-hsn-modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" class="btn btn-primary w-20">Save</button>
                    </div>
                    <!-- END: Modal Footer -->
                </form>
            </div>
        </div>
    </div>
    <!-- END: HSN Modal -->

    <!-- BEGIN: Category Modal -->

    <!-- END: Category Modal -->

    <!-- BEGIN: Company Modal -->

    <!-- END: Company Modal -->
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('company-modal');
        const openModalBtn = document.getElementById('open-company-modal'); // You need this
        const cancelBtn = document.getElementById('cancel-company-modal');

        // OPEN
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function() {
                modal.classList.add('modal-open');
                modal.style.display = 'flex'; // show it
            });
        }

        // CLOSE
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('modal-open');
                modal.style.display = 'none'; // hide it
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Company dropdown (existing)
        initSearchDropdown('product_company', 'companyDropdown', '{{ route('companies.search') }}', 'company');
        // Category dropdown
        initSearchDropdown('product_category', 'categoryDropdown', '{{ route('categories.search') }}',
            'category');

        // HSN Code dropdown with GST auto-fill
        initHsnDropdown();
        initHsnModal();
        initCategoryModal();
        initCompanyModal();
    });

    // Global variables to store selected IDs
    let selectedCategoryId = null;
    let selectedCompanyId = null;

    // Function to select HSN code and auto-fill GST - NOW GLOBAL
    function selectHsnCode(hsnCode, gstData, hsnId = null) {
        const input = document.getElementById('hsn_code');
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

    // Function to open HSN modal
    function openHsnModal(hsnCode) {
        console.log('✅ openHsnModal CALLED with HSN:', hsnCode);
        const modal = document.getElementById('hsn-modal');
        const modalHsnInput = document.getElementById('modal-hsn-code');
        const modalGstInput = document.getElementById('modal-gst');
        const dropdown = document.getElementById('hsnDropdown');

        // Close dropdown
        dropdown.classList.remove('show');

        // Set HSN code in modal
        modalHsnInput.value = hsnCode;
        modalGstInput.value = '';

        // Show modal
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.marginTop = '50px';
        modal.style.marginLeft = '0';
        modal.classList.add('show');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        // Focus on GST input
        setTimeout(() => {
            modalGstInput.focus();
        }, 100);
    }

    // Function to close HSN modal
    function closeHsnModal() {
        const modal = document.getElementById('hsn-modal');
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    }

    // Initialize modal functionality
    function initHsnModal() {
        const modal = document.getElementById('hsn-modal');
        const cancelBtn = document.getElementById('cancel-hsn-modal');
        const form = modal.querySelector('form');
        const modalHsnInput = document.getElementById('modal-hsn-code');
        const modalGstInput = document.getElementById('modal-gst');




        // Cancel button
        cancelBtn.addEventListener('click', closeHsnModal);

        // Handle form submission with AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent normal form submission

            // Use existing element references - fastest approach
            const hsnCode = modalHsnInput.value.trim();
            const gst = modalGstInput.value.trim();
            const shortName = document.getElementById('modal-short-name').value.trim();
            // Quick validation
            if (!hsnCode || !gst) {
                alert('HSN Code and GST are required');
                return;
            }

            // Fastest approach - direct URLSearchParams
            const params = new URLSearchParams();
            params.append('hsn_code', hsnCode);
            params.append('gst', gst);
            params.append('short_name', shortName);
            // Add branch_id if needed
            const branchSelect = document.getElementById('branch');
            if (branchSelect?.value) {
                params.append('branch_id', branchSelect.value);
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Saving...';
            submitBtn.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: params,
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
                    console.log('Success response:', data);

                    if (data.success) {
                        // Close modal first
                        closeHsnModal();

                        // Update HSN field using global function with HSN ID
                        selectHsnCode(data.data.hsn_code, data.data.gst, data.data.id);

                        // Clear form values
                        modalHsnInput.value = '';
                        modalGstInput.value = '';

                    } else {
                        alert('Error: ' + (data.message || 'Failed to create HSN Code'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating HSN Code: ' + error.message);
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
                closeHsnModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeHsnModal();
            }
        });

        // Handle Enter key in modal GST input
        modalGstInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    }

    // HSN dropdown and GST autofill
    function initHsnDropdown() {
        const input = document.getElementById('hsn_code');
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
                        console.log(item);
                        const gstInfo = item.gst ?
                            // ` (GST: ${typeof item.gst === 'string' ? JSON.parse(item.gst).gst || 'N/A' : item.gst.gst || 'N/A'}%)` :
                            ` (GST: ${item.gst}%)` :
                            '';
                        html +=
                            `<div class="dropdown-item" data-index="${index}">${item.hsn_code}${gstInfo}</div>`;
                    });

                    // ALWAYS add "Create new" option - regardless of existing entries
                    if (value) { // Only show if user has typed something
                        html +=
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new: "${value}" with custom GST</div>`;
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
    }

    // Function to select category and store ID
    function selectCategory(categoryName, categoryId = null) {
        const input = document.getElementById('product_category');
        const dropdown = document.getElementById('categoryDropdown');

        dropdown.classList.remove('show');
        input.value = categoryName;

        // Store category ID in hidden field
        let hiddenCategoryIdField = document.getElementById('hidden_category_id');
        if (!hiddenCategoryIdField) {
            hiddenCategoryIdField = document.createElement('input');
            hiddenCategoryIdField.type = 'hidden';
            hiddenCategoryIdField.id = 'hidden_category_id';
            hiddenCategoryIdField.name = 'category_id';
            input.parentNode.appendChild(hiddenCategoryIdField);
        }
        hiddenCategoryIdField.value = categoryId || '';

        console.log('Category selected:', {
            categoryName: categoryName,
            categoryId: categoryId,
            hiddenFieldValue: hiddenCategoryIdField.value
        });
    }

    // Function to select company and store ID
    function selectCompany(companyName, companyId = null) {
        const input = document.getElementById('product_company');
        const dropdown = document.getElementById('companyDropdown');

        dropdown.classList.remove('show');
        input.value = companyName;

        // Store company ID in hidden field
        let hiddenCompanyIdField = document.getElementById('hidden_company_id');
        if (!hiddenCompanyIdField) {
            hiddenCompanyIdField = document.createElement('input');
            hiddenCompanyIdField.type = 'hidden';
            hiddenCompanyIdField.id = 'hidden_company_id';
            hiddenCompanyIdField.name = 'company_id';
            input.parentNode.appendChild(hiddenCompanyIdField);
        }
        hiddenCompanyIdField.value = companyId || '';

        console.log('Company selected:', {
            companyName: companyName,
            companyId: companyId,
            hiddenFieldValue: hiddenCompanyIdField.value
        });
    }

    // Functions to open modals
    // function openCategoryModal(categoryName) {
    //     showModal('category-modal', 'modal-category-name', categoryName, 'modal-category-name');
    // }

    // function showModal(modalId, inputId, inputValue = '', focusId = '') {
    //     const modal = document.getElementById(modalId);
    //     const input = document.getElementById(inputId);

    //     if (!modal) {
    //         console.error(`Modal with ID '${modalId}' not found.`);
    //         return;
    //     }

    //     // Set input value if provided
    //     if (input) {
    //         input.value = inputValue || '';
    //     }

    //     // Show the modal
    //     modal.classList.add('modal-open');
    //     modal.style.display = 'flex';

    //     // Focus input after short delay
    //     if (focusId) {
    //         setTimeout(() => {
    //             const focusInput = document.getElementById(focusId);
    //             if (focusInput) focusInput.focus();
    //         }, 100);
    //     }

    //     // Cancel button: close modal (add listener only once)
    //     const cancelBtn = modal.querySelector(
    //         '.btn-outline-secondary, .modal-cancel, #cancel-category-modal'
    //     );
    //     if (cancelBtn && !cancelBtn.hasAttribute('data-close-bound')) {
    //         cancelBtn.addEventListener('click', function() {
    //             modal.classList.remove('modal-open');
    //             modal.style.display = 'none';
    //         });
    //         cancelBtn.setAttribute('data-close-bound', 'true');
    //     }

    //     // Click outside modal to close (add listener only once)
    //     if (!modal.hasAttribute('data-overlay-close-bound')) {
    //         modal.addEventListener('click', function(e) {
    //             if (e.target === modal) {
    //                 modal.classList.remove('modal-open');
    //                 modal.style.display = 'none';
    //             }
    //         });
    //         modal.setAttribute('data-overlay-close-bound', 'true');
    //     }
    // }




    // function openCompanyModal(companyName) {
    //     showModal('company-modal', 'name', companyName, 'name');
    // }

    // Generic function to show modal
    // function showModal(modalId, inputId, value, focusId) {
    //     const modal = document.getElementById(modalId);
    //     const input = document.getElementById(inputId);
    //     const focusInput = document.getElementById(focusId);

    //     // Set value and show modal
    //     input.value = value;
    //     modal.style.visibility = 'visible';
    //     modal.style.opacity = '1';
    //     modal.style.marginTop = '50px';
    //     modal.style.marginLeft = '0';
    //     modal.classList.add('show');
    //     modal.classList.remove('hidden');
    //     modal.setAttribute('aria-hidden', 'false');

    //     // Focus on input
    //     setTimeout(() => {
    //         focusInput.focus();
    //     }, 100);
    // }

    // Functions to close modals
    // function closeCategoryModal() {
    //     closeModal('category-modal');
    // }

    // function closeCompanyModal() {
    //     closeModal('company-modal');
    // }

    // Generic function to close modal
    // function closeModal(modalId) {
    //     const modal = document.getElementById(modalId);
    //     modal.classList.remove('show');
    //     modal.style.display = 'none';
    //     modal.style.visibility = 'hidden';
    //     modal.style.opacity = '0';
    // }

    // Initialize Category Modal
    // function initCategoryModal() {
    //     const modal = document.getElementById('category-modal');
    //     const cancelBtn = document.getElementById('cancel-category-modal');
    //     const form = modal.querySelector('form');
    //     const modalCategoryInput = document.getElementById('modal-category-name');
    //     const modalImageInput = document.getElementById('modal-category-image'); // <-- add image input
    //     const submitBtn = form.querySelector('button[type="submit"]');

    //     cancelBtn.addEventListener('click', closeCategoryModal);

    //     form.addEventListener('submit', function(e) {
    //         e.preventDefault();

    //         const categoryName = modalCategoryInput.value.trim();

    //         if (!categoryName) {
    //             alert('Category name is required');
    //             return;
    //         }

    //         const formData = new FormData();
    //         formData.append('name', categoryName);

    //         if (modalImageInput?.files[0]) {
    //             formData.append('image', modalImageInput.files[0]); // append image
    //         }

    //         const branchSelect = document.getElementById('branch');
    //         if (branchSelect?.value) {
    //             formData.append('branch_id', branchSelect.value);
    //         }

    //         const originalText = submitBtn.textContent;
    //         submitBtn.textContent = 'Saving...';
    //         submitBtn.disabled = true;

    //         fetch(form.action, {
    //                 method: 'POST',
    //                 body: formData,
    //                 headers: {
    //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //                     'X-Requested-With': 'XMLHttpRequest'
    //                 }
    //             })
    //             .then(response => {
    //                 if (!response.ok) {
    //                     return response.json().then(err => Promise.reject(err));
    //                 }
    //                 return response.json();
    //             })
    //             .then(data => {
    //                 console.log('Category creation response:', data);

    //                 if (data.success && data.data?.name && data.data?.id) {
    //                     closeCategoryModal();
    //                     selectCategory(data.data.name, data.data.id);
    //                     form.reset();
    //                     modalImageInput.value = '';
    //                 } else if (data.success) {
    //                     // Success true, but data is malformed
    //                     console.warn('Success response but incomplete data:', data);
    //                     closeCategoryModal();
    //                     form.reset();
    //                     modalImageInput.value = '';
    //                     // Don’t show any error popup in this case
    //                 } else {
    //                     alert('Error: ' + (data.message || 'Failed to create category'));
    //                 }
    //             })

    //             .catch(error => {
    //                 console.error('Error:', error);
    //                 const errorMessage = error?.errors?.name?.[0] || error.message || 'Unknown error';
    //                 alert('Error creating category: ' + errorMessage);
    //             })
    //             .finally(() => {
    //                 submitBtn.textContent = originalText;
    //                 submitBtn.disabled = false;
    //             });
    //     });

    //     // Close modal when clicking outside or pressing Escape
    //     modal.addEventListener('click', function(e) {
    //         if (e.target === modal) closeCategoryModal();
    //     });

    //     document.addEventListener('keydown', function(e) {
    //         if (e.key === 'Escape' && modal.classList.contains('show')) {
    //             closeCategoryModal();
    //         }
    //     });

    //     // Handle Enter key
    //     modalCategoryInput.addEventListener('keydown', function(e) {
    //         if (e.key === 'Enter') {
    //             e.preventDefault();
    //             form.dispatchEvent(new Event('submit'));
    //         }
    //     });
    // }



    // Initialize Company Modal
    // function initCompanyModal() {
    //     const modal = document.getElementById('company-modal');
    //     const cancelBtn = document.getElementById('cancel-company-modal');
    //     const form = modal.querySelector('form');
    //     const modalCompanyInput = document.getElementById('name');

    //     cancelBtn.addEventListener('click', closeCompanyModal);

    //     form.addEventListener('submit', function(e) {
    //         e.preventDefault();

    //         const companyName = modalCompanyInput.value.trim();

    //         if (!companyName) {
    //             alert('Company name is required');
    //             return;
    //         }

    //         const params = new URLSearchParams();
    //         params.append('name', companyName); // ✅ Corrected field name

    //         const branchSelect = document.getElementById('branch');
    //         if (branchSelect?.value) {
    //             params.append('branch_id', branchSelect.value);
    //         }

    //         const submitBtn = form.querySelector('button[type="submit"]');
    //         const originalText = submitBtn.textContent;
    //         submitBtn.textContent = 'Saving...';
    //         submitBtn.disabled = true;

    //         fetch(form.action, {
    //                 method: 'POST',
    //                 body: params,
    //                 headers: {
    //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //                     'X-Requested-With': 'XMLHttpRequest'
    //                 }
    //             })
    //             .then(response => {
    //                 if (!response.ok) {
    //                     return response.json().then(err => Promise.reject(err));
    //                 }
    //                 return response.json();
    //             })
    //             .then(data => {
    //                 console.log('Company creation response:', data);

    //                 if (data.success) {
    //                     closeCompanyModal();
    //                     selectCompany(data.data.name, data.data
    //                         .id); // ✅ Use 'name' instead of 'company_name'
    //                     form.reset();
    //                 } else {
    //                     const errorMessage = data.message || 'Failed to create company';
    //                     alert('Error: ' + errorMessage);
    //                 }
    //             })
    //             .catch(error => {
    //                 console.error('Error:', error);
    //                 const errorMessage = error?.errors?.name?.[0] || error.message || 'Unknown error';
    //                 alert('Error creating company: ' + errorMessage);
    //             })
    //             .finally(() => {
    //                 submitBtn.textContent = originalText;
    //                 submitBtn.disabled = false;
    //             });
    //     });

    //     // Close modal when clicking outside or pressing Escape
    //     modal.addEventListener('click', function(e) {
    //         if (e.target === modal) closeCompanyModal();
    //     });

    //     document.addEventListener('keydown', function(e) {
    //         if (e.key === 'Escape' && modal.classList.contains('show')) {
    //             closeCompanyModal();
    //         }
    //     });

    //     // Handle Enter key
    //     modalCompanyInput.addEventListener('keydown', function(e) {
    //         if (e.key === 'Enter') {
    //             e.preventDefault();
    //             form.dispatchEvent(new Event('submit'));
    //         }
    //     });
    // }


    function initSearchDropdown(inputId, dropdownId, searchUrl, type) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
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
                    console.log(`${type} Data Response:`, data);
                    currentData = (type === 'category' ? data.categories : data.companies) || [];

                    let html = '';

                    // Show existing items
                    currentData.forEach((item, index) => {
                        html +=
                            `<div class="dropdown-item" data-index="${index}">${item.name}</div>`;
                    });

                    // Add create new option if user typed something
                    if (value) {
                        html +=
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new: "${value}"</div>`;
                    }

                    // Show "No results found" if no items and no search value
                    if (currentData.length === 0 && !value) {
                        html += `<div class="dropdown-item no-results">No ${type} found</div>`;
                    }

                    dropdown.innerHTML = html;
                    dropdown.classList.add('show');
                    selectedIndex = -1;

                    // Add click listeners
                    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                        if (!item.classList.contains('no-results')) {
                            item.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                handleDropdownItemSelection(this, type,
                                    currentData);
                            });
                        }
                    });

                } catch (error) {
                    console.error('Search error:', error);
                    dropdown.classList.remove('show');
                    currentData = [];
                }
            }, 200);
        });

        // Arrow key navigation and Enter key handling
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.dropdown-item:not(.no-results)');

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
                    handleDropdownItemSelection(items[selectedIndex], type, currentData);
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

        // Handle dropdown item selection for company and category
        function handleDropdownItemSelection(item, type, currentData) {
            const dropdown = item.parentElement;
            dropdown.classList.remove('show');

            if (item.classList.contains('create-new')) {
                // Handle "Create new" option - just set the typed value
                const newValue = item.dataset.newValue;
                if (type === 'category') {
                    selectCategory(newValue, null); // null ID for new items
                } else if (type === 'company') {
                    selectCompany(newValue, null); // null ID for new items
                }
            } else if (item.dataset.index !== undefined) {
                // Handle existing item selection
                const index = parseInt(item.dataset.index);
                const selectedItem = currentData[index];

                if (type === 'category') {
                    selectCategory(selectedItem.name, selectedItem.id);
                } else if (type === 'company') {
                    selectCompany(selectedItem.name, selectedItem.id);
                }
            }
        }

        function updateHighlight(dropdown, items, selectedIndex) {
            items.forEach((item, index) => {
                item.style.backgroundColor = index === selectedIndex ? '#e9ecef' : '';
            });

            // Auto-scroll within dropdown
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
    // end search dropdown

    // Highlight record in HSN dropdown
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

    function selectItem(inputId, dropdownId, value) {
        document.getElementById(inputId).value = value;
        document.getElementById(dropdownId).classList.remove('show');

        // Clear the stored ID when selecting existing item from dropdown
        if (inputId === 'product_category') {
            selectedCategoryId = null;
            let hiddenField = document.getElementById('hidden_category_id');
            if (hiddenField) hiddenField.value = '';
        } else if (inputId === 'product_company') {
            selectedCompanyId = null;
            let hiddenField = document.getElementById('hidden_company_id');
            if (hiddenField) hiddenField.value = '';
        }
    }

    // preview image box
    function previewImage(input) {
        const file = input.files[0];
        const uploadMessage = document.getElementById('uploadMessage');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = function(e) {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                uploadMessage.style.display = 'none';
                imagePreview.style.display = 'block';
            };

            reader.readAsDataURL(file);
        }
    }

    // Handle drag and drop
    const dropArea = document.querySelector('.input-form');

    dropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#007bff';
        this.style.backgroundColor = '#f0f8ff';
    });

    dropArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f9f9f9';
    });

    dropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ccc';
        this.style.backgroundColor = '#f9f9f9';

        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            document.getElementById('fileInput').files = files;
            previewImage(document.getElementById('fileInput'));
        }
    });
    // end preview image box
</script>
