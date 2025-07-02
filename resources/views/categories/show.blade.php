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
                        {{-- <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary ml-2">Edit
                            Category</a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
