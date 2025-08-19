@extends('app')

@section('content')
    <div class="content">
        <div class="intro-y grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-12">
                <div class="box p-5 rounded-md">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium text-base truncate">Category Details</div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <!-- LEFT COLUMN -->
                        <div class="p-5 rounded-md bg-slate-100">
                            <div class="font-medium text-lg mb-3">Category Information</div>
                            <p><strong>Name:</strong> {{ $category->name }}</p>
                            <p><strong>Type:</strong> {{ $category->type ?? "N/A" }}</p>
                            @if ($category->image)
                                <div class="mt-5">
                                    {{-- <div class="font-medium text-lg mb-2">category Image</div> --}}
                                    <img src="{{ asset($category->image) }}" alt="category Image"
                                        style="max-width: 250px; border-radius: 10px;" />
                                </div>
                            @endif
                        </div>

                    </div>

                    <!-- Image Preview -->


                    <!-- Navigation -->
                    <div class="mt-5">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back to Category List</a>
                        <a href="{{ route('categories.edit', $category->id) }}"
                            class="btn btn-primary ml-2">Edit Category</a>
                    </div>

                    <!-- Products List -->
                    <div class="mt-10">
                        <h2 class="font-medium text-lg mb-5">Products in this Category</h2>
                        <table class="table-auto w-full border border-gray-300">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th width="50%" class="border border-gray-300 px-4 py-2 text-left">Product Name</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Barcode</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($category->products as $product)
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">{{ $product->product_name }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $product->barcode ?? '-' }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="3" class="border border-gray-300 px-4 py-2">No products found in this category.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
