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
            Edit Purchase Party
        </h2>
        <form action="{{ route('purchase.party.update', $party->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="column p-5">
                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_group" class="form-label w-full flex flex-col sm:flex-row">Ledger Group<span
                                style="color: red;margin-left: 3px;">
                                *</span></label>
                        <select id="ledger_group" name="ledger_group" class="form-control field-new">
                            <option value="" {{ !$party->ledger_group ?? 'selected' }}>Select Ledger Group...</option>
                            <option value="SUNDRY DEBTORS" {{ $party->ledger_group == 'SUNDRY DEBTORS' ? 'selected' : '' }}>SUNDRY DEBTORS</option>
                            <option value="SUNDRY DEBTORS (E-COMMERCE)" {{ $party->ledger_group == 'SUNDRY DEBTORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY DEBTORS (E-COMMERCE)</option>
                            <option value="SUNDRY DEBTORS (FIELD STAFF)" {{ $party->ledger_group == 'SUNDRY DEBTORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY DEBTORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS" {{ $party->ledger_group == 'SUNDRY CREDITORS' ? 'selected' : '' }}>SUNDRY CREDITORS</option>
                            <option value="SUNDRY CREDITORS (E-COMMERCE)" {{ $party->ledger_group == 'SUNDRY CREDITORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY CREDITORS (E-COMMERCE)</option>
                            <option value="SUNDRY CREDITORS (EXPENSES PAYABLE)" {{ $party->ledger_group == 'SUNDRY CREDITORS (EXPENSES PAYABLE)' ? 'selected' : '' }}>SUNDRY CREDITORS (EXPENSES PAYABLE)</option>
                            <option value="SUNDRY CREDITORS (FIELD STAFF)" {{ $party->ledger_group == 'SUNDRY CREDITORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY CREDITORS (FIELD STAFF)</option>
                            <option value="SUNDRY CREDITORS (MANUFACTURERS)" {{ $party->ledger_group == 'SUNDRY CREDITORS (MANUFACTURERS)' ? 'selected' : '' }}>SUNDRY CREDITORS (MANUFACTURERS)</option>
                            <option value="SUNDRY CREDITORS (SUPPLIERS)" {{ $party->ledger_group == 'SUNDRY CREDITORS (SUPPLIERS)' ? 'selected' : '' }}>SUNDRY CREDITORS (SUPPLIERS)</option>
                        </select>
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Party Name<span style="color: red;margin-left: 3px;">
                                *</span></label>
                        <input type="text" name="party_name" class="form-control field-new" value="{{ old('party_name', $party->party_name) }}" required>
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
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="acc_no" class="form-control field-new" value="{{ old('acc_no', $party->acc_no) }}">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control field-new" value="{{ old('ifsc_code', $party->ifsc_code) }}">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Mobile NO.</label>
                        <input type="text" name="mobile_no" value="{{ old('mobile_no', $party->mobile_no) }}"
                            class="form-control field-new"oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control field-new" value="{{ old('email', $party->email) }}">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Station</label>
                        <input type="text" name="station" class="form-control field-new" value="{{ old('station', $party->station) }}">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Pin Code</label>
                        <input type="text" name="pincode" class="form-control field-new" value="{{ old('pincode', $party->pincode) }}"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label for="state" class="form-label w-full flex flex-col sm:flex-row">State</label>
                        <input id="state" type="text" name="state" class="form-control field-new" value="{{ old('state', $party->state) }}">
                    </div>
                    <div class="input-form col-span-3 mt-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control field-new">{{ old('address', $party->address) }}</textarea>
                    </div>
                </div>
                <div class="column p-5">
                    <!-- Balancing Method -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="balancing_method" class="form-label w-full flex flex-col sm:flex-row">Balancing
                            Method</label>
                        <select id="balancing_method" name="balancing_method" class="form-control field-new">
                            <option value="" {{ !$party->balancing_method ?? 'selected'}}>Select Balancing Method...</option>
                            <option value="Fifo Base" {{ $party->balancing_method == 'Fifo Base' ? 'selected' : '' }}>Fifo Base</option>
                            <option value="On Account" {{ $party->balancing_method == 'On Account' ? 'selected' : '' }}>On Account</option>
                        </select>
                    </div>
                    <!-- GST No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="gst_number" class="form-label w-full flex flex-col sm:flex-row">GST NO.</label>
                        <input id="gst_number" type="text" name="gst_number" class="form-control field-new" value="{{ old('gst_number', $party->gst_number) }}">
                    </div>
                    <!-- Pan No -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="pan_no" class="form-label w-full flex flex-col sm:flex-row">Pan No</label>
                        <input id="pan_no" type="text" name="pan_no" class="form-control field-new" value="{{ old('pan_no', $party->pan_no) }}">
                    </div>
                    <!-- GST Heading -->
                    {{-- <div class="input-form col-span-3 mt-3">
                        <label for="gst_heading" class="form-label w-full flex flex-col sm:flex-row">GST Heading</label>
                        <input id="gst_heading" type="text" name="gst_heading" class="form-control field-new">
                    </div> --}}
                    <!-- Mail To -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="mail_to" class="form-label w-full flex flex-col sm:flex-row">Mail To</label>
                        <input id="mail_to" type="text" name="mail_to" class="form-control field-new" value="{{ old('mail_to', $party->mail_to) }}">
                    </div>
                    <!-- Contact person -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="contact_person" class="form-label w-full flex flex-col sm:flex-row">Contact
                            Person</label>
                        <input id="contact_person" type="text" name="contact_person" class="form-control field-new" value="{{ old('contact_person', $party->contact_person) }}">
                    </div>
                    <!-- Designation -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="designation" class="form-label w-full flex flex-col sm:flex-row">Designation</label>
                        <input id="designation" type="text" name="designation" class="form-control field-new" value="{{ old('designation', $party->designation) }}">
                    </div>
                    <!-- Note -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="note" class="form-label w-full flex flex-col sm:flex-row">Note</label>
                        <textarea name="note" id="note" class="form-control field-new">{{ old('note', $party->note) }}</textarea>
                    </div>
                    <!-- Ledger Category -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="ledger_category" class="form-label w-full flex flex-col sm:flex-row">
                            Ledger Category
                        </label>
                        <select id="ledger_category" name="ledger_category" class="form-control field-new">
                            <option value="" {{ !$party->ledger_category ?? 'selected' }}>Select Ledger Category...</option>
                            <option value="Retailer" {{ $party->ledger_category == 'Retailer' ? 'selected' : '' }}>Retailer</option>
                            <option value="Stock List" {{ $party->ledger_category == 'Stock List' ? 'selected' : '' }}>Stock List</option>
                            <option value="Distributor" {{ $party->ledger_category == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                            <option value="Other" {{ $party->ledger_category == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <!-- Country -->
                    <div class="input-form col-span-3 mt-3">
                        <label for="country" class="form-label w-full flex flex-col sm:flex-row">Country</label>
                        <input id="country" type="text" name="country" class="form-control field-new" value="{{ old('country', $party->country) }}">
                    </div>
                </div>
            </div>

            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2 mt-5">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
        </form>
    </div>
@endsection
