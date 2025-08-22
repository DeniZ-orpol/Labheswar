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

        <div class="flex items-center mt-2 mb-3">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-2 ml-auto">
                <input type="text" name="search" id="search-inventory" placeholder="Search by product"
                    value="{{ request('search') }}" class="form-control flex-1">
                <button type="submit" class="btn btn-primary shadow-md">Search</button>
            </form>
        </div>

        <div class="intro-y box p-5 mt-2">
            <div id="scrollable-inventory" style="max-height: calc(100vh - 250px); overflow-y: auto; border: 1px solid #ddd;">
                <table id="inventoryTable" class="table table-bordered table-striped">
                    <thead class="bg-primary text-white sticky top-0">
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
                    <tbody id="inventory-data">
                        @include('inventory.rows', ['inventories' => $inventories])
                    </tbody>
                </table>
            </div>
            <div id="loading" style="display:none; text-align:center; padding:10px;">
                <p>Loading more inventories...</p>
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
            document.querySelectorAll('.' + targetId).forEach(row => {
                row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
            });
        }

        // For product names
        function bindClickEvents() {
            document.querySelectorAll('.product-name.clickable').forEach(span => {
                span.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    toggleItems(targetId);
                });
            });
        }

        // Run initially
        bindClickEvents();
        
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

        let page = 1;
        let loading = false;
        let currentSearch = '';

        const scrollContainer = document.getElementById('scrollable-inventory');
        const inventoryData = document.getElementById('inventory-data');
        const loadingIndicator = document.getElementById('loading');
        const searchInput = document.getElementById('search-inventory');
        let searchTimer;

        // Search with debounce
        searchInput.addEventListener('keyup', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentSearch = searchInput.value.trim();
                page = 1;
                loadMoreData(page, false);
            }, 300);
        });

        // Infinite scroll
        scrollContainer.addEventListener('scroll', function () {
            const scrollBottom = scrollContainer.scrollTop + scrollContainer.clientHeight;
            const scrollHeight = scrollContainer.scrollHeight;

            if (scrollBottom >= scrollHeight - 100 && !loading) {
                page++;
                loadMoreData(page, true);
            }
        });

        function loadMoreData(pageToLoad, append = false) {
            loading = true;
            loadingIndicator.style.display = 'block';

            let url = new URL(window.location.href);
            url.searchParams.set('page', pageToLoad);

            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            } else {
                url.searchParams.delete('search');
            }

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                bindClickEvents();
                if (!data.html.trim() ) {
                    loadingIndicator.innerHTML = '';
                    return;
                }

                if (append) {
                    inventoryData.insertAdjacentHTML('beforeend', data.html);
                } else {
                    inventoryData.innerHTML = data.html;
                }


                if (!data.hasMore) {
                    loadingIndicator.style.display = 'none';
                    loadingIndicator.innerHTML = '';
                    return;
                }

                loadingIndicator.style.display = 'none';
                loading = false;
            })
            .catch(error => {
                console.error("Error fetching inventories:", error);
                loadingIndicator.style.display = 'none';
                loading = false;
            });
        }
    </script>
@endpush
