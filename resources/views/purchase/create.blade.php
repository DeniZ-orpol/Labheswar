@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            New Purchase
        </h2>
        <form action="{{ route('purchase.store') }}" method="POST" class="form-updated validate-form box rounded-md mt-5 p-5">
            @csrf <!-- CSRF token for security -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <!-- Name -->
                <div class="input-form col-span-8 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Purchase Party<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <select id="modal-form-6" name="party_name" class="form-select">
                        <option value="">Select Party...</option>
                        @foreach ($parties as $party)
                            <option value="{{ $party->id }} "> {{ $party->party_name }} </option>
                        @endforeach
                    </select>
                </div>

                <!-- Bill Date -->
                <div class="input-form col-span-4 mt-3">
                    <label for="bill_date" class="form-label w-full flex flex-col sm:flex-row">
                        Bill Date
                    </label>
                    <input id="bill_date" type="date" name="bill_date" class="form-control field-new">
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

            <div class="grid grid-cols-12 gap-2 grid-updated mt-12">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <button type="button" class="btn btn-primary shadow-md mr-2 btn-hover"> + Add Product</button>
                </div>
                <table class="display table intro-y col-span-12 bg-transparent w-full">
                    <thead>
                        <tr class="border-b fs-7 fw-bolder text-gray-700 uppercase text-center">
                            <th scope="col" class="required">Product</th>
                            <th scope="col" class="required">box</th>
                            <th scope="col" class="required">pcs</th>
                            <th scope="col" class="required">free</th>
                            <th scope="col" class="required">p.rate</th>
                            <th scope="col" class="text-end">dis(%)</th>
                            <th scope="col" class="text-end">dis(lungsum)</th>
                            <th scope="col" class="text-end">amount</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <tr class="text-center">
                            <!-- Product -->
                            <td class="table__item-desc w-1/4">
                                <select name="product[]" id="product"
                                    class="form-select text-sm w-full rounded-md py-2 px-3"
                                    onchange="loadProductDetails(this)">
                                    <option value="">Please Select product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-mrp="{{ $product->mrp ?? 0 }}"
                                            data-name="{{ $product->product_name }}"
                                            {{-- data-box-pcs="{{ $product->converse_box ?? 1 }}" --}}
                                            data-sgst="{{ $product->sgst ?? 0 }}" data-cgst="{{ $product->cgst1 ?? 0 }}"
                                            data-purchase-rate="{{ $product->purchase_rate ?? 0 }}"
                                            data-barcode="{{ $product->barcode ?? '' }}"
                                            data-category="{{ $product->category_id ?? '' }}"
                                            data-unit-type="{{ $product->unit_types ?? '' }}">{{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.01">
                            </td>

                            <!-- Discount (%) -->
                            <td>
                                <input type="number" name="discount_percent[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.01">
                            </td>

                            <!-- Discount (Lumpsum) -->
                            <td>
                                <input type="number" name="discount_lumpsum[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)" step="0.01">
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
            <div class="intro-y grid grid-cols-3 gap-5 mt-5 col-span-12">
                <!-- Item info column -->
                <div class="p-5">
                    <p><strong>Item:</strong> <span id="current-item">-</span></p>
                    <p><strong>MRP:</strong> <span id="current-mrp">0.00</span></p>
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
                <div class="p-5 row">
                    <div class="column font-medium text-lg">TOTAL INVOICE VALUE</div>
                    <div class="column font-medium text-lg" id="total-invoice-value">0.00</div>
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
    let productRowCounter = 0;

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

        // Set current date for display
        const todayDisplay = today.toLocaleDateString();
        document.getElementById('current-date').textContent = todayDisplay;

        // Initialize calculations
        calculateAllTotals();

        // Disable delete button if only one row exists initially
        const initialTableBody = document.getElementById('product-table-body');
        if (initialTableBody.children.length === 1) {
            const deleteButton = initialTableBody.querySelector('button[onclick*="removeRow"]');
            if (deleteButton) {
                deleteButton.disabled = true;
                deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
                deleteButton.classList.remove('hover:bg-red-100');
            }
        }

        // Disable arrow key functionality for number inputs
        function disableArrowKeys(event) {
            if (event.target.type === 'number') {
                if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
                    event.preventDefault();
                }
            }
        }

        // Add event listener to document to catch all number inputs
        document.addEventListener('keydown', disableArrowKeys);
    });

    function addProductRow() {
        const tableBody = document.getElementById('product-table-body');
        const existingRow = tableBody.querySelector('tr');
        const newRow = existingRow.cloneNode(true);

        // Clear all input values
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        // Update event handlers for new row
        const productSelect = newRow.querySelector('select[name="product[]"]');
        productSelect.setAttribute('onchange', 'loadProductDetails(this)');

        const inputs = newRow.querySelectorAll(
            'input[name="box[]"], input[name="pcs[]"], input[name="purchase_rate[]"], input[name="discount_percent[]"], input[name="discount_lumpsum[]"]'
        );
        inputs.forEach(input => {
            input.setAttribute('onchange', 'calculateRowAmount(this)');
        });

        tableBody.appendChild(newRow);
    }

    function removeRow(button) {
        const row = button.closest('tr');
        const tableBody = row.closest('tbody');

        if (tableBody.children.length > 1) {
            row.remove();
            calculateAllTotals();
        } else {
            alert('At least one product row is required.');
        }
    }

    function loadProductDetails(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const productName = selectedOption.getAttribute('data-name') || '-';
        const productMrp = selectedOption.getAttribute('data-mrp') || '0.00';
        const productPurchaseRate = selectedOption.getAttribute('data-purchase-rate') || '0.00';
        const sgstRate = selectedOption.getAttribute('data-sgst') || '0';
        const cgstRate = selectedOption.getAttribute('data-cgst') || '0';

        // Update current item details
        document.getElementById('current-item').textContent = productName;
        document.getElementById('current-mrp').textContent = parseFloat(productMrp).toFixed(2);

        // Auto-fill purchase rate if available
        const row = selectElement.closest('tr');
        const purchaseRateInput = row.querySelector('input[name="purchase_rate[]"]');
        if (purchaseRateInput && productPurchaseRate > 0) {
            purchaseRateInput.value = parseFloat(productPurchaseRate).toFixed(2);
        }

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
        const productSelect = row.querySelector('select[name="product[]"]');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const boxToPcs = parseFloat(selectedOption.getAttribute('data-box-pcs') || 1);
        const sgstRate = parseFloat(selectedOption.getAttribute('data-sgst') || 0);
        const cgstRate = parseFloat(selectedOption.getAttribute('data-cgst') || 0);

        // Calculate total pieces: (box * conversion ratio) + individual pcs
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
            const mrp = parseFloat(selectedOption.getAttribute('data-mrp') || 0);
            const boxToPcs = parseFloat(selectedOption.getAttribute('data-box-pcs') || 1);

            // Get quantities
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

        document.getElementById('total-invoice-value').textContent = totalFinalAmount.toFixed(2);

        // Update S.Rate (average rate per piece)
        const averageRate = totalQuantity > 0 ? (totalBaseAmount / totalQuantity) : 0;
        document.getElementById('current-srate').textContent = averageRate.toFixed(2);

        // Update hidden fields for purchase_receipt table
        document.getElementById('receipt-subtotal-hidden').value = totalBaseAmount.toFixed(2);
        document.getElementById('receipt-total-discount-hidden').value = totalDiscountAmount.toFixed(2);
        document.getElementById('receipt-total-gst-amount-hidden').value = totalGstAmount.toFixed(2);
        document.getElementById('receipt-total-amount-hidden').value = totalFinalAmount.toFixed(2);
    }
</script>
