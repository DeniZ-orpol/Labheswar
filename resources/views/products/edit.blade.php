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
    @php
        $isSuperAdmin = strtolower($role->role_name) === 'super admin';
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Product
        </h2>
        <form
            action="{{ $isSuperAdmin
                ? route('products.update', ['id' => $product->id, 'branch' => $branch->id])
                : route('products.update', $product->id) }}"
            method="POST" enctype="multipart/form-data" class="form-updated validate-form">
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
                            <input id="hsn_code" type="text" name="hsn_code" class="form-control field-new search-input"
                                placeholder="Search or type HSN code" autocomplete="off" 
                                value="{{ $product->hsnCode->hsn_code ?? '' }}">
                            <div class="dropdown-list" id="hsnDropdown"></div>
                        </div>
                    </div>

                    <!-- sgst -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_sgst" class="form-label w-full flex flex-col sm:flex-row">
                            SGST
                        </label>
                        <input id="product_sgst" type="number" step="0.01" name="sgst" class="form-control field-new"
                            value="{{ $product->sgst }}">
                    </div>

                    <!-- CGST -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_cgst" class="form-label w-full flex flex-col sm:flex-row">
                            CGST
                        </label>
                        <input id="product_cgst" type="number" step="0.01" name="cgst"
                            class="form-control field-new" value="{{ $product->cgst1 }}">
                    </div>

                    <!-- IGST -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_igst" class="form-label w-full flex flex-col sm:flex-row">
                            IGST
                        </label>
                        <input id="product_igst" type="number" step="0.01" name="igst"
                            class="form-control field-new" value="{{ $product->cgst2 }}">
                    </div>

                    <!-- CESS -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="product_cess" class="form-label w-full flex flex-col sm:flex-row">
                            CESS
                        </label>
                        <input id="product_cess" type="number" step="0.01" name="cess"
                            class="form-control field-new" value="{{ $product->cess }}">
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
                        <input id="product_purchase_rate" type="number" step="0.01" name="purchase_rate"
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

                    <!-- Converse carton -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="converse_carton" class="form-label w-full flex flex-col sm:flex-row">
                            Converse Carton
                        </label>
                        <input id="converse_carton" type="number" name="converse_carton" class="form-control field-new"
                            value="{{ $product->converse_carton }}">
                    </div>

                    <!-- Converse BOX -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="converse_box" class="form-label w-full flex flex-col sm:flex-row">
                            Converse Box
                        </label>
                        <input id="converse_box" type="number" name="converse_boc" class="form-control field-new"
                            value="{{ $product->converse_box }}">
                    </div>

                    <!-- Converse pcs -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="converse_pcs" class="form-label w-full flex flex-col sm:flex-row">
                            Converse PCS
                        </label>
                        <input id="converse_pcs" type="number" name="converse_pcs" class="form-control field-new"
                            value="{{ $product->converse_pcs }}">
                    </div>

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
                        <input id="max_discount" type="number" name="max_discount" class="form-control field-new"
                            value="{{ $product->max_discount }}">
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
                    <div class="input-form col-span-3 mt-3 form-check form-switch w-full sm:ml-auto">
                        <label for="gst_active" class="form-label w-full flex flex-col sm:flex-row">
                            GST
                        </label>
                        <input id="gst_active" type="checkbox" name="gst_active" class="form-check-input mr-0 ml-3"
                            {{ $product->gst_active ? 'checked' : '' }}>
                    </div>

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
                                <img id="previewImg"
                                    src="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
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
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Company dropdown (existing)
        initSearchDropdown('product_company', 'companyDropdown', '{{ route('companies.search') }}');

        // Category dropdown
        initSearchDropdown('product_category', 'categoryDropdown', '{{ route('categories.search') }}');

        // HSN Code dropdown
        initSearchDropdown('hsn_code', 'hsnDropdown', '{{ route('hsn.search') }}');
    });


    // search dropdown
    function initSearchDropdown(inputId, dropdownId, searchUrl) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        let timeout;
        let selectedIndex = -1;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const value = this.value.trim();
            selectedIndex = -1;

            if (value.length < 1) {
                dropdown.classList.remove('show');
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    let url = `${searchUrl}?search=${value}`;

                    // Add branch_id if Super Admin
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
                    const items = data.items || data.companies || data.categories || data
                        .hsn_codes || [];

                    let html = '';
                    items.forEach(item => {
                        html +=
                            `<div class="dropdown-item" onclick="selectItem('${inputId}', '${dropdownId}', '${item}')">${item}</div>`;
                    });

                    // Add create new option
                    if (!items.includes(value)) {
                        html +=
                            `<div class="dropdown-item create-new" onclick="selectItem('${inputId}', '${dropdownId}', '${value}')">Create new: "${value}"</div>`;
                    }

                    dropdown.innerHTML = html;
                    dropdown.classList.add('show');
                    selectedIndex = -1;

                } catch (error) {
                    dropdown.classList.remove('show');
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
                    items[selectedIndex].click();
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

            // Check if item is above visible area
            if (itemTop < dropdownScrollTop) {
                dropdown.scrollTop = itemTop;
            }
            // Check if item is below visible area
            else if (itemTop + itemHeight > dropdownScrollTop + dropdownHeight) {
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
