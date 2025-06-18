@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Inventory List</h2>
            <a href="{{ Route('inventory.create') }}" class="btn btn-primary shadow-md btn-hover ml-auto">
                Add Open Stock
            </a>
        </div>


        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table id="inventoryTable" class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            {{-- <th>Status</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $index => $inventory)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if ($inventory->product->image)
                                        <img src="{{ asset('storage/' . $inventory->product->image) }}" alt="Image"
                                            class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <span class="text-gray-400 italic">No Image</span>
                                    @endif
                                </td>
                                <td>{{ $inventory->product->product_name }}</td>
                                {{-- {{dd($inventory)}} --}}
                                <td class="{{ $inventory->quantity < 0 ? 'text-red-500 font-bold' : '' }}">
                                    {{ $inventory->quantity }}
                                </td>
                                <td>{{ $inventory->product->unit_types ?? '-' }}</td>
                                {{-- <td>
                                    <span class="text-success flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Active
                                    </span>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- DataTables JS -->
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'csv', 'pdf'
                ]
            });
        });
    </script>
@endpush
