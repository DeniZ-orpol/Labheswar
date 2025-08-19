@php
    $currentIndex = (($orders->currentPage() - 1) * $orders->perPage()) + 1;
@endphp
@foreach ($orders as $index => $order)
    <tr class="border-b">
        <td class="px-4 py-2">O-ID: {{ $order->id }}</td>
        <td class="px-4 py-2">{{ $order->created_at->format('d-m-Y') }}</td>
        <td class="px-4 py-2">{{ $order->sub_total }}</td>
        <td class="px-4 py-2">{{ $order->total_texes }}</td>
        <td class="px-4 py-2">{{ $order->total }}</td>
        <td>
            <div class="flex gap-2">
                <a href="{{route('orders.show', $order->id)}}"
                    class="btn btn-primary">Order Receipt</a>
            </div>
        </td>
    </tr>
@endforeach
