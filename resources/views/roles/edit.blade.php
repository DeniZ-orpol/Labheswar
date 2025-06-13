@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Edit User
        </h2>

        <form action="{{ route('roles.update', $role->id) }}" method="POST" class="form-updated validate-form">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="grid grid-cols-12 gap-2 grid-updated">
                <div class="col-span-6 mt-3">
                    <label for="name" class="form-label w-full flex flex-col sm:flex-row">
                        Name <p style="color: red;margin-left: 3px;">*</p>
                    </label>
                    <input id="role_name" type="text" name="role_name" class="form-control field-new"
                        value="{{ old('role_name', $role->role_name) }}" placeholder="Enter Role name" required
                        maxlength="255">
                </div>

            </div>

            <a onclick="goBack()" class="btn btn-outline-primary shadow-md mr-2 mt-5">Back</a>
            <button type="submit" class="btn btn-primary mt-5 btn-hover">Update</button>
        </form>
    </div>
@endsection
