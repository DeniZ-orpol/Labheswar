@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Create Purchase Party
        </h2>
        <form action="{{ route('purchase.party.store') }}" method="POST" class="form-updated">
            @csrf
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-6 mt-3">
                    <label class="form-label">Party Name<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="party_name" class="form-control field-new" required>
                </div>
                {{-- <div class="col-span-6 mt-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control field-new">
                </div> --}}
                <div class="col-span-6 mt-3">
                    <label class="form-label">Gst NO.<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="gst_number" class="form-control field-new" required>
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Bank Account Number</label>
                    <input type="text" name="acc_no" class="form-control field-new">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control field-new">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Station</label>
                    <input type="text" name="station" class="form-control field-new">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Pin Code</label>
                    <input type="text" name="pincode" class="form-control field-new" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Mobile NO.</label>
                    <input type="text" name="mobile_no"
                        class="form-control field-new"oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control field-new">
                </div>
                <div class="col-span-6 mt-3">
                    <label class="form-label">Address</label>
                    <input type="textbox" name="address" class="form-control field-new" style="height: 100px">
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
