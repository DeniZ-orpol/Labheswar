@extends('app')
@section('content')
    @php
        $isPaginated = method_exists($parties, 'links');
    @endphp
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Purchase Party List
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
                            {{-- <th>Ledger Group</th> --}}
                            <th style="TEXT-ALIGN: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($parties && $parties->count())
                            @foreach ($parties as $party)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $party->party_name }}</td>
                                    {{-- <td>{{ $party->ledger_group }}</td> --}}
                                    <td>
                                        <div class="flex gap-2 justify-content-left">
                                            <a href="{{ route('purchase.party.show', $party->id) }}"
                                                class="btn btn-primary mr-1 mb-2">
                                                View
                                                {{-- {{ dd($hsn->id) }} --}}
                                            </a>
                                            <form action=" {{ route('purchase.party.destroy', $party->id) }} "
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this role?');"
                                                style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                            </form>
                                            {{-- <a href="{{ route('purchase.party.edit', $party->id) }}"
                                                class="btn btn-primary mr-1 mb-2">
                                                Edit
                                            </a> --}}
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
                @if ($isPaginated)
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Showing {{ $parties->firstItem() }} to {{ $parties->lastItem() }} of
                            {{ $parties->total() }} entries
                        </div>
                        <div class="pagination-nav">
                            <nav role="navigation" aria-label="Pagination Navigation">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($parties->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">‹</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $parties->previousPageUrl() }}"
                                                rel="prev">‹</a>
                                        </li>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @for ($i = 1; $i <= $parties->lastPage(); $i++)
                                        @if ($i == $parties->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $parties->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    {{-- Next Page Link --}}
                                    @if ($parties->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $parties->nextPageUrl() }}" rel="next">›</a>
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

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection

{{-- @push('styles')
    <!-- TailwindCSS-Compatible DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" />
@endpush --}}

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
