@extends('app')

@section('content')
<div class="content">
    <h2 class="intro-y text-lg font-medium mt-10 heading">
        Manufacturing Details
    </h2>

    <div class="mt-5">
        <div>
            <strong>Product Name:</strong> {{ $manufacturing->product->product_name ?? 'N/A' }}
        </div>
        <div>
            <strong>Quantity:</strong> {{ $manufacturing->qty }}
        </div>
        <div class="mt-4">
            <h3>Ingredients</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ingredientsWithProduct as $item)
                        <tr>
                            <td>{{ $item['product']->product_name ?? 'Unknown Product' }}</td>
                            <td>{{ $item['qty'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('manufacturing.edit', $manufacturing->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection
