@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Hsn Codes
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('hsn_codes.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New Hsn
                    Code</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>#</th>
                        <th>Hsn Code</th>
                        <th>Cgst</th>
                        <th>Sgst</th>
                        <th>Igst</th>
                        <th>Short Name</th>
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hsns as $hsn)
                        <tr>
                            {{-- {{dd($hsn->gst)}} --}}
                            <td>{{ $loop->iteration + $hsns->firstItem() - 1 }}</td>
                            <td>{{ $hsn->hsn_code }}</td>
                            <td>{{ number_format((float) $hsn->gst / 2, 2) }}%</td>
                            <td>{{ number_format((float) $hsn->gst / 2, 2) }}%</td>
                            <td>{{ number_format((float) $hsn->gst, 2) }}%</td>
                            <td>{{ $hsn->short_name }}</td>
                            <td>
                                <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                <!-- <button class="btn btn-primary">Message</button> -->
                                <div class="flex gap-2 justify-content-left">
                                    <a href="{{ route('hsn_codes.show', $hsn->id) }}" class="btn btn-primary mr-1 mb-2">
                                        View
                                        {{-- {{ dd($hsn->id) }} --}}
                                    </a>
                                    <form action="{{ route('hsn_codes.destroy', $hsn->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this hsn?');"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE') <!-- Add this line -->
                                        <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                    </form>
                                    <a href="{{ route('hsn_codes.edit', $hsn->id) }}" class="btn btn-primary mr-1 mb-2">
                                        Edit
                                        {{-- {{ dd($hsn->id) }} --}}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($hsns instanceof \Illuminate\Pagination\LengthAwarePaginator && $hsns->hasPages())
                {{-- Pagination --}}
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Showing {{ $hsns->firstItem() }} to {{ $hsns->lastItem() }} of
                        {{ $hsns->total() }} entries
                    </div>
                    <div class="pagination-nav">
                        <nav role="navigation" aria-label="Pagination Navigation">
                            <ul class="pagination">
                                {{-- Previous Page Link --}}
                                @if ($hsns->onFirstPage())
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link">‹</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $hsns->previousPageUrl() }}" rel="prev">‹</a>
                                    </li>
                                @endif

                                {{-- Page Numbers --}}
                                @for ($i = 1; $i <= $hsns->lastPage(); $i++)
                                    @if ($i == $hsns->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $i }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $hsns->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Next Page Link --}}
                                @if ($hsns->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $hsns->nextPageUrl() }}" rel="next">›</a>
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



            <!-- END: Users Layout -->
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
            display: none;
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
