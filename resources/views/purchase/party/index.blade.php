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
            <div class="intro-y col-span-12 overflow-auto">
                <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                    <thead>
                        <tr class="bg-primary font-bold text-white">
                            <th>#</th>
                            <th>Party Name</th>
                            <th style="TEXT-ALIGN: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($parties && $parties->count())
                            @foreach ($parties as $party)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $party->party_name }}</td>
                                    <td>
                                        <div class="flex gap-2 justify-content-left">
                                            <form action=" {{ route('purchase.party.destroy', $party->id) }} "
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this role?');"
                                                style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                            </form>
                                            <a href="{{ route('purchase.party.edit', $party->id) }}"
                                                class="btn btn-primary mr-1 mb-2">
                                                Edit
                                            </a>
                                        </div>
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
            </div>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection

@push('styles')
    <!-- TailwindCSS-Compatible DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" />
@endpush

@push('styles')
    <style>
        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            /* gray-700 */
        }

        .dataTables_length select {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            /* gray-300 */
            font-size: 0.875rem;
            background-color: white;
            appearance: none;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_paginate {
            margin: 0.75rem 0;
        }

        .dataTables_length select {
            -webkit-appearance: none;
            /* Chrome, Safari */
            -moz-appearance: none;
            /* Firefox */
            appearance: none;
            /* Standard */
            background: none;
            padding-right: 1rem;
            /* ensure padding if icon is not used */
            background-color: white;
            /* fallback background */
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables + Tailwind -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#DataTable').DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                ordering: true,
                searching: true,
                responsive: true,
                language: {
                    paginate: {
                        previous: "←",
                        next: "→"
                    },
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        });
    </script>
@endpush
