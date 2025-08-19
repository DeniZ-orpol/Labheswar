@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Pending Stock Receipts
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 overflow-auto">
                @if($ledgerData->count() > 0)
                    <table class="table table-bordered w-full">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Ledger Name</th>
                                <th class="px-4 py-2">Pending Stock Receipts</th>
                                <th class="px-4 py-2">Total Amount</th>
                                <th class="px-4 py-2">Total Receipt Price</th>
                                <th class="px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledgerData as $index => $ledger)
                                <tr>
                                    <td class="px-4 py-2">{{ $ledgerData->firstItem() + $index }}</td>
                                    <td class="px-4 py-2">{{ $ledger->getRelation('ledger')->party_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $ledger->pending_count }}</td>
                                    <td class="px-4 py-2">{{ number_format($ledger->total_amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($ledgerTotals[$ledger->ledger] ?? 0, 2) }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('stockreceipt.ledgerPendingDetails', ['ledger' => $ledger->ledger]) }}" class="btn btn-primary">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $ledgerData->links() }}
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">
                        No pending stock receipts found.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
