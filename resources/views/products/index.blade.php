@extends('app')

@section('content')
    @php
        $isSuperAdmin = strtolower($role->role_name) === 'super admin';
        $isPaginated = method_exists($products, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Products
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('products.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Product</a>
                @if (!$isSuperAdmin)
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="excel_file">Import Products (Excel):</label>
                        <input type="file" name="excel_file" required accept=".csv, .xlsx, .xls">
                        <button type="submit">Import</button>
                    </form>
                @endif

                @if ($isSuperAdmin)
                    <div class="flex items-center gap-2 ml-auto">
                        <label for="branch_select" class="text-sm font-medium">Select Branch:</label>
                        <select id="branch_select" class="form-select border-gray-300 rounded-md" onchange="changeBranch()">
                            <option value="">-- Select Branch --</option>
                            @foreach ($availableBranches as $availableBranch)
                                <option value="{{ $availableBranch->id }}"
                                    {{ isset($selectedBranch) && $selectedBranch->id == $availableBranch->id ? 'selected' : '' }}>
                                    {{ $availableBranch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </div>

            <div class="intro-y col-span-12 overflow-auto">
                @if ($isSuperAdmin && isset($showNoBranchMessage) && $showNoBranchMessage)
                    <div class="text-center py-8">
                        <div class="text-gray-500 text-lg">
                            <p>Please select a branch to view products</p>
                        </div>
                    </div>
                @else
                    <table id="DataTable" class="display table table-bordered w-full">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th>#</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>HSN</th>
                                <th>MRP</th>
                                @if ($isSuperAdmin && isset($selectedBranch))
                                    <th>Branch</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        @if ($isPaginated)
                                            {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image"
                                                width="80">
                                        @else
                                            No Image
                                        @endif
                                    </td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->category->name ?? '-' }}</td>
                                    <td>{{ $product->hsnCode->hsn_code ?? '-' }}</td>
                                    <td>{{ $product->mrp }}</td>
                                    @if ($isSuperAdmin && isset($selectedBranch))
                                        <td>{{ $selectedBranch->name }}</td>
                                    @endif
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ $isSuperAdmin && isset($selectedBranch)
                                                ? route('products.show', ['id' => $product->id, 'branch' => $selectedBranch->id])
                                                : route('products.show', ['id' => $product->id]) }}"
                                                class="btn btn-primary">View</a>
                                            <a href="{{ $isSuperAdmin && isset($selectedBranch)
                                                ? route('products.edit', ['id' => $product->id, 'branch' => $selectedBranch->id])
                                                : route('products.edit', $product->id) }}"
                                                class="btn btn-primary">Edit</a>
                                            <form
                                                action="{{ $isSuperAdmin && isset($selectedBranch)
                                                    ? route('products.destroy', ['id' => $product->id, 'branch' => $selectedBranch->id])
                                                    : route('products.destroy', $product->id) }}"
                                                method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin && isset($selectedBranch) ? '8' : '7' }}"
                                        class="text-center">No Products Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Show pagination only for branch users (when data is paginated) -->
                    @if ($isPaginated)
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                                {{ $products->total() }} entries
                            </div>
                            <div class="pagination-nav">
                                <nav role="navigation" aria-label="Pagination Navigation">
                                    <ul class="pagination">
                                        {{-- Previous Page Link --}}
                                        @if ($products->onFirstPage())
                                            <li class="page-item disabled" aria-disabled="true">
                                                <span class="page-link">‹</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $products->previousPageUrl() }}"
                                                    rel="prev">‹</a>
                                            </li>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @for ($i = 1; $i <= $products->lastPage(); $i++)
                                            @if ($i == $products->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $products->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Next Page Link --}}
                                        @if ($products->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $products->nextPageUrl() }}"
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

@push('scripts')
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
@endpush

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
