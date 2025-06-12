@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit User
        </h2>

        <form action="{{ route('users.update', $user->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="input-form col-span-3 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Name <p style="color: red;margin-left: 3px;">*</p>
                    </label>
                    <input id="name" type="text" name="name" class="form-control field-new"
                        value="{{ old('name', $user->name) }}" placeholder="Enter customer name" required maxlength="255">
                </div>

                <!-- Email -->
                <div class="input-form col-span-3 mt-3">
                    <label for="email" class="form-label w-full flex flex-col sm:flex-row">
                        Email
                    </label>
                    <input id="email" type="email" name="email" class="form-control field-new"
                        value="{{ old('email', $user->email) }}" placeholder="Enter customer email" maxlength="255">
                </div>

                <!-- Phone -->
                <div class="input-form col-span-3 mt-3">
                    <label for="phone" class="form-label w-full flex flex-col sm:flex-row">
                        Phone
                    </label>
                    <input id="phone" type="text" name="mobile" class="form-control field-new"
                        value="{{ old('mobile', $user->mobile) }}" placeholder="Enter phone number" maxlength="15">
                </div>

                <!-- Role -->
                <div class="input-form col-span-3 mt-3">
                    <label for="role" class="form-label w-full flex flex-col sm:flex-row">
                        Role <p style="color: red;margin-left: 3px;">*</p>
                    </label>
                    <select id="role" name="role" class="form-control field-new" required>
                        <option value="" disabled {{ old('role', $user->role) == '' ? 'selected' : '' }}>Choose...
                        </option>
                        <option value="Admin" {{ old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Manager" {{ old('role', $user->role) == 'Manager' ? 'selected' : '' }}>Manager
                        </option>
                        <option value="Cashier" {{ old('role', $user->role) == 'Cashier' ? 'selected' : '' }}>Cashier
                        </option>
                    </select>
                </div>

                <!-- Date of Birth -->
                <div class="input-form col-span-3 mt-3">
                    <label for="dob" class="form-label w-full flex flex-col sm:flex-row">
                        Date of Birth
                    </label>
                    <input id="dob" type="date" name="dob" class="form-control field-new"
                        value="{{ old('dob', $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '') }}">
                </div>

                <!-- Password -->
                <div class="input-form col-span-3 mt-3">
                    <label for="password" class="form-label w-full flex flex-col sm:flex-row">
                        Password
                    </label>
                    <input id="password" type="password" name="password" class="form-control field-new"
                        placeholder="Leave blank to keep existing password">
                </div>
            </div>

            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2 mt-5">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
        </form>
    </div>
@endsection
