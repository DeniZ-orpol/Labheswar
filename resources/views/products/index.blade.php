@extends('app')

@section('content')
    @php
        $isPaginated = method_exists($products, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Products
        </h2>
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
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('products.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Product</a>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label for="excel_file">Import Products (Excel):</label>
                    <input type="file" name="excel_file" required accept=".csv, .xlsx, .xls">
                    <button type="submit" class="btn btn-primary shadow-md">Import</button>
                </form>
                <a href="{{ route('products.export') }}" class="btn btn-success shadow-md ml-2">Export</a>
            </div>
            <div class="intro-y col-span-12">
                <div class="input-form ">
                    <form method="GET" action="{{ route('products.index') }}" class="flex gap-2">

                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ isset($categoryId) && $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="company_id" class="form-control">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ isset($companyId) && $companyId == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="hsn_code_id" class="form-control">
                            <option value="">All HSN Codes</option>
                            @foreach ($hsnCodes as $hsnCode)
                                <option value="{{ $hsnCode->id }}"
                                    {{ isset($hsnCodeId) && $hsnCodeId == $hsnCode->id ? 'selected' : '' }}>
                                    {{ $hsnCode->hsn_code }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="search" id="search-product" placeholder="Search by name/barcode"
                            value="{{ request('search') }}" class="form-control">

                        <button type="submit" class="btn btn-primary shadow-md btn-hover">Search</button>
                    </form>
                </div>
            </div>

            <div class="intro-y col-span-12">
                <div id="product-results">
                    @include('products.product-list', ['products' => $products])
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

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #ffffff;
            color: #374151;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .page-btn:hover:not(.disabled) {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            color: #111827;
        }

        .page-btn.disabled {
            color: #9ca3af;
            background-color: #f9fafb;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }

        .page-input-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 16px;
        }

        .page-label,
        .page-total {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .page-input {
            width: 60px;
            height: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            background-color: #ffffff;
            color: #374151;
            transition: all 0.15s ease;
        }

        .page-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .page-input:hover {
            border-color: #d1d5db;
        }

        /* Remove spinner arrows from number input */
        .page-input::-webkit-outer-spin-button,
        .page-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .page-input[type=number] {
            -moz-appearance: textfield;
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

            .pagination-nav {
                order: 1;
            }

            .page-input-container {
                margin: 0 8px;
            }

            .page-input {
                width: 50px;
            }
        }

        /* Sticky table header */
        /* Remove sticky header CSS as we will handle it with JS */
        .intro-y.col-span-12.overflow-auto {
            max-height: 70vh;
            /* Adjust this value as needed */
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        /* Sticky table header */
        #DataTable thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: rgb(var(--color-primary) / var(--tw-bg-opacity));
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Ensure table takes full width */
        #DataTable {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        /* Add border to table cells for better visibility */
        #DataTable th {
            border: 1px solid #e5e7eb;
            padding: 12px 8px;
        }

        #DataTable td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
        }

        #DataTable tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements (match your blade HTML)
            const container = document.getElementById('productDataTable'); // outer scrollable container
            const loadingElem = document.getElementById('loading');
            const searchInput = document.getElementById('search-product');
            const categorySelect = document.querySelector('select[name="category_id"]');
            const companySelect = document.querySelector('select[name="company_id"]');
            const hsnCodeSelect = document.querySelector('select[name="hsn_code_id"]');
            const exportBtn = document.querySelector('a[href="{{ route('products.export') }}"]');

            // State
            let nextPageUrl = @json($products->nextPageUrl()); // null or string
            if (!nextPageUrl) nextPageUrl = null;
            let isLoading = false;

            // Helper: build current filters
            function getCurrentFilters() {
                const params = new URLSearchParams();
                if (searchInput && searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());
                if (categorySelect && categorySelect.value !== '') params.set('category_id', categorySelect.value);
                if (companySelect && companySelect.value !== '') params.set('company_id', companySelect.value);
                if (hsnCodeSelect && hsnCodeSelect.value !== '') params.set('hsn_code_id', hsnCodeSelect.value);
                return params;
            }

            // Update export link (optional)
            function updateExportLink() {
                if (!exportBtn) return;
                const base = exportBtn.href.split('?')[0];
                const qs = getCurrentFilters().toString();
                exportBtn.href = qs ? `${base}?${qs}` : base;
            }

            // Replace table/container HTML from AJAX response and update nextPageUrl
            function replaceTableHtmlFromResponse(html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;

                // server returns entire #productDataTable (recommended) — replace container innerHTML
                const newProductContainer = tempDiv.querySelector('#productDataTable');
                if (newProductContainer && container) {
                    container.innerHTML = newProductContainer.innerHTML;
                } else {
                    // fallback: server returns just #DataTable fragment
                    const newDataTable = tempDiv.querySelector('#DataTable');
                    const existingDataTable = container ? container.querySelector('#DataTable') : null;
                    if (newDataTable && existingDataTable) {
                        existingDataTable.outerHTML = newDataTable.outerHTML;
                    } else {
                        console.warn('AJAX response did not contain #productDataTable or #DataTable');
                    }
                }

                // Pick up new pagination next-link (if present)
                const nextLink = tempDiv.querySelector(
                    '.page-btn.next-btn:not(.disabled), .page-btn.next-btn[href]');
                nextPageUrl = nextLink ? nextLink.href : null;
            }

            // Fetch products (apply filters, replace the list, reset scroll)
            function fetchProducts() {
                const qs = getCurrentFilters().toString();
                const url = `{{ route('products.index') }}${qs ? ('?' + qs) : ''}`;

                isLoading = true;
                if (loadingElem) loadingElem.style.display = 'block';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        replaceTableHtmlFromResponse(html);
                        updateExportLink();
                        // Reset container scroll to top so user sees first page of filtered results
                        if (container) container.scrollTop = 0;
                    })
                    .catch(err => console.error('fetchProducts error:', err))
                    .finally(() => {
                        isLoading = false;
                        if (loadingElem) loadingElem.style.display = 'none';
                    });
            }

            // Append next page rows (infinite scroll)
            function loadMore() {
                if (!nextPageUrl || isLoading) return;

                // Combine the nextPageUrl's page param with current filters
                const urlObj = new URL(nextPageUrl, window.location.origin);
                const params = getCurrentFilters();
                if (urlObj.searchParams.has('page')) params.set('page', urlObj.searchParams.get('page'));
                const finalUrl = `${urlObj.origin}${urlObj.pathname}?${params.toString()}`;

                isLoading = true;
                if (loadingElem) loadingElem.style.display = 'block';

                fetch(finalUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;

                        const newRowsEl = tempDiv.querySelector('#DataTable tbody');
                        const currentTbody = container ? container.querySelector('#DataTable tbody') : document
                            .querySelector('#DataTable tbody');

                        if (newRowsEl && currentTbody) {
                            currentTbody.insertAdjacentHTML('beforeend', newRowsEl.innerHTML);
                        } else {
                            // If tbody not found, replace full table/container (failsafe)
                            replaceTableHtmlFromResponse(html);
                        }

                        // Update pagination block if server returned it
                        const newPagination = tempDiv.querySelector('.pagination-wrapper');
                        if (newPagination) {
                            const existingPagination = document.querySelector('.pagination-wrapper');
                            if (existingPagination) existingPagination.outerHTML = newPagination.outerHTML;
                            else {
                                // append after table if needed
                                const table = document.querySelector('#DataTable');
                                if (table) table.insertAdjacentHTML('afterend', newPagination.outerHTML);
                            }
                        }

                        // Update next page url
                        const nextLink = tempDiv.querySelector(
                            '.page-btn.next-btn:not(.disabled), .page-btn.next-btn[href]');
                        nextPageUrl = nextLink ? nextLink.href : null;
                    })
                    .catch(err => console.error('loadMore error:', err))
                    .finally(() => {
                        isLoading = false;
                        if (loadingElem) loadingElem.style.display = 'none';
                    });
            }

            // Debounce helper for search input
            function debounce(fn, wait = 300) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), wait);
                };
            }

            // Attach filter event listeners
            if (searchInput) searchInput.addEventListener('input', debounce(fetchProducts, 350));
            if (categorySelect) categorySelect.addEventListener('change', fetchProducts);
            if (companySelect) companySelect.addEventListener('change', fetchProducts);
            if (hsnCodeSelect) hsnCodeSelect.addEventListener('change', fetchProducts);

            // Scroll handler — prefer container scroll, fallback to window scroll
            const SCROLL_THRESHOLD = 150; // px from bottom
            if (container) {
                container.addEventListener('scroll', function() {
                    if (isLoading || !nextPageUrl) return;
                    const pos = container.scrollTop + container.clientHeight;
                    if (pos >= container.scrollHeight - SCROLL_THRESHOLD) loadMore();
                });
            } else {
                window.addEventListener('scroll', function() {
                    if (isLoading || !nextPageUrl) return;
                    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight -
                        SCROLL_THRESHOLD) loadMore();
                });
            }

            // expose goToPage if your pagination uses it elsewhere
            window.goToPage = function(page) {
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            };

            // Ensure export link is correct on initial load
            updateExportLink();
        });
    </script>
@endpush
