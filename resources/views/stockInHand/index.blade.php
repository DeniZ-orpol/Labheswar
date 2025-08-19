@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Stock In Hand List</h2>
            <a href="{{ route('stock-in-hand.create') }}"
                class="btn btn-primary shadow-md btn-hover ml-auto">Add Stock In Hand</a>
        </div>
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

        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Qty in Hand</th>
                            <th>Qty in Sold</th>
                            <th>Inventory Value</th>
                            <th>Sale Value</th>
                            <th>Available Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stocks as $index => $stock)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $stock->product->product_name }}</td>
                                <td>{{ $stock->price }}</td>
                                <td>{{ $stock->qty_in_hand ?? '-' }}</td>
                                <td>{{ $stock->qty_sold ?? '-' }}</td>
                                <td>{{ $stock->inventory_value ?? '-' }}</td>
                                <td>{{ $stock->sale_value ?? '-' }}</td>
                                <td>{{ $stock->available_stock ?? '-' }}</td>
                                <td>{{ $stock->status == '1' ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                        <a href="{{ route('stock-in-hand.edit', $stock->id) }}" class="btn btn-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="10">No In Hand Stock Entry Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
