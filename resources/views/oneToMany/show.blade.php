@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
        One To Many Conversion Details
    </h2>
    
    <div class="mt-5">
        <div>
            <strong>Ledger Name:</strong> {{ $oneToMany->ledger->party_name ?? 'N/A' }}
        </div>
        <div>
            <strong>Raw Product Name:</strong> {{ $oneToMany->rawItem->product_name ?? 'N/A' }}
        </div>
        <div>
            <strong>Quantity Used:</strong> {{ $oneToMany->qty ?? 'N/A' }}
        </div>
        <div>
            <strong>Date:</strong> {{ $oneToMany->date ? \Carbon\Carbon::parse($oneToMany->date)->format('d-m-Y') : 'N/A' }}
        </div>
        <div>
            <strong>Entry No:</strong> {{ $oneToMany->entry_no ?? 'N/A' }}
        </div>
        <div class="mt-4">
            <h3  class="intro-y text-lg font-medium mt-10 heading">Converse Products</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        {{-- <th>Amount</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($oneToMany->item_to_create as $item)
                        <tr>
                            <td>{{ $item->product_name ?? 'Unknown Product' }}</td>
                            <td>{{ $item->qty ?? 'N/A' }}</td>
                            {{-- <td>{{ $item['amount'] }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('one-to-many.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('one-to-many.edit', $oneToMany->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection
