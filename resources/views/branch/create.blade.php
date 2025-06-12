@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Party
        </h2>
        <form action="{{ route('branch.store') }}" method="POST" class=" form-updated validate-form">
            @csrf
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="input-form col-span-3 mt-3">
                    <label for="branch_name" class="form-label w-full flex flex-col sm:flex-row">
                        Brach Name<p style="color: red;margin-left: 3px;"> *</p> 
                    </label>
                    <input id="branch_name" type="text" name="name" class="form-control field-new"
                        placeholder="Enter Branch name" required maxlength="255">
                </div>

                <!-- Address -->
                <div class="input-form col-span-3 mt-3">
                    <label for="branch_address" class="form-label w-full flex flex-col sm:flex-row">
                        Address<span style="color: red;margin-left: 3px;"> *</span>
                    </label>
                    <textarea id="branch_address" name="address" class="form-control field-new" placeholder="Enter address"></textarea>
                </div>

                <!-- latitude -->
                <div class="input-form col-span-3 mt-3">
                    <label for="latitude" class="form-label w-full flex flex-col sm:flex-row">
                        Latitude
                    </label>
                    <input id="latitude" type="text" name="latitude" class="form-control field-new"=">
                </div>

                <!-- longitude -->
                <div class="input-form col-span-3 mt-3">
                    <label for="longitude" class="form-label w-full flex flex-col sm:flex-row">
                        Longitude
                    </label>
                    <input id="longitude" type="text" name="longitude" class="form-control field-new">
                </div>

                <!-- GST No -->
                <div class="input-form col-span-3 mt-3">
                    <label for="gst_no" class="form-label w-full flex flex-col sm:flex-row">
                        GST No 
                    </label>
                    <input id="gst_no" type="text" name="gst_no" class="form-control field-new"
                        placeholder="Enter phone number" required maxlength="15">
                </div>

                <!-- Date of Birth -->
                {{-- <div class="input-form col-span-3 mt-3">
                    <label for="dob" class="form-label w-full flex flex-col sm:flex-row">
                        Date of Birth 
                    </label>
                    <input id="dob" type="date" name="dob" class="form-control field-new">
                </div> --}}

                <!-- Branch admin -->
                <div class="input-form col-span-3 mt-3">
                    <label for="branch_admin" class="form-label w-full flex flex-col sm:flex-row">
                        Branch Admin 
                    </label>
                    <select id="branch_admin" name="branch_admin" class="form-control field-new">
                        <option value="" selected>Choose...</option>
                        @foreach ($users as $user)
                            @if($user != null && $user->role != 'Superadmin')
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Preferred Payment Method -->
                {{-- <div class="input-form col-span-3 mt-3">
                    <label for="preferred_payment_method" class="form-label w-full flex flex-col sm:flex-row">
                        Preferred Payment Method 
                    </label>
                    <input id="preferred_payment_method" type="text" name="preferred_payment_method"
                        class="form-control field-new" placeholder="Enter preferred payment method">
                </div> --}}

                <!-- Loyalty Points -->
                 {{-- <div class="input-form col-span-3 mt-3">
                    <label for="loyalty_points" class="form-label w-full flex flex-col sm:flex-row">
                        Loyalty Points <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional, must be an integer
                        </span>
                    </label>
                    <input id="loyalty_points" type="number" step="any" name="loyalty_points" class="form-control field-new"
                        placeholder="Enter loyalty points" >
                </div> --}}

                <!-- GST Number -->
                {{-- <div class="input-form col-span-3 mt-3">
                    <label for="gst_number" class="form-label w-full flex flex-col sm:flex-row">
                        GST Number 
                    </label>
                    <input id="gst_number" type="text" name="gst_number" class="form-control field-new"
                        placeholder="Enter GST number" maxlength="15">
                </div> --}}

                <!-- Party  Type -->
                {{-- <div class="input-form col-span-3 mt-3">
                    <label for="customer_type" class="form-label w-full flex flex-col sm:flex-row">
                        Party  Type
                    </label>
                    <select id="customer_type" name="customer_type" class="form-control field-new" required>
                        <option value="" selected>Choose...</option>
                        <option value="Retail">Retail</option>
                        <option value="Wholesale">Wholesale</option>
                    </select>
                </div> --}}

                <!-- Notes -->
                {{-- <div class="input-form col-span-4 mt-3">
                    <label for="notes" class="form-label w-full flex flex-col sm:flex-row">
                        Notes 
                    </label>
                    <textarea id="notes" name="notes" class="form-control field-new" placeholder="Enter any additional notes"></textarea>
                </div>
                <br> --}}
                <!-- Submit Button -->

            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Submit</button>
        </form>
        <!-- END: Validation Form -->
        <!-- BEGIN: Success Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Branh Created Successfully!</div>
                {{-- <div class="text-slate-500 mt-1"> Please check your e-mail for further info! </div> --}}
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
