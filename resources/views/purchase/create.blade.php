@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            New Purchase
        </h2>
        <form action="{{ route('purchase.store') }}" method="POST" class="form-updated validate-form">
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
                    <select id="gst" name="gst" class="form-control field-new">
                        <option value="on" selected>ON</option>
                        <option value="off">OFF</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 grid-updated mt-12">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <button type="button" class="btn btn-primary shadow-md mr-2 btn-hover"> + Add Product</button>
                </div>
                <table class="display table intro-y col-span-12 bg-transparent table-striped w-full">
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
                    <tbody>
                        <tr class="text-center">
                            <!-- Product -->
                            <td class="table__item-desc w-1/4">
                                <select name="product[]" id="product"
                                    class="form-select text-sm w-full rounded-md py-2 px-3">
                                    <option value="">Please Select product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
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
                                <input type="number" name="purchase_rate[]" class="form-control field-new" maxlength="255"
                                    onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Discount (%) -->
                            <td>
                                <input type="number" name="discount_percent[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Discount (Lumpsum) -->
                            <td>
                                <input type="number" name="discount_lumpsum[]" class="form-control field-new text-end"
                                    maxlength="255" onchange="calculateRowAmount(this)">
                            </td>

                            <!-- Amount -->
                            <td>
                                <input type="number" name="amount[]" class="form-control field-new text-end"
                                    maxlength="255" readonly>
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
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
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
    });

    function addProductRow() {
        const tableBody = document.querySelector('.table tbody');
        const existingRow = tableBody.querySelector('tr');
        const newRow = existingRow.cloneNode(true);

        // Clear all input values
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        tableBody.appendChild(newRow);
    }

    function removeRow(button) {
        const row = button.closest('tr');
        const tableBody = row.closest('tbody');

        if (tableBody.children.length > 1) {
            row.remove();
        } else {
            alert('At least one product row is required.');
        }
    }

    function calculateRowAmount(input) {
        const row = input.closest('tr');
        const box = parseFloat(row.querySelector('input[name="box[]"]')?.value || 0);
        const pcs = parseFloat(row.querySelector('input[name="pcs[]"]')?.value || 0);
        const purchaseRate = parseFloat(row.querySelector('input[name="purchase_rate[]"]')?.value || 0);
        const discountPercent = parseFloat(row.querySelector('input[name="discount_percent[]"]')?.value || 0);
        const discountLumpsum = parseFloat(row.querySelector('input[name="discount_lumpsum[]"]')?.value || 0);

        let baseAmount = (box + pcs) * purchaseRate;

        if (discountPercent > 0) {
            baseAmount = baseAmount - (baseAmount * (discountPercent / 100));
        }

        if (discountLumpsum > 0) {
            baseAmount = baseAmount - discountLumpsum;
        }

        const gstSelect = document.querySelector('select[name="gst"]');
        const gstRate = (gstSelect && gstSelect.value === 'on') ? 18 : 0;
        const finalAmount = baseAmount + (baseAmount * (gstRate / 100));

        const amountInput = row.querySelector('input[name="amount[]"]');
        if (amountInput) {
            amountInput.value = finalAmount.toFixed(2);
        }
    }
</script>
