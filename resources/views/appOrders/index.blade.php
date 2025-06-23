@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Orders
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            {{-- <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('categories.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Category</a>
            </div> --}}

            <div class="intro-y col-span-12 overflow-auto">
                <table id="DataTable" class="display table table-bordered w-full">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-4 py-2">Order ID</th>
                            <th class="px-4 py-2">Order Date</th>
                            <th class="px-4 py-2">Sub Total</th>
                            <th class="px-4 py-2">Texes</th>
                            <th class="px-4 py-2">Total</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $index => $order)
                            <tr class="border-b">
                                <td class="px-4 py-2">O-ID: {{ $order->id }}</td>
                                <td class="px-4 py-2">{{ $order->created_at->format('d-m-Y') }}</td>
                                <td class="px-4 py-2">{{ $order->sub_total }}</td>
                                <td class="px-4 py-2">{{ $order->total_texes }}</td>
                                <td class="px-4 py-2">{{ $order->total }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{route('orders.show', $order->id)}}"
                                            class="btn btn-primary">Order Receipt</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 py-4">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
