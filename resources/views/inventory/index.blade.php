@extends('app')

@section('content')
    <div class="content">
        <div class="flex items-center justify-between mt-5 mb-4">
            <h2 class="text-lg font-medium">Inventory List</h2>
            <a href="{{ Route('inventory.create') }}" class="btn btn-primary shadow-md btn-hover ml-auto">
                Add Open Stock
            </a>
        </div>


        <div class="intro-y box p-5 mt-2">
            <div class="overflow-x-auto">
                <table id="inventoryTable" class="table table-bordered table-striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $index => $inventory)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if ($inventory->product->image)
                                        <img src="{{ asset('storage/' . $inventory->product->image) }}" alt="Image"
                                            class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <span class="text-gray-400 italic">No Image</span>
                                    @endif
                                </td>
                                <td>{{ $inventory->product->product_name }}</td>
                                {{-- {{dd($inventory)}} --}}
                                <td class="{{ $inventory->quantity < 0 ? 'text-red-500 font-bold' : '' }}">
                                    {{ $inventory->quantity }}
                                </td>
                                <td>{{ $inventory->product->unit_types ?? '-' }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button onclick="openInventoryModal({{ $inventory->product_id }})"
                                            class="flex items-center justify-center text-success cursor-pointer hover:text-success-dark">
                                            View
                                        </button>
                                    </div>
                                </td>
                            </tr>
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
            <td class="border border-gray-300 px-3 py-2 text-sm ${inventory.quantity < 0 ? 'text-red-500 font-bold' : ''}">${inventory.quantity}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">${inventory.purchase_id == null ? `${inventory.type} (Opening)` : `${inventory.type} (Purchase)`}</td>
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
    </script>
@endpush
