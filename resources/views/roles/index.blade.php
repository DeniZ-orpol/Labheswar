@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Roles
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('roles.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New Role</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>ID</th>
                        <th>Role Name</th>
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $role->role_name }}</td>
                            <td>
                                <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                <!-- <button class="btn btn-primary">Message</button> -->
                                <div class="flex gap-2 justify-content-left">
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this role?');"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE') <!-- Add this line -->
                                        <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                    </form>

                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary mr-1 mb-2"> Edit
                                        {{-- {{dd($role->id)}} --}}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
