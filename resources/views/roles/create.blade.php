@extends('app')
@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Role
        </h2>
        <form action="{{ route('roles.store') }}" method="POST" class="form-updated">
            @csrf
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-6 mt-3">
                    <label for="role_name" class="form-label">Role Name<span style="color: red;margin-left: 3px;">
                            *</span></label>
                    <input type="text" name="role_name" id="role_name" class="form-control field-new" required>
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
