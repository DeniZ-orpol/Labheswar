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
            box-sizing: border-box;
        }
    </style>
@endpush
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Purchase Party
        </h2>
        <form action="{{ route('purchase.party.store') }}" method="POST" class="form-updated">
            @csrf
            <div class="row">
                <div class="column p-5">
                    <!-- Select ledger group -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_group" class="form-label w-full flex flex-col sm:flex-row">Ledger Group<span
                                style="color: red;margin-left: 3px;">
                                *</span></label>
                        <select id="ledger_group" name="ledger_group" class="form-control field-new">
                            <option value="" selected>Select Ledger Group...</option>
                            <option value="SUNDRY DEBTORS">SUNDRY DEBTORS</option>
                            <option value="SUNDRY DEBTORS (E-COMMERCE)">SUNDRY DEBTORS (E-COMMERCE)</option>
                            <option value="SUNDRY DEBTORS (FIELD STAFF)">SUNDRY DEBTORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS">SUNDRY CREDITORS</option>
                            <option value="SUNDRY CREDITORS (E-COMMERCE)">SUNDRY CREDITORS (E-COMMERCE)</option>
                            <option value="SUNDRY CREDITORS (EXPENSES PAYABLE)">SUNDRY CREDITORS (EXPENSES PAYABLE)</option>
                            <option value="SUNDRY CREDITORS (FIELD STAFF)">SUNDRY CREDITORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS (MANUFACTURERS)">SUNDRY CREDITORS (MANUFACTURERS)</option>
                            <option value="SUNDRY CREDITORS (SUPPLIERS)">SUNDRY CREDITORS (SUPPLIERS)</option>
                        </select>
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Party Name<span style="color: red;margin-left: 3px;">
                                *</span></label>
                        <input type="text" name="party_name" class="form-control field-new" required>
                    </div>
                    <!-- GST No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_number" class="form-label w-full flex flex-col sm:flex-row">GST No</label>
                        <input id="gst_number" type="text" name="gst_number" class="form-control field-new">
                    </div>
                    <!-- State -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="state" class="form-label w-full flex flex-col sm:flex-row">State</label>
                        <input id="state" type="text" name="state" class="form-control field-new">
                    </div>
                    <!-- Pan No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pan_no" class="form-label w-full flex flex-col sm:flex-row">Pan No</label>
                        <input id="pan_no" type="text" name="pan_no" class="form-control field-new">
                    </div>
                    {{-- <div class="input-form col-span-3 mt-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control field-new">
                        </div> --}}
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Gst NO.</label>
                        <input type="text" name="gst_number" class="form-control field-new">
                    </div> --}}
                    
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control field-new">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Station</label>
                        <input type="text" name="station" class="form-control field-new">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Pin Code</label>
                        <input type="text" name="pincode" class="form-control field-new"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control field-new"></textarea>
                    </div>
                </div>
                <div class="column p-5">
                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="balancing_method" class="form-label w-full flex flex-col sm:flex-row">Balancing
                            Method</label>
                        <select id="balancing_method" name="balancing_method" class="form-control field-new">
                            <option value="Bill By Bill" selected>Bill By Bill</option>
                            <option value="Fifo Base">Fifo Base</option>
                            <option value="On Account">On Account</option>
                        </select>
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="acc_no" class="form-control field-new">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control field-new">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Mobile NO.</label>
                        <input type="text" name="mobile_no"
                            class="form-control field-new"oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                    </div>
                    <!-- GST Heading -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="gst_heading" class="form-label w-full flex flex-col sm:flex-row">GST Heading</label>
                        <input id="gst_heading" type="text" name="gst_heading" class="form-control field-new">
                    </div> --}}
                    <!-- Mail To -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="mail_to" class="form-label w-full flex flex-col sm:flex-row">Mail To</label>
                        <input id="mail_to" type="text" name="mail_to" class="form-control field-new">
                    </div>
                    <!-- Contact person -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person" class="form-label w-full flex flex-col sm:flex-row">Contact
                            Person</label>
                        <input id="contact_person" type="text" name="contact_person" class="form-control field-new">
                    </div>
                    <!-- Designation -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="designation" class="form-label w-full flex flex-col sm:flex-row">Designation</label>
                        <input id="designation" type="text" name="designation" class="form-control field-new">
                    </div> --}}
                    <!-- Note -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="note" class="form-label w-full flex flex-col sm:flex-row">Note</label>
                        <textarea name="note" id="note" class="form-control field-new"></textarea>
                    </div> --}}
                    <!-- Ledger Category -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_category" class="form-label w-full flex flex-col sm:flex-row">
                            Ledger Category
                        </label>
                        <select id="ledger_category" name="ledger_category" class="form-control field-new">
                            <option value="" selected>Select Ledger Category...</option>
                            <option value="Retailer">Retailer</option>
                            <option value="Stock List">Stock List</option>
                            <option value="Distributor">Distributor</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <!-- Country -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="country" class="form-label w-full flex flex-col sm:flex-row">Country</label>
                        <input id="country" type="text" name="country" class="form-control field-new">
                    </div>
                </div>
            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back </a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Save</button>
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

@push('scripts')
    <script>
        const gstStateCodes = {
            "01": "Jammu & Kashmir",
            "02": "Himachal Pradesh",
            "03": "Punjab",
            "04": "Chandigarh",
            "05": "Uttarakhand",
            "06": "Haryana",
            "07": "Delhi",
            "08": "Rajasthan",
            "09": "Uttar Pradesh",
            "10": "Bihar",
            "11": "Sikkim",
            "12": "Arunachal Pradesh",
            "13": "Nagaland",
            "14": "Manipur",
            "15": "Mizoram",
            "16": "Tripura",
            "17": "Meghalaya",
            "18": "Assam",
            "19": "West Bengal",
            "20": "Jharkhand",
            "21": "Odisha",
            "22": "Chhattisgarh",
            "23": "Madhya Pradesh",
            "24": "Gujarat",
            "25": "Daman and Diu",
            "26": "Dadra and Nagar Haveli",
            "27": "Maharashtra",
            "28": "Andhra Pradesh (Old)",
            "29": "Karnataka",
            "30": "Goa",
            "31": "Lakshadweep",
            "32": "Kerala",
            "33": "Tamil Nadu",
            "34": "Puducherry",
            "35": "Andaman and Nicobar Islands",
            "36": "Telangana",
            "37": "Andhra Pradesh"
        };

        document.getElementById('gst_number').addEventListener('blur', function() {
            const gstin = this.value.trim().toUpperCase();

            // Basic format check
            if (gstin.length >= 15) {
                const stateCode = gstin.slice(0, 2);
                const pan = gstin.slice(2, 12);

                const stateName = gstStateCodes[stateCode] || '';

                // Autofill fields
                document.getElementById('state').value = `${stateCode}-${stateName}`.toUpperCase();
                document.getElementById('pan_no').value = pan;
                document.getElementById('country').value = "India";
            }
        });

        // Autofill Mail To from Party Name
        document.querySelector('input[name="party_name"]').addEventListener('blur', function() {
            const partyName = this.value.trim();
            document.getElementById('mail_to').value = partyName;
        });

        // Setup enter navigation for purchaser party form
        function setupEnterNavigation() {
            let currentFieldIndex = 0;

            const formFields = [
                { selector: '#ledger_group', type: 'select' },
                { selector: 'input[name="party_name"]', type: 'input' },
                { selector: '#gst_number', type: 'input' },
                { selector: '#state', type: 'input' },
                { selector: '#pan_no', type: 'input' },
                { selector: 'input[name="email"]', type: 'input' },
                { selector: 'input[name="station"]', type: 'input' },
                { selector: 'input[name="pincode"]', type: 'input' },
                { selector: '#address', type: 'textarea' },
                { selector: '#balancing_method', type: 'select' },
                { selector: 'input[name="acc_no"]', type: 'input' },
                { selector: 'input[name="ifsc_code"]', type: 'input' },
                { selector: 'input[name="mobile_no"]', type: 'input' },
                { selector: '#mail_to', type: 'input' },
                { selector: '#contact_person', type: 'input' },
                { selector: '#ledger_category', type: 'select' },
                { selector: '#country', type: 'input' }
            ];

            function focusField(selector) {
                const element = document.querySelector(selector);
                if (element) {
                    element.focus();
                    if (element.tagName === 'SELECT') {
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
                        currentFieldIndex = fieldIndex + 1;
                        focusField(formFields[currentFieldIndex].selector);
                    } else {
                        const submitButton = document.querySelector('button[type="submit"]');
                        if (submitButton) {
                            submitButton.focus();
                        }
                    }
                }
            }

            formFields.forEach((field, index) => {
                const element = document.querySelector(field.selector);
                if (element) {
                    element.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            handleFormFieldNavigation(e, index);
                        }
                    });
                }
            });

            setTimeout(() => {
                focusField(formFields[0].selector);
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupEnterNavigation();
        });
    </script>
@endpush
