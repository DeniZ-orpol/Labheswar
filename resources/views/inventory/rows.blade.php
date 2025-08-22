@php
    // Helper to convert product name unit (e.g., 250G) to KG
    function getKgValue($productName, $unitType = 'KG') {
        if (strtoupper($unitType) === 'KG') {
            if (preg_match('/(\d+)\s*G\b/i', $productName, $matches)) {
                return ((float) $matches[1]) / 1000;
            }
            if (preg_match('/(\d+(?:\.\d+)?)\s*KG\b/i', $productName, $matches)) {
                return (float) $matches[1];
            }
            return 1;
        }
        return 1;
    }

    $groupedItems = [];
    $index = ($paginator->currentPage() - 1) * $paginator->perPage();

    foreach ($inventories as $item) {
        $refId = $item->product->reference_id ?? 0;
        if (!isset($groupedItems[$refId])) {
            $groupedItems[$refId] = [];
        }
        $groupedItems[$refId][] = $item;
    }
@endphp

@foreach ($groupedItems[0] ?? [] as $mainItem)
    @php
        $mainItemId = $mainItem->product_id;
        $subItems = $groupedItems[$mainItemId] ?? [];
        $hasSubItems = count($subItems) > 0;

        // Conversion for main item
        // $mainQtyKg = $mainItem->quantity * getKgValue($mainItem->product->product_name, $mainItem->product->unit_types);
        // $totalQtyHand = $mainQtyKg;
        $totalQty = $mainItem->quantity;
        $totalQtySold = $mainItem->sold_quantity;
        $totalTaxable = $mainItem->taxable_value;
        $totalFinal = $mainItem->final_value;

        foreach ($subItems as $subItem) {
            // $subQtyKg = $subItem->quantity * getKgValue($subItem->product->product_name, $subItem->product->unit_types);
            // $totalQtyHand += $subQtyKg;
            $totalQty += $subItem->total_used ?? $subItem->quantity;
            $totalQtySold += $subItem->sold_quantity;
            $totalTaxable += $subItem->taxable_value;
            $totalFinal += $subItem->final_value;
        }
    @endphp

    <!-- Main item row -->
    <tr class="main-item-row">
        <td>{{ ++$index }}</td>
        <td>
            <span class="product-name @if ($hasSubItems) clickable @endif"
                @if ($hasSubItems) data-target="items-{{ $mainItemId }}" @endif>
                @if ($hasSubItems)
                    <strong>{{ $mainItem->product->product_name }}</strong>
                @else
                    {{ $mainItem->product->product_name ?? "" }}
                @endif
            </span>
            @if ($hasSubItems)
                <span class="badge bg-info">({{ count($subItems) }} sub-items)</span>
            @endif
        </td>
        <td>{{ $mainItem->product->hsnCode->hsn_code ?? 'N/A' }}</td>
        <td>{{ $mainItem->product->unit_types ?? 'PCS' }}</td>
        {{-- <td class="{{ $totalQtyHand < 0 ? 'text-red-500 font-bold' : '' }}">
            {{ number_format($totalQtyHand, 3) }}
        </td> --}}
        <td class="{{ $totalQty < 0 ? 'text-red-500 font-bold' : '' }}">{{ $totalQty }} </td>
        <td>{{ $totalQtySold }}</td>
        <td>{{ number_format($totalTaxable, 2) }}</td>
        <td>{{ number_format($totalFinal, 2) }}</td>
        <td>
            @if (!$hasSubItems)
                <div class="flex gap-2">
                    <button onclick="openInventoryModal({{ $mainItem->product_id }})"
                        class="flex items-center justify-center text-success cursor-pointer hover:text-success-dark">
                        History
                    </button>
                </div>
            @endif
        </td>
    </tr>

    @if ($hasSubItems)
        <tbody id="items-{{ $mainItemId }}" class="item-details" style="display: none;">
            <!-- Main item detail -->
            @php
                // $mainQtyKg = $mainItem->quantity * getKgValue($mainItem->product->product_name, $mainItem->product->unit_types);
            @endphp
            <tr class="sub-item-row items-{{ $mainItemId }}" style="display:none;">
                <td>{{ $index }}.1</td>
                <td style="padding-left: 40px;">{{ $mainItem->product->product_name }} (Main)</td>
                <td>{{ $mainItem->product->hsnCode->hsn_code ?? 'N/A' }}</td>
                <td>{{ $mainItem->product->unit_types ?? 'PCS' }}</td>
                {{-- <td class="{{ $mainQtyKg < 0 ? 'text-red-500 font-bold' : '' }}">
                    {{ number_format($mainQtyKg, 3) }}
                </td> --}}
                <td class="{{ $mainItem->quantity < 0 ? 'text-red-500 font-bold' : '' }}">{{ $mainItem->quantity }}</td>
                <td>{{ $mainItem->sold_quantity }}</td>
                <td>{{ number_format($mainItem->taxable_value, 2) }}</td>
                <td>{{ number_format($mainItem->final_value, 2) }}</td>
                <td>
                    <div class="flex gap-2">
                        <button onclick="openInventoryModal({{ $mainItem->product_id }})"
                            class="flex items-center justify-center text-success cursor-pointer hover:text-success-dark">
                            History
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Sub-items -->
            @foreach ($subItems as $subIndex => $subItem)
                @php
                    // $subQtyKg = $subItem->quantity * getKgValue($subItem->product->product_name, $subItem->product->unit_types);
                @endphp
                <tr class="sub-item-row items-{{ $mainItemId }}" style="display:none;">
                    <td>{{ $index }}.{{ $subIndex + 2 }}</td>
                    <td style="padding-left: 40px;">{{ $subItem->product->product_name }}</td>
                    <td>{{ $subItem->product->hsnCode->hsn_code ?? 'N/A'}}</td>
                    <td>{{ $subItem->product->unit_types ?? 'PCS' }}</td>
                    {{-- <td class="{{ $subQtyKg < 0 ? 'text-red-500 font-bold' : '' }}">
                        {{ number_format($subQtyKg, 3) }}
                    </td> --}}
                    <td class="{{ $subItem->quantity < 0 ? 'text-red-500 font-bold' : '' }}" >{{ $subItem->quantity }} ({{ $subItem->total_used ?? 0 }})</td>
                    <td>{{ $subItem->sold_quantity }}</td>
                    <td>{{ number_format($subItem->taxable_value, 2) }}</td>
                    <td>{{ number_format($subItem->final_value, 2) }}</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="openInventoryModal({{ $subItem->product_id }})"
                                class="flex items-center justify-center text-success cursor-pointer hover:text-success-dark">
                                History
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endif
@endforeach

<!-- Display orphan sub-items (no main item present) -->
@foreach ($groupedItems as $refId => $items)
    @php
        $mainItemExists = collect($groupedItems[0] ?? [])->firstWhere('product_id', $refId);
    @endphp
    @if ($refId != 0 && !$mainItemExists)
        @foreach ($items as $item)
            @php
                $qtyKg = $item->quantity * getKgValue($item->product->product_name, $item->product->unit_types);
            @endphp
            <tr>
                <td>{{ ++$index }}</td>
                <td style="padding-left: 20px;">{{ $item->product->product_name }} <span class="text-sm text-gray-500">(No main item)</span></td>
                <td>{{ $item->product->hsnCode->hsn_code ?? 'N/A' }}</td>
                <td>{{ $item->product->unit_types ?? 'PCS' }}</td>
                <td class="{{ $qtyKg < 0 ? 'text-red-500 font-bold' : '' }}">
                    {{ number_format($qtyKg, 3) }}
                </td>
                <td>{{ $item->sold_quantity }}</td>
                <td>{{ number_format($item->taxable_value, 2) }}</td>
                <td>{{ number_format($item->final_value, 2) }}</td>
                <td>
                    <div class="flex gap-2">
                        <button onclick="openInventoryModal({{ $item->product_id }})"
                            class="flex items-center justify-center text-success cursor-pointer hover:text-success-dark">
                            History
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
@endforeach