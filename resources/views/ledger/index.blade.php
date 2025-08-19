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
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('purchase.party.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Create New
                Ledger</a>
            <div class="input-form ml-auto">
                <form method="GET" action="{{ route('ledger.index') }}" class="flex gap-2">
                    <input type="text" name="search" id="search" placeholder="Search by name" value=""
                        class="form-control flex-1">
                    <button type="submit" class="btn btn-primary shadow-md btn-hover">Search</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div id="success-alert" class="alert alert-success"
                style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="alert alert-danger"
                style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="intro-y box p-5 mt-4">
            <div class="overflow-x-auto">
                <div id="scrollable-table"
                    style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                        <thead style="position: sticky; top: 0; z-index: 10;" class="font-bold text-white bg-primary">
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

                        <tbody id="data">
                            @include('ledger.rows', ['page' => 1])
                        </tbody>
                    </table>
                </div>
                <div id="loading" style="display: none; text-align: center; padding: 10px;">
                    <p>Loading more Ledgers...</p>
                </div>
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
document.addEventListener('DOMContentLoaded', function () {
    const loadingIndicator = document.getElementById('loading');
    const searchInput = document.getElementById('search');
    const scrollContainer = document.getElementById('scrollable-table');
    const dataContainer = document.getElementById('data');

    let page = 1;
    let loading = false;
    let currentSearch = '';

    // Search input with debounce
    if (searchInput) {
        let searchTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentSearch = this.value.trim();
                page = 1;
                loadMoreData(page, false);
            }, 500);
        });
    }

    // Ledger group change
    document.getElementById('ledger_group')?.addEventListener('change', function () {
        const selectedValue = this.value;
        const baseUrl = "{{ url()->current() }}";
        const newUrl = selectedValue
            ? `${baseUrl}?ledger_group=${encodeURIComponent(selectedValue)}`
            : baseUrl;
        window.location.href = newUrl;
    });

    // Infinite scroll
    scrollContainer.addEventListener('scroll', function () {
        const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
        const scrollHeight = scrollContainer.scrollHeight;
        if (scrollBottom >= scrollHeight - 100 && !loading) {
            page++;
            loadMoreData(page, true);
        }
    });

    // Load data function
    function loadMoreData(page, append = true) {
        loading = true;
        loadingIndicator.style.display = 'block';

        let url = `?page=${page}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(data => {
                if (data.trim().length === 0) {
                    loadingIndicator.style.display = 'none';
                    return;
                }
                if (append) {
                    dataContainer.insertAdjacentHTML('beforeend', data);
                } else {
                    dataContainer.innerHTML = data;
                }
                loadingIndicator.style.display = 'none';
                loading = false;
            })
            .catch(error => {
                console.error("Error fetching more data:", error);
                loading = false;
            });
    }

    // Initial load
    loadMoreData(page, false);
});
</script>

@endpush
