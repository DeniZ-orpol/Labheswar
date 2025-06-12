@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Party
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a  class="btn btn-primary shadow-md mr-2 btn-hover">Add New Party</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12 ">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th style="TEXT-ALIGN: left;">Phone</th>
                        <th>Address</th>
                        <th style="TEXT-ALIGN: left;" >Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="TEXT-ALIGN: left;">{{ $user->phone }}</td>
                            <td>{{ $user->address }}</td>
                            {{-- <td>
                                <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                <!-- <button class="btn btn-primary">Message</button> -->
                                <div class="flex gap-2 justify-content-left">
                                    <a href="{{ route('users.show', $user->id) }}"
                                        class="btn btn-primary mr-1 mb-2">View  </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mr-1 mb-2"> Edit   </a>
                                </div>
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
