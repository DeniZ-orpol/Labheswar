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

                <!-- Date of Birth -->
                <div class="input-form col-span-3 mt-3">
                    <label for="dob" class="form-label w-full flex flex-col sm:flex-row">
                        Date of Birth
                    </label>
                    <input id="dob" type="date" name="dob" class="form-control field-new"
                        value="{{ old('dob', $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '') }}">
                </div>

                <!-- Role -->
                <div class="input-form col-span-3 mt-3">
                    <label for="role_id" class="form-label w-full flex flex-col sm:flex-row">
                        Role<p style="color: red;margin-left: 3px;"> *</p>
                    </label>
                    <select id="role_id" name="role_id" class="form-control field-new" required>
                        <option value="" disabled {{ !$user->role_id ? 'selected' : '' }}>Choose...</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch -->
                <div class="input-form col-span-3 mt-3">
                    <label for="branch_id" class="form-label w-full flex flex-col sm:flex-row">
                        Branch<p style="color: red;margin-left: 3px;"> *</p>
                    </label>
                    <input id="branch_id" type="text" name="branch_id" class="form-control field-new"
                        value="{{ old('branch_id', $user->branch->name) }}" disabled >
                    {{-- <select id="branch_id" name="branch_id" class="form-control field-new" required>
                        <option value="" disabled {{ !$user->branch->id ? 'selected' : '' }}>Choose...</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branch->id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select> --}}
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
