<div id="productDataTable" class="table-responsive" style="height: calc(100vh - 150px); overflow-y: auto;">
    <table id="DataTable" class="display table table-bordered w-full">
        <thead class="position-sticky top-0" style="z-index: 10;">
            <tr class="bg-primary text-white text-center">
                <th>#</th>
                <th>Image</th>
                <th>Product</th>
                <th>Category</th>
                <th>HSN</th>
                <th>MRP</th>
                <th>Sale Rate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>
                        {{ method_exists($products, 'links') ? ($products->currentPage() - 1) * $products->perPage() + $loop->iteration : $loop->iteration }}
                    </td>
                    <td>
                        @if ($product->image)
                            <img src="{{ asset($product->image) }}" alt="Product Image" width="80">
                        @else
                            No Image
                        @endif
                    </td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->hsnCode->hsn_code ?? '-' }}</td>
                    <td>{{ $product->mrp }}</td>
                    <td>{{ $product->sale_rate_a ?? '-' }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary">View</a>
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
                    <td colspan="8" class="text-center">No Products Found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div id="loading" style="display: none; text-align: center; padding: 20px;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    @if (method_exists($products, 'links'))
        <!-- <div class="mt-4">
            {!! $products->links() !!}
        </div> -->
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
