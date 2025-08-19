@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Pending Stock
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 overflow-auto">
                <table class="table table-bordered w-full">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Ledger</th>
                            <th class="px-4 py-2">Total Pending Amount</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingStock as $ledgerId => $stock)
                            <tr>
                                <td class="px-4 py-2">{{ $loop->index + 1 }}</td>
                                <td class="px-4 py-2">{{  $stock->first()->getRelation('ledger')->party_name ?? 'Unknown Ledger' }}</td>
                                <td class="px-4 py-2">{{ number_format($pendingTotals[$ledgerId] ?? 0, 2) }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('stockissue.pendingLedgerDetails', $ledgerId) }}" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                            
                        @empty
                            <tr class="text-center">
                                <td colspan="4" class="px-4 py-2"> No pending stock found.</td> 
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
