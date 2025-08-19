@extends('app')

@section('content')
<div class="content">
    <h2 class="text-lg font-medium mb-4">Ledger Purchase Records for {{ $ledger->party_name ?? 'Ledger' }}</h2>

    <div class="intro-y box p-5 mt-4">
        <div class="overflow-x-auto">
            @if($purchases->isEmpty())
                <p>No purchase records found for this ledger.</p>
            @else
                @php
                    $totalAmount = $purchases->sum('amount');
                @endphp

                <table class="table table-bordered table-striped">
                    <thead class="font-bold text-white bg-primary">
                        <tr>
                            <th>#</th>
                            <th>Bill Date</th>
                            <th>Bill No</th>
                            <th>Product</th>
                            <th>Quantity (Box)</th>
                            <th>Quantity (Pcs)</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $index => $purchase)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($purchase->bill_date)->format('Y-m-d') }}
                                </td>
                                <td class="whitespace-nowrap">{{ $purchase->bill_no }}</td>
                                <td>{{ $purchase->product }}</td>
                                <td>{{ $purchase->box }}</td>
                                <td>{{ $purchase->pcs }}</td>
                                <td class="text-end">{{ number_format($purchase->p_rate, 2) }}</td>
                                <td class="text-end">{{ $purchase->discount }}%</td>
                                <td class="text-end">{{ number_format($purchase->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold">
                            <td colspan="8" class="text-end">Total</td>
                            <td class="text-end">{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
