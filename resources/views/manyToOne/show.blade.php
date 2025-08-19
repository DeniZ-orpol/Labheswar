@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
        Many To One Conversion Details
    </h2>
    
    <div class="mt-5">
        <div>
            <strong>Ledger Name:</strong> {{ $manyToOne->ledger->party_name ?? 'N/A' }}
        </div>
        <div>
            <strong>Produced Product Name:</strong> {{ $manyToOne->product->product_name ?? 'N/A' }}
        </div>
        <div>
            <strong>Quantity Created:</strong> {{ $manyToOne->qty ?? 'N/A' }}
        </div>
        <div>
            <strong>Date:</strong> {{ $manyToOne->date ? \Carbon\Carbon::parse($manyToOne->date)->format('d-m-Y') : 'N/A' }}
        </div>
        <div>
            <strong>Entry No:</strong> {{ $manyToOne->entry_no ?? 'N/A' }}
        </div>
        <div class="mt-4">
            <h3  class="intro-y text-lg font-medium mt-10 heading">Raw Products</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        {{-- <th>Amount</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($manyToOne->raw_item as $item)
                        <tr>
                            <td>{{ $item->product_name ?? 'Unknown Product' }}</td>
                            <td>{{ $item->qty }}</td>
                            {{-- <td>{{ $item['amount'] }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('many-to-one.index') }}" class="btn btn-secondary">Back to List</a>
            {{-- <a href="{{ route('many-to-one.edit', $oneToMany->id) }}" class="btn btn-primary">Edit</a> --}}
        </div>
    </div>
</div>
@endsection
