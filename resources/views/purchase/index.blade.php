@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Purchase
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('purchase.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Make New
                    Purchase</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <div class="intro-y col-span-12 overflow-auto">
                <table id="DataTable" class="display table table-bordered w-full">
                    <thead>
                        <tr class="bg-primary font-bold text-white">
                            <th>#</th>
                            <th>Party Name</th>
                            <th>Bill Date</th>
                            <th>Delivery Date</th>
                            <th>GST</th>
                            <th>Total</th>
                            <th style="TEXT-ALIGN: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($purchaseReceipt && $purchaseReceipt->count())
                            @foreach ($purchaseReceipt as $purchaseRec)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $purchaseRec->purchaseParty->party_name }}</td>
                                    <td>{{ $purchaseRec->bill_date }}</td>
                                    <td>{{ $purchaseRec->delivery_date }}</td>
                                    <td>{{ $purchaseRec->gst_status }}</td>
                                    <td>{{ $purchaseRec->total_amount }}</td>
                                    <td>
                                        <div class="flex gap-2 justify-content-left">
                                            <form action="{{ route('purchase.destroy', $purchaseRec->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this role?');"
                                                style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                            </form>
                                            <a href="{{ route('purchase.edit', $purchaseRec->id) }}"
                                                class="btn btn-primary mr-1 mb-2">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">No Purchase found.</td>
                            </tr>
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
