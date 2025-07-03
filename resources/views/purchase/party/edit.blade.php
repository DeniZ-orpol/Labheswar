@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit Purchase Party
        </h2>
        <form action="{{ route('purchase.party.update', $party->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-6 mt-3">
                    <label class="form-label w-full flex flex-col sm:flex-row">
                        Party Name <p style="color: red;margin-left: 3px;">*</p>
                    </label>
                    <input type="text" name="party_name" class="form-control field-new"
                        value="{{ old('party_name', $party->party_name) }}" placeholder="Enter Purchase Party name" required
                        maxlength="255">
                </div>
                {{-- <div class="col-span-6 mt-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control field-new" value="{{ old('company_name', $party->company_name) }}"
                        placeholder="Enter Company Name">
                </div> --}}
                <div class="col-span-6 mt-3">
                    <label class="form-label">Gst NO.<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="gst_number" class="form-control field-new" required value="{{ old('gst_number', $party->gst_number) }}"
                        placeholder="Enter GST Number">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Bank Account Number</label>
                    <input type="text" name="acc_no" class="form-control field-new" value="{{ old('acc_no', $party->acc_no) }}"
                        placeholder="Enter Bank Account Number">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control field-new" value="{{ old('ifsc_code', $party->ifsc_code) }}"
                        placeholder="Enter IFSC Code">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Station</label>
                    <input type="text" name="station" class="form-control field-new" value="{{ old('station', $party->station) }}"
                        placeholder="Enter Station">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Pin Code</label>
                    <input type="text" name="pincode" class="form-control field-new" value="{{ old('pincode', $party->pincode) }}"
                        placeholder="Enter Pin Code" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Mobile NO.</label>
                    <input type="text" name="mobile_no"
                        class="form-control field-new"oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" value="{{ old('mobile_no', $party->mobile_no) }}">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control field-new" value="{{ old('email', $party->email) }}"
                        placeholder="Enter Email">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Address</label>
                    <input type="textbox" name="address" class="form-control field-new" style="height: 100px" value="{{ old('address', $party->address) }}"
                        placeholder="Enter Address">
                </div>
            </div>

            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2 mt-5">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
        </form>
    </div>
@endsection
