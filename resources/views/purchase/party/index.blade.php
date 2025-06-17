@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Purchase
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ route('purchase.party.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Create New
                    Party</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>Id</th>
                        <th>Party Name</th>
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($parties && $parties->count())
                        @foreach ($parties as $party)
                            <tr>
                                <td>{{ $party->id }}</td>
                                <td>{{ $party->party_name }}</td>
                                <td>
                                    <div class="flex gap-2 justify-content-left">
                                        <form action="#" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this role?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                        </form>
                                    </div>
                                    <a href="{{route('purchase.party.edit', $party->id)}}" class="btn btn-primary mr-1 mb-2">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No Purchase Party found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
