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
            padding: 10px;
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
            Edit Product
        </h2>
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
            class="form-updated validate-form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="column">
                    {{-- <div class="grid grid-cols-12 gap-2 grid-updated"> --}}
                    <!-- barcode -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="barcode" class="form-label w-full flex flex-col sm:flex-row">
                            Barcode<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="barcode" type="text" name="product_barcode" class="form-control field-new"
                            value="{{ $product->barcode }}" required>

                    </div>

                    <!-- Name -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_name" class="form-label w-full flex flex-col sm:flex-row">
                            Name<span style="color: red;margin-left: 3px;"> *</span>
                        </label>
                        <input id="product_name" type="text" name="product_name" class="form-control field-new"
                            placeholder="Enter Product name" required maxlength="255" value="{{ $product->product_name }}">
                    </div>

                    <!-- search option -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="search_option" class="form-label w-full flex flex-col sm:flex-row">
                            Search Option
                        </label>
                        <input id="search_option" type="text" name="search_option" class="form-control field-new"
                            value="{{ $product->search_option }}">
                    </div>

                    <!-- Unit Types -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="unit_type" class="form-label w-full flex flex-col sm:flex-row">
                            Unit Type <p style="color: red; margin-left: 3px;">*</p>
                        </label>
                        <select id="unit_type" name="unit_type" class="form-control field-new" required>
                            <option value="" disabled
                                {{ old('unit_type', $product->unit_types ?? '') == '' ? 'selected' : '' }}>
                                Choose...
                            </option>
                            <option value="PCS"
                                {{ old('unit_type', $product->unit_types ?? '') == 'PCS' ? 'selected' : '' }}>
                                PCS
                            </option>
                            <option value="KG"
                                {{ old('unit_type', $product->unit_types ?? '') == 'KG' ? 'selected' : '' }}>
                                KG
                            </option>
                            <option value="LITER"
                                {{ old('unit_type', $product->unit_types ?? '') == 'LITER' ? 'selected' : '' }}>
                                LITER
                            </option>
                            <option value="BOX"
                                {{ old('unit_type', $product->unit_types ?? '') == 'BOX' ? 'selected' : '' }}>
                                BOX
                            </option>
                        </select>
                    </div>

                    <!-- Company -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_company" class="form-label w-full flex flex-col sm:flex-row">
                            Company
                        </label>
                        <input id="product_company" type="text" name="product_company" class="form-control field-new"
                            value="{{ $product->pCompany->name ?? '' }}">
                    </div> --}}
                    <!-- Company with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_company" class="form-label w-full flex flex-col sm:flex-row">
                            Company
                        </label>
                        <div class="search-dropdown">
                            <input id="product_company" type="text" name="product_company"
                                class="form-control field-new search-input" placeholder="Search or type company name"
                                autocomplete="off" value="{{ $product->pCompany->name ?? '' }}">
                            <div class="dropdown-list" id="companyDropdown"></div>

                            <!-- Hidden field for company ID -->
                            <input type="hidden" id="hidden_company_id" name="company_id"
                                value="{{ $product->company ?? '' }}">
                        </div>
                    </div>

                    <!-- category -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_category" class="form-label w-full flex flex-col sm:flex-row">
                            category
                        </label>
                        <input id="product_category" type="text" name="product_category" class="form-control field-new"
                            value="{{ $product->category->name ?? '' }}">
                    </div> --}}
                    <!-- Category with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_category" class="form-label w-full flex flex-col sm:flex-row">
                            Category
                        </label>
                        <div class="search-dropdown">
                            <input id="product_category" type="text" name="product_category"
                                class="form-control field-new search-input" placeholder="Search or type category"
                                autocomplete="off" value="{{ $product->category->name ?? '' }}">
                            <div class="dropdown-list" id="categoryDropdown"></div>

                            <!-- Hidden field for category ID -->
                            <input type="hidden" id="hidden_category_id" name="category_id"
                                value="{{ $product->category_id ?? '' }}">
                        </div>
                    </div>

                    <!-- HSN code -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="hsn_code" class="form-label w-full flex flex-col sm:flex-row">
                            HSN Code
                        </label>
                        <input id="hsn_code" type="text" name="hsn_code" class="form-control field-new"
                            value="{{ $product->hsnCode->hsn_code ?? '' }}">
                    </div> --}}
                    <!-- HSN Code with Searchable Dropdown -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="hsn_code" class="form-label w-full flex flex-col sm:flex-row">
                            HSN Code
                        </label>
                        <div class="search-dropdown">
                            @php
                                $hsnDisplayValue = '';
                                if ($product->hsnCode) {
                                    $hsnDisplayValue = $product->hsnCode->hsn_code;
                                    if ($product->hsnCode->gst) {
                                        $gstData = is_string($product->hsnCode->gst)
                                            ? json_decode($product->hsnCode->gst, true)
                                            : $product->hsnCode->gst;
                                        $gstValue = is_array($gstData) ? $gstData['gst'] ?? $gstData : $gstData;

                                        $sgst = $gstValue / 2;
                                        $cgst = $gstValue / 2;
                                        $igst = $gstValue;

                                        $hsnDisplayValue .= " (SGST: {$sgst}%, CGST: {$cgst}%, IGST: {$igst}%)";
                                    }
                                }
                            @endphp

                            <input id="hsn_code" type="text" name="hsn_code_display"
                                class="form-control field-new search-input" placeholder="Search or type HSN code"
                                autocomplete="off" value="{{ $hsnDisplayValue }}">
                            <div class="dropdown-list" id="hsnDropdown"></div>

                            <!-- Hidden fields for actual HSN data -->
                            <input type="hidden" id="hidden_hsn_code" name="hsn_code"
                                value="{{ $product->hsnCode->hsn_code ?? '' }}">
                            <input type="hidden" id="hidden_hsn_id" name="hsn_code_id"
                                value="{{ $product->hsn_code_id ?? '' }}">
                        </div>
                    </div>

                    <!-- sgst -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_sgst" class="form-label w-full flex flex-col sm:flex-row">
                            SGST
                        </label>
                        <input id="product_sgst" type="number" step="0.01" name="sgst" class="form-control field-new"
                            value="{{ $hsnGst['SGST'] ?? '' }}">
                    </div> --}}

                    <!-- CGST -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_cgst" class="form-label w-full flex flex-col sm:flex-row">
                            CGST
                        </label>
                        <input id="product_cgst" type="number" step="0.01" name="cgst"
                            class="form-control field-new" value="{{ $hsnGst['CGST'] ?? '' }}">
                    </div> --}}

                    <!-- IGST -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="product_igst" class="form-label w-full flex flex-col sm:flex-row">
                            IGST
                        </label>
                        <input id="product_igst" type="number" step="0.01" name="igst"
                            class="form-control field-new" value="{{ $hsnGst['IGST'] ?? '' }}">
                    </div> --}}

                    <!-- CESS -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_cess" class="form-label w-full flex flex-col sm:flex-row">
                            CESS
                        </label>
                        <input id="product_cess" type="number" step="0.01" name="cess"
                            class="form-control field-new" value="{{ $product->cess ?? null }}">
                        {{-- class="form-control field-new" value="{{ $hsnGst['CESS'] ?? $product->cess ?? '' }}"> --}}
                    </div>

                    <!-- MRP -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_mrp" class="form-label w-full flex flex-col sm:flex-row">
                            MRP
                        </label>
                        <input id="product_mrp" type="number" name="mrp" step="0.01"
                            class="form-control field-new" value="{{ $product->mrp }}">
                    </div>

                    <!-- Purchase rate -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_purchase_rate" class="form-label w-full flex flex-col sm:flex-row">
                            Purchase Rate
                        </label>
                        <input id="product_purchase_rate" type="number" step="0.0001" name="purchase_rate"
                            class="form-control field-new" value="{{ $product->purchase_rate }}">
                    </div>

                    <!-- Sale rate A -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_a" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate A
                        </label>
                        <input id="product_sale_rate_a" type="number" step="0.01" name="sale_rate_a"
                            class="form-control field-new" value="{{ $product->sale_rate_a }}">
                    </div>

                    <!-- Sale rate B -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_b" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate B
                        </label>
                        <input id="product_sale_rate_b" type="number" step="0.01" name="sale_rate_b"
                            class="form-control field-new" value="{{ $product->sale_rate_b }}">
                    </div>

                    <!-- Sale rate C -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sale_rate_c" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Rate C
                        </label>
                        <input id="product_sale_rate_c" type="number" step="0.01" name="sale_rate_c"
                            class="form-control field-new" value="{{ $product->sale_rate_c }}">
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
                                    class="form-control field-new" value="{{ $product->converse_carton }}">
                            </div>
                        </div>
                        <!-- Carton barcode -->
                        <div class="column">
                            <div class="input-form col-span-3">
                                <label for="carton_barcode" class="form-label w-full flex flex-col sm:flex-row">
                                    Carton Barcode
                                </label>
                                <input id="carton_barcode" type="number" name="carton_barcode"
                                    class="form-control field-new" value="{{ $product->carton_barcode }}">
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
                                    class="form-control field-new" value="{{ $product->converse_box }}">
                            </div>
                        </div>
                        <!-- BOX barcode -->
                        <div class="column">
                            <div class="input-form col-span-3">
                                <label for="box_barcode" class="form-label w-full flex flex-col sm:flex-row">
                                    Box Barcode
                                </label>
                                <input id="box_barcode" type="number" name="box_barcode" class="form-control field-new"
                                    value="{{ $product->box_barcode }}">
                            </div>
                        </div>
                    </div>

                    <!-- Converse pcs -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="converse_pcs" class="form-label w-full flex flex-col sm:flex-row">
                            Converse PCS
                        </label>
                        <input id="converse_pcs" type="number" name="converse_pcs" class="form-control field-new"
                            value="{{ $product->converse_pcs }}">
                    </div> --}}

                    <!-- Negative Billing -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="negative_billing" class="form-label w-full flex flex-col sm:flex-row">
                            Negative Billing
                        </label>
                        <select id="negative_billing" name="negative_billing" class="form-control field-new">
                            <option value="NO"
                                {{ old('negative_billing', $product->negative_billing ?? '') == 'NO' ? 'selected' : '' }}>
                                NO</option>
                            <option value="YES"
                                {{ old('negative_billing', $product->negative_billing ?? '') == 'YES' ? 'selected' : '' }}>
                                YES</option>
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
                        <input id="min_qty" type="number" name="min_qty" class="form-control field-new"
                            value="{{ $product->min_qty }}">
                    </div>

                    <!-- Reorder quantity -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="reorder_qty" class="form-label w-full flex flex-col sm:flex-row">
                            Reorder Quantity
                        </label>
                        <input id="reorder_qty" type="number" name="reorder_qty" class="form-control field-new"
                            value="{{ $product->reorder_qty }}">
                    </div>

                    <!-- Discount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="discount" class="form-label w-full flex flex-col sm:flex-row">
                            Discount
                        </label>
                        <select id="discount" name="discount" class="form-control field-new">
                            <option value="" disabled
                                {{ old('discount', $product->discount ?? '') == '' ? 'selected' : '' }}>
                                Choose...
                            </option>
                            <option value="applicable"
                                {{ old('discount', $product->discount ?? '') == 'applicable' ? 'selected' : '' }}>
                                Applicable
                            </option>
                            <option value="not_applicable"
                                {{ old('discount', $product->discount ?? '') == 'not_applicable' ? 'selected' : '' }}>
                                Not Applicable
                            </option>
                        </select>
                    </div>


                    <!-- Max Discount -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="max_discount" class="form-label w-full flex flex-col sm:flex-row">
                            Max Discount (%)
                        </label>
                        <input id="max_discount" type="number" step="0.0001" name="max_discount"
                            class="form-control field-new" value="{{ $product->max_discount }}">
                    </div>

                    <!-- Discount Scheme -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="discount_scheme" class="form-label w-full flex flex-col sm:flex-row">
                            Discount Scheme
                        </label>
                        <input id="discount_scheme" type="text" name="discount_scheme" class="form-control field-new"
                            value="{{ $product->discount_scheme }}">
                    </div>

                    <!-- Bonus Use -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="bonus_use" class="form-label w-full flex flex-col sm:flex-row">
                            Bonus Use
                        </label>
                        <select id="bonus_use" name="bonus_use" class="form-control field-new">
                            <option value="no"
                                {{ old('bonus_use', $product->bonus_use ?? '') == '0' ? 'selected' : '' }}>NO</option>
                            <option value="yes"
                                {{ old('bonus_use', $product->bonus_use ?? '') == '1 ' ? 'selected' : '' }}>YES</option>
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
                        <input id="decimal_btn" type="checkbox" name="decimal_btn" class="form-check-input mr-0 ml-3"
                            {{ $product->decimal_btn ? 'checked' : '' }}>
                    </div>

                    <!-- Sale online toggle -->
                    <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="sale_online" class="form-label w-full flex flex-col sm:flex-row">
                            Sale Online
                        </label>
                        <input id="sale_online" type="checkbox" name="sale_online" class="form-check-input mr-0 ml-3"
                            {{ $product->sale_online ? 'checked' : '' }}>
                    </div>

                    <!-- GST active toggle -->
                    {{-- <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="gst_active" class="form-label w-full flex flex-col sm:flex-row">
                            GST
                        </label>
                        <input id="gst_active" type="checkbox" name="gst_active" class="form-check-input mr-0 ml-3"
                            {{ $product->gst_active ? 'checked' : '' }}>
                    </div> --}}

                    <div class="input-form col-span-3 mt-3">
                        <label for="fileInput" class="form-label w-full flex flex-col sm:flex-row">
                            Product Image
                        </label>

                        <div
                            style="position: relative; border: 2px dashed #ccc; border-radius: 8px; padding: 50px 40px; text-align: center; background-color: #f9f9f9; cursor: pointer;">
                            <input name="product_image" type="file" id="fileInput" accept="image/*"
                                style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 1;"
                                onchange="previewImage(this)" />

                            <div id="uploadMessage" style="color: #666; font-size: 16px; pointer-events: none;">
                                Drop Product image file here or click to upload.
                            </div>

                            <!-- Preview box (shows initially if product has image) -->
                            <div id="imagePreview"
                                style="max-width: 300px; margin: 0 auto; {{ $product->image ? '' : 'display: none;' }}">
                                <img id="previewImg" src="{{ $product->image ? asset($product->image) : '' }}"
                                    style="width: 100%; height: auto; border-radius: 8px; margin-top: 10px;" />
                                <div style="margin-top: 10px; font-size: 14px; color: #666;">
                                    <span id="fileName">{{ $product->image ? basename($product->image) : '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>


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
    <div id="category-modal" class="modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Category</h2>
                </div>
                <!-- END: Modal Header -->

                {{-- <form action="{{ route('categories.modalstore') }}" id="category-form" method="POST"> --}}
                <form action="{{ route('categories.modalstore') }}" id="category-form" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12">
                            <label for="modal-category-name" class="form-label">Category Name</label>
                            <input id="modal-category-name" name="name" type="text" class="form-control"
                                placeholder="Enter category name" required>
                        </div>
                        <div class="col-span-12">
                            <label for="modal-category-image" class="form-label">Category Image (Optional)</label>
                            <input id="modal-category-image" name="image" type="file" class="form-control"
                                accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancel-category-modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" class="btn btn-primary w-20">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END: Category Modal -->

    <!-- BEGIN: Company Modal -->
    <div id="company-modal" class="modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- BEGIN: Modal Header -->
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Company</h2>
                </div>
                <!-- END: Modal Header -->

                {{-- <form action="{{ route('companies.modalstore') }}" id="company-form" method="POST"> --}}
                <form action="{{ route('company.modalstore') }}" id="company-form" method="POST">
                    @csrf
                    <!-- BEGIN: Modal Body -->
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Company Name</label>
                            <input id="name" name="name" type="text" class="form-control"
                                placeholder="Enter company name" required>
                        </div>
                    </div>
                    <!-- END: Modal Body -->

                    <!-- BEGIN: Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" id="cancel-company-modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" class="btn btn-primary w-20">Save</button>
                    </div>
                    <!-- END: Modal Footer -->
                </form>
            </div>
        </div>
    </div>
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

        // Pre-populate existing category and company IDs if available
        initializeExistingData();
    });

    // Global variables to store selected IDs
    let selectedCategoryId = null;
    let selectedCompanyId = null;

    function initializeExistingData() {
        // Debug: Log existing data
        console.log('Initializing existing data:', {
            categoryId: '{{ $product->category_id ?? 'null' }}',
            companyId: '{{ $product->company_id ?? 'null' }}',
            hsnId: '{{ $product->hsn_code_id ?? 'null' }}'
        });

        // The hidden fields are already in the HTML with values, so we just need to verify they exist

        // Verify category ID field exists and has value
        const categoryIdField = document.getElementById('hidden_category_id');
        if (categoryIdField) {
            console.log('Category ID field value:', categoryIdField.value);
        } else {
            console.warn('Category ID field not found');
        }

        // Verify company ID field exists and has value
        const companyIdField = document.getElementById('hidden_company_id');
        if (companyIdField) {
            console.log('Company ID field value:', companyIdField.value);
        } else {
            console.warn('Company ID field not found');
        }

        // Verify HSN ID field exists and has value
        const hsnIdField = document.getElementById('hidden_hsn_id');
        if (hsnIdField) {
            console.log('HSN ID field value:', hsnIdField.value);
        } else {
            console.warn('HSN ID field not found');
        }
    }


    // Function to select HSN code and auto-fill GST - NOW GLOBAL
    function selectHsnCode(hsnCode, gstData, hsnId = null) {
        const input = document.getElementById('hsn_code');
        const dropdown = document.getElementById('hsnDropdown');

        dropdown.classList.remove('show');

        let displayText = hsnCode;

        if (gstData) {
            try {
                // Handle both string and number GST values
                let gstValue;
                if (typeof gstData === 'string') {
                    try {
                        const parsedGst = JSON.parse(gstData);
                        gstValue = parsedGst.gst || parsedGst;
                    } catch (e) {
                        gstValue = parseFloat(gstData);
                    }
                } else {
                    gstValue = gstData;
                }

                const sgst = gstValue / 2 || 0;
                const cgst = gstValue / 2 || 0;
                const igst = gstValue || 0;

                displayText += ` (SGST: ${sgst}%, CGST: ${cgst}%, IGST: ${igst}%)`;

            } catch (e) {
                console.error('Error parsing GST data:', e);
            }
        }

        // Set display value to input field
        input.value = displayText;

        // Update hidden fields
        const hiddenHsnField = document.getElementById('hidden_hsn_code');
        const hiddenHsnIdField = document.getElementById('hidden_hsn_id');

        if (hiddenHsnField) {
            hiddenHsnField.value = hsnCode;
        }

        if (hiddenHsnIdField) {
            hiddenHsnIdField.value = hsnId || '';
        }

        console.log('HSN updated:', {
            hsnCode: hsnCode,
            hsnId: hsnId,
            displayText: displayText
        });
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
    // function initHsnDropdown() {
    //     const input = document.getElementById('hsn_code');
    //     const dropdown = document.getElementById('hsnDropdown');
    //     const searchUrl = '{{ route('hsn.search') }}';
    //     let timeout;
    //     let selectedIndex = -1;
    //     let currentHsnData = []; // Store current search results

    //     input.addEventListener('input', function() {
    //         clearTimeout(timeout);
    //         const value = this.value.trim();
    //         selectedIndex = -1;

    //         if (value.length < 1) {
    //             dropdown.classList.remove('show');
    //             currentHsnData = [];
    //             return;
    //         }

    //         timeout = setTimeout(async () => {
    //             try {
    //                 let url = `${searchUrl}?search=${value}`;

    //                 const branchSelect = document.getElementById('branch');
    //                 if (branchSelect && branchSelect.value) {
    //                     url += `&branch_id=${branchSelect.value}`;
    //                 }

    //                 const response = await fetch(url, {
    //                     headers: {
    //                         'X-CSRF-TOKEN': document.querySelector(
    //                             'meta[name="csrf-token"]').content
    //                     }
    //                 });

    //                 const data = await response.json();
    //                 currentHsnData = data.hsn_codes || [];

    //                 let html = '';

    //                 // Show existing HSN codes first
    //                 currentHsnData.forEach((item, index) => {
    //                     // Show HSN code with GST info for better identification
    //                     console.log(item);
    //                     const gstInfo = item.gst ?
    //                         // ` (GST: ${typeof item.gst === 'string' ? JSON.parse(item.gst).gst || 'N/A' : item.gst.gst || 'N/A'}%)` :
    //                         ` (GST: ${item.gst}%)` :
    //                         '';
    //                     html +=
    //                         `<div class="dropdown-item" data-index="${index}">${item.hsn_code}${gstInfo}</div>`;
    //                 });

    //                 // ALWAYS add "Create new" option - regardless of existing entries
    //                 if (value) { // Only show if user has typed something
    //                     html +=
    //                         `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new: "${value}" with custom GST</div>`;
    //                 }

    //                 dropdown.innerHTML = html;
    //                 dropdown.classList.add('show');
    //                 selectedIndex = -1;

    //                 // Add click listeners to dropdown items
    //                 dropdown.querySelectorAll('.dropdown-item').forEach(item => {
    //                     item.addEventListener('mousedown', function(e) {
    //                         e.preventDefault();
    //                         if (this.dataset.newValue) {
    //                             // Creating new HSN code
    //                             openHsnModal(this.dataset.newValue);
    //                         } else if (this.dataset.index !== undefined) {
    //                             // Selecting existing HSN code
    //                             const index = parseInt(this.dataset.index);
    //                             selectHsnCode(currentHsnData[index].hsn_code,
    //                                 currentHsnData[index].gst, currentHsnData[
    //                                     index].id);
    //                         }
    //                     });
    //                 });

    //             } catch (error) {
    //                 console.error('HSN search error:', error);
    //                 dropdown.classList.remove('show');
    //                 currentHsnData = [];
    //             }
    //         }, 200);
    //     });

    //     // Arrow key navigation
    //     input.addEventListener('keydown', function(e) {
    //         const items = dropdown.querySelectorAll('.dropdown-item');

    //         if (items.length === 0) return;

    //         if (e.key === 'ArrowDown') {
    //             e.preventDefault();
    //             selectedIndex = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
    //             updateHighlight(dropdown, items, selectedIndex);
    //         } else if (e.key === 'ArrowUp') {
    //             e.preventDefault();
    //             selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
    //             updateHighlight(dropdown, items, selectedIndex);
    //         } else if (e.key === 'Enter') {
    //             e.preventDefault();
    //             if (selectedIndex >= 0 && items[selectedIndex]) {
    //                 handleHsnDropdownItemClick(items[selectedIndex]);
    //             }
    //         } else if (e.key === 'Escape') {
    //             dropdown.classList.remove('show');
    //             selectedIndex = -1;
    //         }
    //     });

    //     // Close dropdown when clicking outside
    //     document.addEventListener('click', function(e) {
    //         if (!input.contains(e.target) && !dropdown.contains(e.target)) {
    //             dropdown.classList.remove('show');
    //             selectedIndex = -1;
    //         }
    //     });

    //     // Handle dropdown item selection
    //     function handleHsnDropdownItemClick(item) {
    //         dropdown.classList.remove('show');

    //         if (item.dataset.newValue) {
    //             openHsnModal(item.dataset.newValue);
    //         } else if (item.dataset.index !== undefined) {
    //             const index = parseInt(item.dataset.index);
    //             selectHsnCode(currentHsnData[index].hsn_code, currentHsnData[index].gst, currentHsnData[index].id);
    //         }
    //     }
    // }
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
    // Update selectCategory function to work with existing hidden field
    function selectCategory(categoryName, categoryId = null) {
        const input = document.getElementById('product_category');
        const dropdown = document.getElementById('categoryDropdown');

        dropdown.classList.remove('show');
        input.value = categoryName;

        // Update existing hidden field
        const hiddenCategoryIdField = document.getElementById('hidden_category_id');
        if (hiddenCategoryIdField) {
            hiddenCategoryIdField.value = categoryId || '';
        } else {
            console.warn('Hidden category ID field not found');
        }

        console.log('Category updated:', {
            categoryName: categoryName,
            categoryId: categoryId,
            hiddenFieldValue: hiddenCategoryIdField?.value
        });
    }

    // Function to select company and store ID
    function selectCompany(companyName, companyId = null) {
        const input = document.getElementById('product_company');
        const dropdown = document.getElementById('companyDropdown');

        dropdown.classList.remove('show');
        input.value = companyName;

        // Update existing hidden field
        const hiddenCompanyIdField = document.getElementById('hidden_company_id');
        if (hiddenCompanyIdField) {
            hiddenCompanyIdField.value = companyId || '';
        } else {
            console.warn('Hidden company ID field not found');
        }

        console.log('Company updated:', {
            companyName: companyName,
            companyId: companyId,
            hiddenFieldValue: hiddenCompanyIdField?.value
        });
    }

    // Functions to open modals
    function openCategoryModal(categoryName) {
        showModal('category-modal', 'modal-category-name', categoryName, 'modal-category-name');
    }

    function showModal(modalId, inputId, inputValue = '', focusId = '') {
        const modal = document.getElementById(modalId);
        const input = document.getElementById(inputId);

        if (!modal) {
            console.error(`Modal with ID '${modalId}' not found.`);
            return;
        }

        // Set input value if provided
        if (input) {
            input.value = inputValue || '';
        }

        // Show the modal
        modal.classList.add('modal-open');
        modal.style.display = 'flex';

        // Focus input after short delay
        if (focusId) {
            setTimeout(() => {
                const focusInput = document.getElementById(focusId);
                if (focusInput) focusInput.focus();
            }, 100);
        }

        // Cancel button: close modal (add listener only once)
        const cancelBtn = modal.querySelector(
            '.btn-outline-secondary, .modal-cancel, #cancel-category-modal'
        );
        if (cancelBtn && !cancelBtn.hasAttribute('data-close-bound')) {
            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('modal-open');
                modal.style.display = 'none';
            });
            cancelBtn.setAttribute('data-close-bound', 'true');
        }

        // Click outside modal to close (add listener only once)
        if (!modal.hasAttribute('data-overlay-close-bound')) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('modal-open');
                    modal.style.display = 'none';
                }
            });
            modal.setAttribute('data-overlay-close-bound', 'true');
        }
    }




    function openCompanyModal(companyName) {
        showModal('company-modal', 'name', companyName, 'name');
    }

    // Generic function to show modal
    function showModal(modalId, inputId, value, focusId) {
        const modal = document.getElementById(modalId);
        const input = document.getElementById(inputId);
        const focusInput = document.getElementById(focusId);

        // Set value and show modal
        input.value = value;
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.marginTop = '50px';
        modal.style.marginLeft = '0';
        modal.classList.add('show');
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        // Focus on input
        setTimeout(() => {
            focusInput.focus();
        }, 100);
    }

    // Functions to close modals
    function closeCategoryModal() {
        closeModal('category-modal');
    }

    function closeCompanyModal() {
        closeModal('company-modal');
    }

    // Generic function to close modal
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    }

    // Initialize Category Modal
    function initCategoryModal() {
        const modal = document.getElementById('category-modal');
        const cancelBtn = document.getElementById('cancel-category-modal');
        const form = modal.querySelector('form');
        const modalCategoryInput = document.getElementById('modal-category-name');
        const modalImageInput = document.getElementById('modal-category-image'); // <-- add image input
        const submitBtn = form.querySelector('button[type="submit"]');

        cancelBtn.addEventListener('click', closeCategoryModal);

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const categoryName = modalCategoryInput.value.trim();

            if (!categoryName) {
                alert('Category name is required');
                return;
            }

            const formData = new FormData();
            formData.append('name', categoryName);

            if (modalImageInput?.files[0]) {
                formData.append('image', modalImageInput.files[0]); // append image
            }

            const branchSelect = document.getElementById('branch');
            if (branchSelect?.value) {
                formData.append('branch_id', branchSelect.value);
            }

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
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Category creation response:', data);

                    if (data.success && data.data?.name && data.data?.id) {
                        closeCategoryModal();
                        selectCategory(data.data.name, data.data.id);
                        form.reset();
                        modalImageInput.value = '';
                    } else if (data.success) {
                        // Success true, but data is malformed
                        console.warn('Success response but incomplete data:', data);
                        closeCategoryModal();
                        form.reset();
                        modalImageInput.value = '';
                        // Don’t show any error popup in this case
                    } else {
                        alert('Error: ' + (data.message || 'Failed to create category'));
                    }
                })

                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error?.errors?.name?.[0] || error.message || 'Unknown error';
                    alert('Error creating category: ' + errorMessage);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Close modal when clicking outside or pressing Escape
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeCategoryModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeCategoryModal();
            }
        });

        // Handle Enter key
        modalCategoryInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    }



    // Initialize Company Modal
    function initCompanyModal() {
        const modal = document.getElementById('company-modal');
        const cancelBtn = document.getElementById('cancel-company-modal');
        const form = modal.querySelector('form');
        const modalCompanyInput = document.getElementById('name');

        cancelBtn.addEventListener('click', closeCompanyModal);

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const companyName = modalCompanyInput.value.trim();

            if (!companyName) {
                alert('Company name is required');
                return;
            }

            const params = new URLSearchParams();
            params.append('name', companyName); // ✅ Corrected field name

            const branchSelect = document.getElementById('branch');
            if (branchSelect?.value) {
                params.append('branch_id', branchSelect.value);
            }

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
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Company creation response:', data);

                    if (data.success && data.data?.name && data.data?.id) {
                        closeCompanyModal();
                        selectCompany(data.data.name, data.data.id); // Use 'name' field
                        form.reset();
                    } else {
                        const errorMessage = data.message || 'Failed to create company';
                        alert('Error: ' + errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error?.errors?.name?.[0] || error.message || 'Unknown error';
                    alert('Error creating company: ' + errorMessage);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Close modal when clicking outside or pressing Escape
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeCompanyModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeCompanyModal();
            }
        });

        // Handle Enter key
        modalCompanyInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    }


    // search dropdown
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

                    let items = [];
                    if (type === 'category') {
                        currentData = data.categories || [];
                        items = currentData.map(item => typeof item === 'object' ? item
                            .category_name || item.name : item);
                    } else if (type === 'company') {
                        currentData = data.companies || [];
                        items = currentData.map(item => typeof item === 'object' ? item
                            .company_name || item.name : item);
                    } else {
                        items = data.items || data.companies || data.categories || [];
                    }

                    let html = '';

                    // Show existing items with IDs
                    if (type === 'category' || type === 'company') {
                        currentData.forEach((item, index) => {
                            const itemName = typeof item === 'object' ?
                                (item.category_name || item.company_name || item.name) :
                                item;
                            html +=
                                `<div class="dropdown-item" data-index="${index}">${itemName}</div>`;
                        });
                    } else {
                        items.forEach(item => {
                            html +=
                                `<div class="dropdown-item" onclick="selectItem('${inputId}', '${dropdownId}', '${item}')">${item}</div>`;
                        });
                    }

                    // Always add create new option
                    let createNewText = '';
                    if (type === 'category') {
                        createNewText =
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new category: "${value}"</div>`;
                    } else if (type === 'company') {
                        createNewText =
                            `<div class="dropdown-item create-new" data-new-value="${value}">+ Create new company: "${value}"</div>`;
                    } else {
                        createNewText =
                            `<div class="dropdown-item create-new" onclick="selectItem('${inputId}', '${dropdownId}', '${value}')">Create new: "${value}"</div>`;
                    }

                    html += createNewText;

                    dropdown.innerHTML = html;
                    dropdown.classList.add('show');
                    selectedIndex = -1;

                    // Add click listeners for category and company dropdowns
                    if (type === 'category' || type === 'company') {
                        dropdown.querySelectorAll('.dropdown-item:not(.create-new)').forEach(
                            item => {
                                item.addEventListener('mousedown', function(e) {
                                    e.preventDefault();
                                    const index = parseInt(this.dataset.index);
                                    const selectedItem = currentData[index];

                                    console.log('Selecting from dropdown:', {
                                        type: type,
                                        index: index,
                                        selectedItem: selectedItem
                                    });

                                    if (type === 'category') {
                                        const categoryName = typeof selectedItem ===
                                            'object' ?
                                            (selectedItem.category_name || selectedItem
                                                .name) : selectedItem;
                                        const categoryId = typeof selectedItem ===
                                            'object' ? selectedItem.id : null;
                                        selectCategory(categoryName, categoryId);
                                    } else if (type === 'company') {
                                        const companyName = typeof selectedItem ===
                                            'object' ?
                                            (selectedItem.company_name || selectedItem
                                                .name) : selectedItem;
                                        const companyId = typeof selectedItem ===
                                            'object' ? selectedItem.id : null;
                                        selectCompany(companyName, companyId);
                                    }
                                });
                            });

                        // Add click listener for "Create new" option
                        dropdown.querySelector('.create-new')?.addEventListener('mousedown',
                            function(e) {
                                e.preventDefault();
                                if (type === 'category') {
                                    openCategoryModal(value);
                                } else if (type === 'company') {
                                    openCompanyModal(value);
                                }
                            });
                    }

                } catch (error) {
                    console.error('Search error:', error);
                    dropdown.classList.remove('show');
                    currentData = [];
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
                    if (type === 'category' || type === 'company') {
                        handleDropdownItemClick(items[selectedIndex], type, value);
                    } else {
                        items[selectedIndex].click();
                    }
                }
            } else if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                selectedIndex = -1;
            }
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                selectedIndex = -1;
            }
        });

        function handleDropdownItemClick(item, dropdownType, searchValue) {
            if (item.classList.contains('create-new')) {
                if (dropdownType === 'category') {
                    openCategoryModal(searchValue);
                } else if (dropdownType === 'company') {
                    openCompanyModal(searchValue);
                }
            } else if (item.dataset.index !== undefined) {
                const index = parseInt(item.dataset.index);
                const selectedItem = currentData[index];

                if (dropdownType === 'category') {
                    const categoryName = typeof selectedItem === 'object' ?
                        (selectedItem.category_name || selectedItem.name) : selectedItem;
                    const categoryId = typeof selectedItem === 'object' ? selectedItem.id : null;
                    selectCategory(categoryName, categoryId);
                } else if (dropdownType === 'company') {
                    const companyName = typeof selectedItem === 'object' ?
                        (selectedItem.company_name || selectedItem.name) : selectedItem;
                    const companyId = typeof selectedItem === 'object' ? selectedItem.id : null;
                    selectCompany(companyName, companyId);
                }
            }
        }
    }
    // end search dropdown

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
    }
    // end search dropdown

    function previewImage(input) {
        const preview = document.getElementById('previewImg');
        const previewBox = document.getElementById('imagePreview');
        const fileNameText = document.getElementById('fileName');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewBox.style.display = 'block';
                fileNameText.innerText = input.files[0].name;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
