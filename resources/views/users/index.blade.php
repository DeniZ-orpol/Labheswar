@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Users
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('users.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New User</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th style="TEXT-ALIGN: left;">Phone</th>
                        <th>Dob</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($users && $users->count())
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td style="TEXT-ALIGN: left;">{{ $user->mobile }}</td>
                                <td>{{ $user->dob }}</td>
                                <td>{{ $user->role_data->role_name ?? '-' }}</td>
                                <td>{{ $user->branch->name ?? '-' }}</td>
                                <td>
                                    <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                    <!-- <button class="btn btn-primary">Message</button> -->
                                    <div class="flex gap-2 justify-content-left">
                                        <a href="{{ route('users.show', $user->id) }}"
                                            class="btn btn-primary mr-1 mb-2">View
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE') <!-- Add this line -->
                                            <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                        </form>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mr-1 mb-2">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
