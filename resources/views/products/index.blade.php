@extends('app')

@section('content')
    @php
        $isPaginated = method_exists($products, 'links');
    @endphp
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Products
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('products.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Product</a>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label for="excel_file">Import Products (Excel):</label>
                    <input type="file" name="excel_file" required accept=".csv, .xlsx, .xls">
                    <button type="submit">Import</button>
                </form>
                <div class="input-form ml-auto">
                    <form method="GET" action="{{ route('products.index') }}" class="flex">
                        <input type="text" name="search" id="search-product" placeholder="Search by name/barcode"
                            value="{{ request('search') }}" class="form-control flex-1">
                        <button type="submit" class="btn btn-primary shadow-md btn-hover">Search</button>
                    </form>
                </div>
            </div>

            <div class="intro-y col-span-12 overflow-auto">
                <table id="DataTable" class="display table table-bordered w-full">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th>#</th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>HSN</th>
                            <th>MRP</th>
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
                                        {{-- <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image"
                                                width="80"> --}}
                                        <img src="{{ asset($product->image) }}" alt="Product Image" width="80">
                                    @else
                                        No Image
                                    @endif
                                </td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ $product->hsnCode->hsn_code ?? '-' }}</td>
                                <td>{{ $product->mrp }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('products.show', $product->id) }}"
                                            class="btn btn-primary">View</a>
                                        <a href="{{ route('products.edit', array_merge(['product' => $product->id], request()->only(['page', 'search']))) }}"
                                            class="btn btn-primary">Edit</a>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST"
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
                                <td colspan="7" class="text-center">No Products Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Show pagination only for branch users (when data is paginated) -->
                @if ($isPaginated)
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            @if ($products->total() > 0)
                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                                {{ $products->total() }} entries
                            @else
                                No entries found
                            @endif
                        </div>
                        <div class="pagination-nav">
                            <nav role="navigation" aria-label="Pagination Navigation">
                                <div class="pagination-controls">
                                    {{-- Previous Page Button --}}
                                    @if ($products->onFirstPage())
                                        <button class="page-btn prev-btn disabled" disabled>
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                                            </svg>
                                        </button>
                                    @else
                                        <a href="{{ $products->previousPageUrl() }}" class="page-btn prev-btn">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Page Input --}}
                                    <div class="page-input-container">
                                        <span class="page-label">Page</span>
                                        <input type="number" class="page-input" value="{{ $products->currentPage() }}"
                                            min="1" max="{{ $products->lastPage() }}"
                                            onkeypress="if(event.key === 'Enter') goToPage(this.value)">
                                        <span class="page-total">of {{ $products->lastPage() }}</span>
                                    </div>

                                    {{-- Next Page Button --}}
                                    @if ($products->hasMorePages())
                                        <a href="{{ $products->nextPageUrl() }}" class="page-btn next-btn">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                                            </svg>
                                        </a>
                                    @else
                                        <button class="page-btn next-btn disabled" disabled>
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
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
    </style>
@endpush

@push('scripts')
    <script>
        function goToPage(pageNumber) {
            const maxPage = {{ $isPaginated ? $products->lastPage() : 1 }};
            const currentUrl = new URL(window.location.href);

            // Validate page number
            pageNumber = parseInt(pageNumber);
            if (isNaN(pageNumber) || pageNumber < 1) {
                pageNumber = 1;
            } else if (pageNumber > maxPage) {
                pageNumber = maxPage;
            }

            // Update the input field with validated page number
            document.querySelector('.page-input').value = pageNumber;

            // Navigate to the page
            currentUrl.searchParams.set('page', pageNumber);
            window.location.href = currentUrl.toString();
        }

        // Auto-select input content when focused
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-product');
            const pageInput = document.querySelector('.page-input');
            if (pageInput) {
                pageInput.addEventListener('focus', function() {
                    this.select();
                });
            }

            // Auto-focus search input if there's a search parameter
            if (searchInput && new URLSearchParams(window.location.search).has('search')) {
                searchInput.focus();
            }

            // Handle Enter key in search input
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.closest('form').submit();
                    }
                });
            }
        });
    </script>
@endpush
