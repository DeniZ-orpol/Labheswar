@extends('app')
@section('content')
    <!-- BEGIN: Content -->
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Users
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <a href="{{ Route('product.create') }}" class="btn btn-primary shadow-md mr-2 btn-hover">Add Product</a>
            </div>

            <!-- BEGIN: Users Layout -->
            <!-- DataTable: Add class 'datatable' to your table -->
            <table id="DataTable" class="display table table-bordered intro-y col-span-12">
                <thead>
                    <tr class="bg-primary font-bold text-white">
                        <th>Id</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>HSN</th>
                        <th>MRP</th>
                        {{-- <th>Status</th> --}}
                        {{-- <th>Edit</th>
                        <th>Delete</th> --}}
                        <th style="TEXT-ALIGN: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($products && $products->count())
                        @foreach ($products as $product)
                            <tr>
                                {{-- {{ dd($product) }} --}}
                                <td> {{ $product->id }} </td>
                                <td> {{ $product->product_name }} </td>
                                <td>{{ $product->category->name }}</td>
                                <td>{{ $product->hsnCode->hsn_code }}</td>
                                <td>{{ $product->mrp }}</td>
                                {{-- <td>{{ $user->email }}</td>
                                <td style="TEXT-ALIGN: left;">{{ $user->mobile }}</td>
                                <td>{{ $user->dob }}</td>
                                <td>{{ $user->role->role_name ?? '-' }}</td>
                                <td>{{ $user->branch_name ?? '-' }}</td> --}}
                                <td>
                                    <!-- Add buttons for actions like 'View', 'Edit' etc. -->
                                    <!-- <button class="btn btn-primary">Message</button> -->
                                    <div class="flex gap-2 justify-content-left">
                                        <a href="#"
                                            class="btn btn-primary mr-1 mb-2">View
                                        </a>
                                        <form
                                            action="#"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this product?');"
                                            style="display: inline-block;">
                                            @csrf
                                            @method('DELETE') <!-- Add this line -->
                                            <button type="submit" class="btn btn-danger mr-1 mb-2">Delete</button>
                                        </form>
                                        <a href="#"
                                            class="btn btn-primary mr-1 mb-2">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No Products found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- END: Users Layout -->
        </div>
    </div>
@endsection
