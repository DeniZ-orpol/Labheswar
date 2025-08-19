@extends('app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    @php
        $isPaginated = method_exists($categories, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Category List
        </h2>
        @if (session('success'))
            <div id="success-alert" class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif
    
        @if (session('error'))
            <div id="error-alert" class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('categories.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New Category</a>
                <div class="input-form ml-auto">
                    <form method="GET" action="{{ route('categories.index') }}" class="flex gap-2">
                        <input type="text" name="search" id="search-category" placeholder="Search by name"
                            value="{{ request('search') }}" class="form-control flex-1">
                        <button type="submit" class="btn btn-primary shadow-md btn-hover">Search</button>
                    </form>
                </div>
            </div>

            <!-- BEGIN: Categories Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
             <div class="intro-y col-span-12 ">
                <div id="scrollable-table" style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table  class="table table-bordered w-full" style="border-collapse: collapse;">
                        <thead style="position: sticky; top: 0; z-index: 10;">
                            <tr class="bg-primary font-bold text-white">
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Image</th>
                                <th style="TEXT-ALIGN: left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="category-data">
                            @include('categories.rows', ['page' => 1])
                        </tbody>
                    </table>
                </div>
                <!-- END: Categories Layout -->
                <div id="loading" style="display: none; text-align: center; padding: 10px;">
                    <p>Loading more categories...</p>
                </div>
            </div>

            <!-- END: Categories Layout -->
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
    let page = 1;
    let loading = false;
    let currentSearch = '';
    const categoryTableBody = document.getElementById('category-data');
    const loadingIndicator = document.getElementById('loading');
    const scrollContainer = document.getElementById('scrollable-table');
    const searchInput = document.getElementById('search-category');
    let searchTimer;

    // Initialize Sortable once and keep reusing
    let sortableInstance;
    function initSortable() {
        if (sortableInstance) sortableInstance.destroy();

        sortableInstance = new Sortable(categoryTableBody, {
            animation: 150,
            onEnd: function () {
                let order = [];
                categoryTableBody.querySelectorAll('tr').forEach((row, index) => {
                    order.push({
                        id: row.getAttribute('data-id'),
                        position: index + 1
                    });
                });

                fetch("{{ route('categories.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(res => res.json())
                .then(data => {
                    console.log(data.message);
                })
                .catch(console.error);
            }
        });
    }

    // Load data for given page and append or replace
    function loadData(pageToLoad, append = false) {
        if (loading) return;
        loading = true;
        loadingIndicator.style.display = 'block';

        // Build URL with page and search
        let url = `?page=${pageToLoad}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(data => {
            if (!data.trim()) {
                // No more data
                loadingIndicator.innerHTML = '';
                return;
            }

            if (append) {
                categoryTableBody.insertAdjacentHTML('beforeend', data);
            } else {
                categoryTableBody.innerHTML = data;
            }

            loadingIndicator.style.display = 'none';
            loading = false;

            initSortable();
        })
        .catch(err => {
            console.error("Error loading categories:", err);
            loadingIndicator.style.display = 'none';
            loading = false;
        });
    }

    // Search input keyup event with debounce
    searchInput.addEventListener('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentSearch = searchInput.value.trim();
            page = 1;
            loadData(page, false); // replace results with filtered data
        }, 300);
    });

    // Infinite scroll event
    scrollContainer.addEventListener('scroll', () => {
        const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
        const scrollHeight = scrollContainer.scrollHeight;

        if (scrollBottom >= scrollHeight - 100 && !loading) {
            page++;
            loadData(page, true); // append more results
        }
    });

    // Initial call to load sortable on page load
    document.addEventListener('DOMContentLoaded', () => {
        initSortable();
    });
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
