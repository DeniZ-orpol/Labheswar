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
        .error-text {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
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
                        <select id="ledger_group" name="ledger_group" class="form-control field-new @error('ledger_group') is-invalid @enderror">
                            <option value="" selected>Select Ledger Group...</option>
                            <option value="SUNDRY DEBTORS" {{ old('ledger_group') == 'SUNDRY DEBTORS' ? 'selected' : '' }}>SUNDRY DEBTORS</option>
                            <option value="SUNDRY DEBTORS (E-COMMERCE)" {{ old('ledger_group') == 'SUNDRY DEBTORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY DEBTORS (E-COMMERCE)</option>
                            <option value="SUNDRY DEBTORS (FIELD STAFF)" {{ old('ledger_group') == 'SUNDRY DEBTORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY DEBTORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS" {{ old('ledger_group') == 'SUNDRY CREDITORS' ? 'selected' : '' }}>SUNDRY CREDITORS</option>
                            <option value="SUNDRY CREDITORS (E-COMMERCE)" {{ old('ledger_group') == 'SUNDRY CREDITORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY CREDITORS (E-COMMERCE)</option>
                            <option value="SUNDRY CREDITORS (EXPENSES PAYABLE)" {{ old('ledger_group') == 'SUNDRY CREDITORS (EXPENSES PAYABLE)' ? 'selected' : '' }}>SUNDRY CREDITORS (EXPENSES PAYABLE)</option>
                            <option value="SUNDRY CREDITORS (FIELD STAFF)" {{ old('ledger_group') == 'SUNDRY CREDITORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY CREDITORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS (MANUFACTURERS)" {{ old('ledger_group') == 'SUNDRY CREDITORS (MANUFACTURERS)' ? 'selected' : '' }}>SUNDRY CREDITORS (MANUFACTURERS)</option>
                            <option value="SUNDRY CREDITORS (SUPPLIERS)" {{ old('ledger_group') == 'SUNDRY CREDITORS (SUPPLIERS)' ? 'selected' : '' }}>SUNDRY CREDITORS (SUPPLIERS)</option>
                        </select>
                        @error('ledger_group')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- GST No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_number" class="form-label w-full flex flex-col sm:flex-row">GST No</label>
                        <input id="gst_number" type="text" name="gst_number" class="form-control field-new @error('gst_number') is-invalid @enderror" value="{{ old('gst_number') }}">
                        @error('gst_number')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Party Name<span style="color: red;margin-left: 3px;">
                                *</span></label>
                        <input type="text" name="party_name" class="form-control field-new @error('party_name') is-invalid @enderror" value="{{ old('party_name') }}" >
                        @error('party_name')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- State -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="state" class="form-label w-full flex flex-col sm:flex-row">State</label>
                        <input id="state" type="text" name="state" class="form-control field-new @error('state') is-invalid @enderror" value="{{ old('state') }}">
                        @error('state')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Pan No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pan_no" class="form-label w-full flex flex-col sm:flex-row">Pan No</label>
                        <input id="pan_no" type="text" name="pan_no" class="form-control field-new @error('pan_no') is-invalid @enderror" value="{{ old('pan_no') }}">
                        @error('pan_no')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control field-new @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}">
                        @error('company_name')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div> --}}
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Gst NO.</label>
                        <input type="text" name="gst_number" class="form-control field-new">
                    </div> --}}
                    
                    <!-- <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Station</label>
                        <input type="text" name="station" class="form-control field-new">
                    </div> -->
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Pin Code</label>
                        <input type="text" name="pincode" class="form-control field-new @error('pincode') is-invalid @enderror"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" value="{{ old('pincode') }}">
                        @error('pincode')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control field-new @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Country -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="country" class="form-label w-full flex flex-col sm:flex-row">Country</label>
                        <input id="country" type="text" name="country" class="form-control field-new @error('country') is-invalid @enderror" value="{{ old('country') }}">
                        @error('country')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="column p-5">
                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="balancing_method" class="form-label w-full flex flex-col sm:flex-row">Balancing
                            Method</label>
                        <select id="balancing_method" name="balancing_method" class="form-control field-new @error('balancing_method') is-invalid @enderror">
                            <option value="Bill By Bill" {{ old('balancing_method') == 'Bill By Bill' ? 'selected' : '' }}>Bill By Bill</option>
                            <option value="Fifo Base" {{ old('balancing_method') == 'Fifo Base' ? 'selected' : '' }}>Fifo Base</option>
                            <option value="On Account" {{ old('balancing_method') == 'On Account' ? 'selected' : '' }}>On Account</option>
                        </select>
                        @error('balancing_method')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="acc_no" class="form-control field-new @error('acc_no') is-invalid @enderror" value="{{ old('acc_no') }}">
                        @error('acc_no')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control field-new @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code') }}">
                        @error('ifsc_code')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Mobile NO.</label>
                        <input type="text" name="mobile_no"
                            class="form-control field-new @error('mobile_no') is-invalid @enderror" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" value="{{ old('mobile_no') }}">
                        @error('mobile_no')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- GST Heading -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="gst_heading" class="form-label w-full flex flex-col sm:flex-row">GST Heading</label>
                        <input id="gst_heading" type="text" name="gst_heading" class="form-control field-new">
                    </div> --}}
                    <!-- Mail To -->
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control field-new @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- <div class="input-form col-span-3 mt-3">
                        <label for="mail_to" class="form-label w-full flex flex-col sm:flex-row">Mail To</label>
                        <input id="mail_to" type="text" name="mail_to" class="form-control field-new">
                    </div> -->
                    <!-- Contact person -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person" class="form-label w-full flex flex-col sm:flex-row">Sales
                            Man</label>
                        <input id="contact_person" type="text" name="contact_person" class="form-control field-new @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}">
                        @error('contact_person')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person_no" class="form-label w-full flex flex-col sm:flex-row">S. Contact
                            No</label>
                        <input id="contact_person_no" type="text" name="contact_person_no" class="form-control field-new @error('contact_person_no') is-invalid @enderror" value="{{ old('contact_person_no') }}">
                        @error('contact_person_no')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
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
                            Ledger Type
                        </label>
                        <select id="ledger_category" name="ledger_category" class="form-control field-new @error('ledger_category') is-invalid @enderror">
                            <option value="" selected>Select Ledger Type...</option>
                            <option value="registered" {{ old('ledger_category') == 'registered' ? 'selected' : '' }}>Registered</option>
                            <option value="composition" {{ old('ledger_category') == 'composition' ? 'selected' : '' }}>Composition</option>
                            <option value="unregistered" {{ old('ledger_category') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                            <option value="sez" {{ old('ledger_category') == 'sez' ? 'selected' : '' }}>Sez</option>
                            <option value="rcm_compulsory" {{ old('ledger_category') == 'rcm_compulsory' ? 'selected' : '' }}>Rcm Compulsory</option>
                            <option value="gst_reversal" {{ old('ledger_category') == 'gst_reversal' ? 'selected' : '' }}>GST Reversal </option>
                            <option value="related" {{ old('ledger_category') == 'related' ? 'selected' : '' }}>Releted</option>
                            <option value="uin_holder" {{ old('ledger_category') == 'uin_holder' ? 'selected' : '' }}>UIN Holder</option>
                            <option value="prohibited" {{ old('ledger_category') == 'prohibited' ? 'selected' : '' }}>Prohibited</option>
                            <option value="production_house" {{ old('ledger_category') == 'production_house' ? 'selected' : '' }}>Production House</option>
                            <option value="store_transfer" {{ old('ledger_category') == 'store_transfer' ? 'selected' : '' }}>Store Transfer</option>
                        </select>
                        @error('ledger_category')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
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
                { selector: '#gst_number', type: 'input' },
                { selector: 'input[name="party_name"]', type: 'input' },
                { selector: '#state', type: 'input' },
                { selector: '#pan_no', type: 'input' },
                // { selector: 'input[name="station"]', type: 'input' },
                { selector: 'input[name="pincode"]', type: 'input' },
                { selector: '#address', type: 'textarea' },
                { selector: '#country', type: 'input' },
                { selector: '#balancing_method', type: 'select' },
                { selector: 'input[name="acc_no"]', type: 'input' },
                { selector: 'input[name="ifsc_code"]', type: 'input' },
                { selector: 'input[name="mobile_no"]', type: 'input' },
                // { selector: '#mail_to', type: 'input' },
                { selector: 'input[name="email"]', type: 'input' },
                { selector: '#contact_person', type: 'input' },
                { selector: '#contact_person_no', type: 'input' },
                { selector: '#ledger_category', type: 'select' }
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
