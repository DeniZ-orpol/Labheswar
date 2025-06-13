@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Branch
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            {{-- <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ route('branch.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New Branch</a>
            </div> --}}

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12 ">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>GST No</th>
                        {{-- <th>Branch Admin</th> --}}
                        {{-- <th>Email</th>
                        <th style="TEXT-ALIGN: left;">Phone</th> --}}
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($branches as $branch)
                        <tr>
                            <td>{{ $branch->id }}</td>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->location }}</td>
                            <td>{{ $branch->latitude }}</td>
                            <td>{{ $branch->longitude }}</td>
                            <td>{{ $branch->gst_no }}</td>
                            {{-- <td>{{ $branch->admin_name ?? 'No Admin Assigned' }}</td> --}}
                            <td>
                                <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                <!-- <button class="btn btn-primary">Message</button> -->
                                <div class="flex gap-2 justify-content-left">
                                    <a href="{{ route('branch.show', $branch->id) }}"
                                        class="btn btn-primary mr-1 mb-2">View</a>
                                    <a href="{{ route('branch.edit', $branch->id) }}" class="btn btn-primary mr-1 mb-2">
                                        Edit</a>
                                    {{-- <form action="{{ route('branch.delete', $branch->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');"
                                        style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                    </form> --}}
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
