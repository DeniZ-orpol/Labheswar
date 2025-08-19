@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Hsn Codes
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
                <a href="{{ Route('hsn_codes.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add New Hsn
                    Code</a>
                <div class="input-form ml-auto">
                    <form method="GET" action="{{ route('hsn_codes.index') }}" class="flex gap-2">
                        <input type="text" name="search" id="search-hsn" placeholder="Search by name"
                            value="{{ request('search') }}" class="form-control flex-1">
                        <button type="submit" class="btn btn-primary shadow-md btn-hover">Search</button>
                    </form>
                </div>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <div class="intro-y col-span-12 mt-5">
                <div id="scrollable-table" style="max-height: calc(100vh - 200px); overflow-y: auto; border: 1px solid #ddd;">
                    <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                        <thead style="position: sticky; top: 0; z-index: 10;">
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
                        <tbody id="hsn-data">
                            @include('hsn.rows', ['page' => 1])
                        </tbody>
                    </table>
                </div>
                <div id="loading" style="display: none; text-align: center; padding: 10px;">
                    <p>Loading more hsns...</p>
                </div>
            </div>



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

        let page = 1;
        let loading = false;
        let currentSearch = '';
        let noMoreData = false;

        const scrollContainer = document.getElementById('scrollable-table');
        const tableBody = document.getElementById('hsn-data');
        const loadingIndicator = document.getElementById('loading');
        const searchInput = document.getElementById('search-hsn'); // Make sure you have this input
        let searchTimer;

        function loadData(pageToLoad, append = false) {
            if (loading || noMoreData) return;
            loading = true;
            loadingIndicator.style.display = 'block';

            let url = new URL(window.location.href);
            url.searchParams.set('page', pageToLoad);
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                loading = false;
                loadingIndicator.style.display = 'none';

                if (!html.trim()) {
                    noMoreData = true;
                    return;
                }

                if (append) {
                    tableBody.insertAdjacentHTML('beforeend', html);
                } else {
                    tableBody.innerHTML = html;
                }
            })
            .catch(error => {
                console.error("Error loading data:", error);
                loading = false;
                loadingIndicator.style.display = 'none';
            });
        }

        scrollContainer.addEventListener('scroll', function () {
            const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
            const scrollHeight = scrollContainer.scrollHeight;

            if (scrollBottom >= scrollHeight - 100 && !loading && !noMoreData) {
                page++;
                loadData(page, true);
            }
        });

        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    currentSearch = searchInput.value.trim();
                    page = 1;
                    noMoreData = false;
                    loadData(page, false); // reset results
                }, 300);
            });
        }
    </script>
@endpush
