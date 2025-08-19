@extends('app')

@section('content')
    <div class="content">
        <h2 class="intro-y text-lg font-medium mt-10 heading">
            Pending Stock Receipt Details for Ledger: {{ $ledger }}
        </h2>
        <div class="grid grid-cols-12 gap-6 mt-5 grid-updated">
            <div class="intro-y col-span-12 overflow-auto">
                @if(count($formulasData) > 0)
                    @php
                        $totalReceiptPrice = 0;
                    @endphp
                    @foreach($formulasData as $formulaId => $data)
                        @php
                            $formulaTotalPrice = 0;
                        @endphp
                        <div class="mb-6 p-4 border rounded shadow-sm">
                            <h3 class="text-lg font-semibold mb-2">Formula: {{ $data['formula']->product_name ?? 'N/A' }}</h3>
                            <p>Received Quantity: {{ $data['issued_qty'] }}</p>
                            <table class="table table-bordered w-full mt-3">
                                <thead>
                                    <tr class="bg-primary text-white">
                                        <th class="px-4 py-2">Ingredient</th>
                                        <th class="px-4 py-2">Required Quantity</th>
                                        <th class="px-4 py-2">Available Quantity</th>
                                        <th class="px-4 py-2">Price</th>
                                        <th class="px-4 py-2">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $ingredients = is_array($data['formula']->ingredients)
                                            ? $data['formula']->ingredients
                                            : json_decode($data['formula']->ingredients, true);
                                    @endphp
                                    @foreach($ingredients as $ingredient)
                                        @php
                                            $productId = $ingredient['product_id'];
                                            $requiredQty = $ingredient['quantity'] * $data['issued_qty'];
                                            $availableQty = 0;
                                            $price = 0;
                                            $totalAmount = 0;
                                            $productName = 'N/A';
                                            foreach($stockAvailability as $stockItem) {
                                                if($stockItem['product']->id == $productId) {
                                                    $availableQty = $stockItem['available_qty'];
                                                    $price = $stockItem['product']->purchase_rate ?? 0;
                                                    $productName = $stockItem['product']->product_name ?? 'N/A';
                                                    break;
                                                }
                                            }
                                            $totalAmount = $price * $requiredQty;
                                            $formulaTotalPrice += $totalAmount;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2">{{ $productName }}</td>
                                            <td class="px-4 py-2">{{ $requiredQty }}</td>
                                            <td class="px-4 py-2">{{ $availableQty }}</td>
                                            <td class="px-4 py-2">{{ number_format($price, 2) }}</td>
                                            <td class="px-4 py-2">{{ number_format($totalAmount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="mt-2 font-semibold">Total Receipt Price for this Formula: {{ number_format($formulaTotalPrice, 2) }}</p>
                        </div>
                        @php
                            $totalReceiptPrice += $formulaTotalPrice;
                        @endphp
                    @endforeach
                    <div class="text-right font-bold text-lg mt-4">
                        Total Receipt Price for Ledger: {{ number_format($totalReceiptPrice, 2) }}
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">
                        No pending stock receipt details found for this ledger.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
