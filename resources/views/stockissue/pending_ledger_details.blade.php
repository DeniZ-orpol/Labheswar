@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Pending Stock Details for Ledger
        </h2>

        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12">
                <div class="box p-5 mb-5 border border-gray-300 rounded-md shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="table table-bordered w-full">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="px-4 py-2">Product</th>
                                    <th class="px-4 py-2">MRP</th>
                                    <th class="px-4 py-2">Sale Rate</th>
                                    <th class="px-4 py-2">Quantity</th>
                                    <th class="px-4 py-2">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mergedProductList as $item)
                                    <tr>
                                        <td class="px-4 py-2">{{ $item['product'] }}</td>
                                        <td class="px-4 py-2">{{ number_format($item['mrp'], 2) }}</td>
                                        <td class="px-4 py-2">{{ number_format($item['sale_rate'], 2) }}</td>
                                        <td class="px-4 py-2">{{ $item['qty'] }}</td>
                                        <td class="px-4 py-2">{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="7" class="px-4 py-2"> No pending stock issues found for this ledger.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
