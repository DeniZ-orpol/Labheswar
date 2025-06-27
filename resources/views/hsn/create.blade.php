@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Hsn Code
        </h2>
        <form action="{{ route('hsn_codes.store') }}" method="POST" class="form-updated">
            @csrf
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-3 mt-3">
                    <label for="hsn_code" class="form-label">Hsn Code<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="hsn_code" id="hsn_code" class="form-control field-new" required>
                </div>
                <div class="col-span-3 mt-3">
                    <label for="gst" class="form-label">GST(%)<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="gst" id="gst" class="form-control field-new" required
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                </div>
                <div class="col-span-3 mt-3">
                    <label for="short_name" class="form-label">Short Name</label>
                    <input type="text" name="short_name" id="short_name" class="form-control field-new" required>
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
