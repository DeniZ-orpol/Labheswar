@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            user
        </h2>
        <form action="{{ route('users.store') }}" method="POST" class="form-updated validate-form">
            @csrf <!-- CSRF token for security -->
            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="input-form col-span-3 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Name<p style="color: red;margin-left: 3px;"> *</p>
                    </label>
                    <input id="name" type="text" name="name" class="form-control field-new"
                        placeholder="Enter customer name" required maxlength="255">
                </div>

                <!-- Email -->
                <div class="input-form col-span-3 mt-3">
                    <label for="email" class="form-label w-full flex flex-col sm:flex-row">
                        Email
                    </label>
                    <input id="email" type="email" name="email" class="form-control field-new"
                        placeholder="Enter customer email" maxlength="255">
                </div>

                <!-- Phone -->
                <div class="input-form col-span-3 mt-3">
                    <label for="phone" class="form-label w-full flex flex-col sm:flex-row">
                        Phone
                    </label>
                    <input id="phone" type="text" name="mobile" class="form-control field-new"
                        placeholder="Enter phone number" required maxlength="15">
                </div>

                <!-- Role -->
                <div class="input-form col-span-3 mt-3">
                    <label for="customer_type" class="form-label w-full flex flex-col sm:flex-row">
                        Role<p style="color: red;margin-left: 3px;"> *</p>
                    </label>
                    <select id="customer_type" name="role" class="form-control field-new" required>
                        <option value="" selected>Choose...</option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Cashier">Cashier</option>
                    </select>
                </div>

                <!-- Date of Birth -->
                <div class="input-form col-span-3 mt-3">
                    <label for="dob" class="form-label w-full flex flex-col sm:flex-row">
                        Date of Birth
                    </label>
                    <input id="dob" type="date" name="dob" class="form-control field-new">
                </div>

                <!-- Password -->
                <div class="input-form col-span-3 mt-3">
                    <label for="preferred_payment_method" class="form-label w-full flex flex-col sm:flex-row">
                        Password
                    </label>
                    <input id="preferred_payment_method" type="password" name="password" class="form-control field-new"
                        placeholder="Enter password">
                </div>


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
