@extends('app')

@section('content')
    @php
        $isPaginated = method_exists($ledgers, 'links');
    @endphp
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Ledger</h2>
            <div class="input-form col-span-3 ml-auto">
                <label for="ledger_group" class="form-label w-full flex flex-col sm:flex-row">Filter Ledger Group</label>
                <select id="ledger_group" name="ledger_group" class="form-control field-new">
                    <option value="" {{ $selectedLedgerGroup == '' ? 'selected' : '' }}>All</option>
                    <option value="SUNDRY DEBTORS" {{ $selectedLedgerGroup == 'SUNDRY DEBTORS' ? 'selected' : '' }}>SUNDRY
                        DEBTORS</option>
                    <option value="SUNDRY DEBTORS (E-COMMERCE)"
                        {{ $selectedLedgerGroup == 'SUNDRY DEBTORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY DEBTORS
                        (E-COMMERCE)</option>
                    <option value="SUNDRY DEBTORS (FIELD STAFF)"
                        {{ $selectedLedgerGroup == 'SUNDRY DEBTORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY DEBTORS (FIELD
                        STAFF)</option>
                    <option value="SUNDRY CREDITORS" {{ $selectedLedgerGroup == 'SUNDRY CREDITORS' ? 'selected' : '' }}>
                        SUNDRY CREDITORS</option>
                    <option value="SUNDRY CREDITORS (E-COMMERCE)"
                        {{ $selectedLedgerGroup == 'SUNDRY CREDITORS (E-COMMERCE)' ? 'selected' : '' }}>SUNDRY CREDITORS
                        (E-COMMERCE)</option>
                    <option value="SUNDRY CREDITORS (EXPENSES PAYABLE)"
                        {{ $selectedLedgerGroup == 'SUNDRY CREDITORS (EXPENSES PAYABLE)' ? 'selected' : '' }}>SUNDRY
                        CREDITORS (EXPENSES PAYABLE)</option>
                    <option value="SUNDRY CREDITORS (FIELD STAFF)"
                        {{ $selectedLedgerGroup == 'SUNDRY CREDITORS (FIELD STAFF)' ? 'selected' : '' }}>SUNDRY CREDITORS
                        (FIELD STAFF)</option>
                    <option value="SUNDRY CREDITORS (MANUFACTURERS)"
                        {{ $selectedLedgerGroup == 'SUNDRY CREDITORS (MANUFACTURERS)' ? 'selected' : '' }}>SUNDRY CREDITORS
                        (MANUFACTURERS)</option>
                    <option value="SUNDRY CREDITORS (SUPPLIERS)"
                        {{ $selectedLedgerGroup == 'SUNDRY CREDITORS (SUPPLIERS)' ? 'selected' : '' }}>SUNDRY CREDITORS
                        (SUPPLIERS)</option>
                </select>
            </div>
        </div>

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone No</th>
                            <th>GST No</th>
                            <th>State</th>
                            <th>Balancing Method</th>
                            <th>Ledger Group</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ledgers as $index => $ledger)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $ledger->party_name }}</td>
                                <td>{{ $ledger->mobile_no }}</td>
                                <td>{{ $ledger->gst_number }}</td>
                                <td>{{ $ledger->state }}</td>
                                <td>{{ $ledger->balancing_method }}</td>
                                <td>{{ $ledger->ledger_group }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                        <a href="{{ route('purchase.party.edit', $ledger->id) }}"
                                            class="btn btn-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="8">No Ledger Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Show pagination only for branch users (when data is paginated) -->
                @if ($isPaginated)
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Showing {{ $ledgers->firstItem() }} to {{ $ledgers->lastItem() }} of
                            {{ $ledgers->total() }} entries
                        </div>
                        <div class="pagination-nav">
                            <nav role="navigation" aria-label="Pagination Navigation">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($ledgers->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">‹</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $ledgers->previousPageUrl() }}"
                                                rel="prev">‹</a>
                                        </li>
                                    @endif

                                    {{-- Page Numbers with Compact Logic --}}
                                    @php
                                        $currentPage = $ledgers->currentPage();
                                        $lastPage = $ledgers->lastPage();
                                        $showEllipsis = $lastPage > 7; // Show ellipsis if more than 7 pages
                                    @endphp

                                    @if (!$showEllipsis)
                                        {{-- Show all pages if 7 or fewer --}}
                                        @for ($i = 1; $i <= $lastPage; $i++)
                                            @if ($i == $currentPage)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $ledgers->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor
                                    @else
                                        {{-- Compact pagination with ellipsis --}}

                                        {{-- Always show first page --}}
                                        @if ($currentPage != 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $ledgers->url(1) }}">1</a>
                                            </li>
                                        @else
                                            <li class="page-item active">
                                                <span class="page-link">1</span>
                                            </li>
                                        @endif

                                        {{-- Show second page if current page is not near the beginning --}}
                                        @if ($currentPage > 4)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $ledgers->url(2) }}">2</a>
                                            </li>
                                        @endif

                                        {{-- Left ellipsis --}}
                                        @if ($currentPage > 4)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif

                                        {{-- Pages around current page --}}
                                        @php
                                            $start = max(2, $currentPage - 1);
                                            $end = min($lastPage - 1, $currentPage + 1);

                                            // Adjust range if we're near the beginning
                                            if ($currentPage <= 3) {
                                                $start = 2;
                                                $end = min($lastPage - 1, 4);
                                            }

                                            // Adjust range if we're near the end
                                            if ($currentPage >= $lastPage - 2) {
                                                $start = max(2, $lastPage - 3);
                                                $end = $lastPage - 1;
                                            }
                                        @endphp

                                        @for ($i = $start; $i <= $end; $i++)
                                            @if ($i == $currentPage)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $ledgers->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Right ellipsis --}}
                                        @if ($currentPage < $lastPage - 3)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif

                                        {{-- Show second-to-last page if current page is not near the end --}}
                                        @if ($currentPage < $lastPage - 3)
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $ledgers->url($lastPage - 1) }}">{{ $lastPage - 1 }}</a>
                                            </li>
                                        @endif

                                        {{-- Always show last page --}}
                                        @if ($currentPage != $lastPage)
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $ledgers->url($lastPage) }}">{{ $lastPage }}</a>
                                            </li>
                                        @else
                                            <li class="page-item active">
                                                <span class="page-link">{{ $lastPage }}</span>
                                            </li>
                                        @endif
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($ledgers->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $ledgers->nextPageUrl() }}" rel="next">›</a>
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

@push('scripts')
    <script>
        document.getElementById('ledger_group').addEventListener('change', function() {
            const selectedValue = this.value;
            const baseUrl = "{{ url()->current() }}"; // current route without query params

            // Build URL with query param
            const newUrl = selectedValue ?
                `${baseUrl}?ledger_group=${encodeURIComponent(selectedValue)}` :
                baseUrl;

            window.location.href = newUrl; // Redirect
        });
    </script>
@endpush
