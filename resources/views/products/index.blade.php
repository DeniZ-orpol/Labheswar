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
                                        <a href="{{ route('products.edit', $product->id) }}"
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
                                    <td colspan="7"
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
                                            <a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">›</a>
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
