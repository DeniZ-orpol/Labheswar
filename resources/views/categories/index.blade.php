@extends('app')

@section('content')
    @php
        $isPaginated = method_exists($categories, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Category
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('categories.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Category</a>
            </div>

            <div class="intro-y col-span-12 overflow-auto">
                <table id="DataTable" class="display table table-bordered w-full">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Image</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2">{{ $category->name }}</td>
                                <td class="px-4 py-2">
                                    @if ($category->image)
                                        <img src="{{ asset($category->image) }}" alt="Category Image"
                                            class="h-12 w-12 object-cover rounded">
                                    @else
                                        <span class="text-gray-500 italic">No Image</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('categories.show', $category->id) }}"
                                            class="btn btn-primary">View</a>
                                        <a href="{{ route('categories.edit', $category->id) }}"
                                            class="btn btn-primary">Edit</a>
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-gray-500 py-4">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Show pagination when data is paginated -->
                @if ($isPaginated && $categories->count() > 0)
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of
                            {{ $categories->total() }} entries
                        </div>
                        <div class="pagination-nav">
                            <nav role="navigation" aria-label="Pagination Navigation">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($categories->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">‹</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $categories->previousPageUrl() }}"
                                                rel="prev">‹</a>
                                        </li>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @for ($i = 1; $i <= $categories->lastPage(); $i++)
                                        @if ($i == $categories->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $categories->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    {{-- Next Page Link --}}
                                    @if ($categories->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $categories->nextPageUrl() }}"
                                                rel="next">›</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">›</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom Pagination Styles */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 0 1rem;
        }

        .pagination-info {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
        }

        .pagination {
            display: flex;
            align-items: center;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0;
        }

        .pagination li {
            margin: 0;
        }

        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            color: #374151;
            transition: all 0.15s ease;
        }

        /* First page button */
        .pagination .page-item:first-child a {
            border-radius: 6px 0 0 6px;
        }

        /* Last page button */
        .pagination .page-item:last-child a {
            border-radius: 0 6px 6px 0;
        }

        /* Single page item (when only one page) */
        .pagination .page-item:only-child a {
            border-radius: 6px;
        }

        /* Active page */
        .pagination .page-item.active span,
        .pagination .page-item.active a {
            background-color: #3b82f6;
            border-color: #3b82f6;
            /* color: #ffffff; */
            font-weight: 600;
        }

        /* Hover effects */
        .pagination a:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            color: #111827;
        }

        .pagination .page-item.active a:hover,
        .pagination .page-item.active span:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        /* Disabled state */
        .pagination .page-item.disabled span,
        .pagination .page-item.disabled a {
            color: #9ca3af;
            background-color: #f9fafb;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }

        .pagination .page-item.disabled:hover span,
        .pagination .page-item.disabled:hover a {
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        /* Previous/Next arrow styling */
        .pagination .page-item:first-child a,
        .pagination .page-item:last-child a {
            font-weight: 600;
        }

        /* Remove border between adjacent items */
        .pagination .page-item+.page-item a,
        .pagination .page-item+.page-item span {
            border-left: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .pagination-wrapper {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }

            .pagination-info {
                order: 2;
            }

            .pagination {
                order: 1;
            }
        }
    </style>
@endpush

{{-- @push('scripts')
    <script>
        function changeBranch() {
            const branchSelect = document.getElementById('branch_select');
            const selectedBranchId = branchSelect.value;

            // Build URL with branch_id parameter
            const currentUrl = new URL(window.location.href);

            if (selectedBranchId) {
                currentUrl.searchParams.set('branch_id', selectedBranchId);
            } else {
                currentUrl.searchParams.delete('branch_id');
            }

            // Remove page parameter when switching branches
            currentUrl.searchParams.delete('page');

            // Redirect to new URL
            window.location.href = currentUrl.toString();
        }
    </script>
@endpush --}}

{{-- @push('styles')
    <!-- TailwindCSS-Compatible DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" />
@endpush --}}

{{-- @push('styles')
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
@endpush --}}

{{-- @push('scripts')
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
@endpush --}}
