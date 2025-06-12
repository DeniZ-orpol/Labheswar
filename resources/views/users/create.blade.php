@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Party
        </h2>
        <form action="{{ route('customers.store') }}" method="POST" class=" form-updated validate-form">
            @csrf <!-- CSRF token for security -->
            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="input-form col-span-3 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Name<p style="color: red;margin-left: 3px;"> *</p> <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Required, max 255 characters
                        </span>
                    </label>
                    <input id="name" type="text" name="name" class="form-control field-new"
                        placeholder="Enter customer name" required maxlength="255">
                </div>

                <!-- Email -->
                <div class="input-form col-span-3 mt-3">
                    <label for="email" class="form-label w-full flex flex-col sm:flex-row">
                        Email <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional, must be a valid email
                        </span>
                    </label>
                    <input id="email" type="email" name="email" class="form-control field-new"
                        placeholder="Enter customer email" maxlength="255">
                </div>

                <!-- Phone -->
                <div class="input-form col-span-3 mt-3">
                    <label for="phone" class="form-label w-full flex flex-col sm:flex-row">
                        Phone <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Required, max 15 characters
                        </span>
                    </label>
                    <input id="phone" type="text" name="phone" class="form-control field-new"
                        placeholder="Enter phone number" required maxlength="15">
                </div>

                <!-- Address -->
                <div class="input-form col-span-3 mt-3">
                    <label for="address" class="form-label w-full flex flex-col sm:flex-row">
                        Address <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional
                        </span>
                    </label>
                    <textarea id="address" name="address" class="form-control field-new" placeholder="Enter address"></textarea>
                </div>

                <!-- Date of Birth -->
                <div class="input-form col-span-3 mt-3">
                    <label for="dob" class="form-label w-full flex flex-col sm:flex-row">
                        Date of Birth <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional, must be a valid date
                        </span>
                    </label>
                    <input id="dob" type="date" name="dob" class="form-control field-new">
                </div>

                <!-- Gender -->
                <div class="input-form col-span-3 mt-3">
                    <label for="gender" class="form-label w-full flex flex-col sm:flex-row">
                        Gender <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional, select one
                        </span>
                    </label>
                    <select id="gender" name="gender" class="form-control field-new">
                        <option value="" selected>Choose...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Preferred Payment Method -->
                <div class="input-form col-span-3 mt-3">
                    <label for="preferred_payment_method" class="form-label w-full flex flex-col sm:flex-row">
                        Preferred Payment Method <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional
                        </span>
                    </label>
                    <input id="preferred_payment_method" type="text" name="preferred_payment_method"
                        class="form-control field-new" placeholder="Enter preferred payment method">
                </div>

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
                <div class="input-form col-span-3 mt-3">
                    <label for="gst_number" class="form-label w-full flex flex-col sm:flex-row">
                        GST Number <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional, max 15 characters
                        </span>
                    </label>
                    <input id="gst_number" type="text" name="gst_number" class="form-control field-new"
                        placeholder="Enter GST number" maxlength="15">
                </div>

                <!-- Party  Type -->
                <div class="input-form col-span-3 mt-3">
                    <label for="customer_type" class="form-label w-full flex flex-col sm:flex-row">
                        Party  Type<p style="color: red;margin-left: 3px;"> *</p> <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Required, select one
                        </span>
                    </label>
                    <select id="customer_type" name="customer_type" class="form-control field-new" required>
                        <option value="" selected>Choose...</option>
                        <option value="Retail">Retail</option>
                        <option value="Wholesale">Wholesale</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="input-form col-span-4 mt-3">
                    <label for="notes" class="form-label w-full flex flex-col sm:flex-row">
                        Notes <span class="sm:ml-auto mt-1 sm:mt-0 text-xs text-slate-500">
                            Optional
                        </span>
                    </label>
                    <textarea id="notes" name="notes" class="form-control field-new" placeholder="Enter any additional notes"></textarea>
                </div>
                <br>
                <!-- Submit Button -->

            </div>
            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2">Back    </a>
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
