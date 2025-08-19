@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Inventory List</h2>
        </div>

        @if (session('success'))
            <div id="success-alert" class="alert alert-success"
                style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="alert alert-danger"
                style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif


        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table id="inventoryTable" class="table table-bordered table-striped">
    <thead class="bg-gray-100">
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>HSN</th>
            <th>Unit</th>
            <th>Qty in Hand (KG)</th>
            <th>Qty in Sold</th>
            <th>Taxable Value</th>
            <th>Final Value</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
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
            $index = 0;

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
                            {{ $mainItem->product->product_name }}
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
                <td>{{ $totalQty }} </td>
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
                    <tr>
                        <td>{{ $index }}.1</td>
                        <td style="padding-left: 40px;">{{ $mainItem->product->product_name }} (Main)</td>
                        <td>{{ $mainItem->product->hsnCode->hsn_code ?? 'N/A' }}</td>
                        <td>{{ $mainItem->product->unit_types ?? 'PCS' }}</td>
                        {{-- <td class="{{ $mainQtyKg < 0 ? 'text-red-500 font-bold' : '' }}">
                            {{ number_format($mainQtyKg, 3) }}
                        </td> --}}
                        <td>{{ $mainItem->quantity }}</td>
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
                        <tr>
                            <td>{{ $index }}.{{ $subIndex + 2 }}</td>
                            <td style="padding-left: 40px;">{{ $subItem->product->product_name }}</td>
                            <td>{{ $subItem->product->hsnCode->hsn_code ?? 'N/A'}}</td>
                            <td>{{ $subItem->product->unit_types ?? 'PCS' }}</td>
                            {{-- <td class="{{ $subQtyKg < 0 ? 'text-red-500 font-bold' : '' }}">
                                {{ number_format($subQtyKg, 3) }}
                            </td> --}}
                            <td>{{ $subItem->quantity }} ({{ $subItem->total_used ?? 0 }} {{ $subItem->product->unit_types }})</td>
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
    </tbody>
</table>
            </div>
        </div>
    </div>

    <!-- Inventory Details Modal -->
    <div id="inventoryModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Inventory Details</h2>
                </div>
                <div class="modal-body p-0">
                    <div class="p-6">
                        <!-- Product Info -->
                        <div id="productInfo" class="text-center mb-4">
                            <h3 id="productName" class="text-xl font-semibold text-gray-800">Product Name</h3>
                        </div>
                        <!-- Inventory Table -->
                        <div class="overflow-x-auto max-h-80">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            ID</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            Quantity</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            Type</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            MRP</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            Sale Price</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-700">
                                            GST</th>
                                        <th
                                            class="border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700">
                                            Purchase Price</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTableBody" class="bg-white">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel-inventory-modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openInventoryModal(productId) {
            // Show loading state
            const modal = document.getElementById('inventoryModal');
            const tableBody = document.getElementById('inventoryTableBody');
            const productName = document.getElementById('productName');

            // Clear previous data
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Loading...</td></tr>';
            productName.textContent = 'Loading...';

            // Show modal
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.marginTop = '50px';
            modal.style.marginLeft = '0';
            modal.style.zIndex = '50';
            modal.classList.add('show');
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');

            // Fetch inventory data
            fetch(`/inventory/${productId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateInventoryModal(data.inventories);
                    } else {
                        showError('Failed to load inventory data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Error loading inventory data');
                });
        }

        function populateInventoryModal(inventories) {
            const tableBody = document.getElementById('inventoryTableBody');
            const productName = document.getElementById('productName');

            // Clear loading state
            tableBody.innerHTML = '';

            if (inventories.length === 0) {
                tableBody.innerHTML =
                    '<tr><td colspan="7" class="text-center py-4 text-gray-500">No inventory records found</td></tr>';
                productName.textContent = 'No Product Data';
                return;
            }

            // Set product name from first inventory record
            if (inventories[0].product && inventories[0].product.product_name) {
                productName.textContent = inventories[0].product.product_name;
            }

            // Populate table rows
            inventories.forEach((inventory, index) => {
                const row = document.createElement('tr');
                row.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';

                row.innerHTML = `
            <td class="border border-gray-300 px-3 py-2 text-sm">${index + 1}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm ${inventory.total_qty < 0 ? 'text-red-500 font-bold' : ''}">${inventory.total_qty}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">
                ${inventory.purchase_id != null ? `${inventory.type} (Purchase)` : 
                inventory.one_to_many_id != null ? `${inventory.type} (OneToMany)` : 
                inventory.many_to_one_id != null ? `${inventory.type} (ManyToOne)` : 
                `${inventory.type} (Opening)`}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-sm">₹${parseFloat(inventory.mrp || 0).toFixed(2)}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">₹${parseFloat(inventory.sale_price || 0).toFixed(2)}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm text-center">${inventory.gst}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">₹${parseFloat(inventory.purchase_price || 0).toFixed(2)}</td>
        `;

                tableBody.appendChild(row);
            });
        }

        function showError(message) {
            const tableBody = document.getElementById('inventoryTableBody');
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">${message}</td></tr>`;
        }

        // Close modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('inventoryModal');
            const cancelBtn = document.getElementById('cancel-inventory-modal');

            // Close modal when cancel button is clicked
            cancelBtn.addEventListener('click', function() {
                closeInventoryModal();
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeInventoryModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeInventoryModal();
                }
            });


        });

        function closeInventoryModal() {
            const modal = document.getElementById('inventoryModal');
            modal.classList.remove('show');
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
        }

        function toggleItems(targetId) {
            const items = document.getElementById(targetId);
            const icon = document.querySelector(`.toggle-items[data-target="${targetId}"] i`);

            if (items.style.display === 'none') {
                items.style.display = '';
                if (icon) {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                }
            } else {
                items.style.display = 'none';
                if (icon) {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                }
            }
        }

        // For toggle buttons
        document.querySelectorAll('.toggle-items').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                toggleItems(targetId);
            });
        });

        // For product names
        document.querySelectorAll('.product-name.clickable').forEach(span => {
            span.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                toggleItems(targetId);
            });
        });

        // Add cursor pointer style for clickable elements
        document.addEventListener('DOMContentLoaded', function() {
            const style = document.createElement('style');
            style.textContent = `
            .product-name.clickable {
                cursor: pointer;
            }
            .product-name.clickable:hover {
                text-decoration: underline;
            }
        `;
            document.head.appendChild(style);
        });
    </script>
@endpush
