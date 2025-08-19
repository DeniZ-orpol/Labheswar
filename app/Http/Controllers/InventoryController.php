<?php

namespace App\Http\Controllers;

use App\Models\AppCartsOrders;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Formula;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->increment('quantity', $request->quantity);

        Inventory::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => $request->quantity,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return back()->with('error', 'Not enough stock.');
        }

        $product->decrement('quantity', $request->quantity);

        Inventory::create([
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Stock deducted successfully.');
    }
    public function quickUpdate(Request $request)
    {

        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $request->validate([
            // 'product_id' => 'required|integer|exists:products,id',
            'column' => 'required|string',
            'value' => 'nullable',
        ]);

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $product = Product::with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $request->product_id)
                ->firstOrFail();
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $request->product_id)
                ->firstOrFail();
        }
        if ($request->column == 'total_qty') {
            $inventory = Inventory::on($branch->connection_name)->where('product_id', $request->product_id)
                        ->whereNull('purchase_id')
                        ->whereNull('one_to_many_id')
                        ->whereNull('many_to_one_id')
                        ->where('type', 'in')->first();

            if (!$inventory) {
                // Call private function to insert inventory
                $this->insertInventory($product, $request->value,$request->gst,$request->gst_p);
                return response()->json(['success' => true, 'message' => 'Inventory inserted.']);
            }

            // Update existing inventory
            if ($request->value != '') {
                $inventory->total_qty = $request->value;
                $inventory->quantity = $request->value ?? "";
            }


            $inventory->save();

            return response()->json(['success' => true, 'message' => 'Product updated.']);
        }



        // dd($request->all(), $product->id);
        if ($request->value != '') {
            $product->{$request->column} = $request->value;
        }
        $product->save();

        return response()->json(['success' => true, 'message' => 'Product updated.']);
    }

    private function insertInventory($product, $qty, $gst = "off", $gst_p = 0)
    {
        // dd($product, $qty);

        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $inventory = Inventory::where('product_id', $product['id'])
                ->whereNull('purchase_id')
                ->whereNull('one_to_many_id')
                ->whereNull('many_to_one_id')
                ->where('type', 'in')->first();
        } else {
            $inventory = Inventory::on($branch->connection_name)->where('product_id', $product['id'])
                ->whereNull('purchase_id')
                ->whereNull('one_to_many_id')
                ->whereNull('many_to_one_id')
                ->where('type', 'in')->first();
        }
        // dd($inventory);

        // if (empty($product['qty']) || $product['qty'] == 0) {
        //     continue; // skip if no qty entered
        // }

        if ($inventory) {
            if ($inventory->total_qty == $inventory->quantity) {
                $inventory->update([
                    'total_qty' => $product['qty'],
                    'quantity' => $product['qty']
                ]);
            } else {
                $inventory->update([
                    'total_qty' => $product['qty']
                ]);
            }
        } else {
            $inventory = [
                'product_id' => $product['id'],
                // 'barcode'        => $product['barcode'] ?? null,
                'quantity' => $qty,
                'total_qty' => $qty,
                'mrp' => $product['mrp'] ?? 0,
                'sale_price' => $product['sale_rate_a'] ?? 0,
                'purchase_price' => $product['purchase_rate'] ?? 0,
                // 'gst'            => $product['gst_percent'] ?? 0,
                'gst' => $gst ?? 'off',
                'gst_p' => $gst == 'on' ? $gst_p : 0,
                'reason' => $product['reason'] ?? null,
                'type' => 'in', // or allow it from input
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Inventory::insert($inventory);
            } else {
                Inventory::on($branch->connection_name)->insert($inventory);
            }
        }

    }
    public function index()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Fetch inventory and order data
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $inventories = Inventory::get();
            $orderInventories = AppCartsOrders::get();
        } else {
            $inventories = Inventory::on($branch->connection_name)->get();
            $orderInventories = AppCartsOrders::on($branch->connection_name)->get();
        }

        $inventoriesList = collect();

        if ($inventories->isNotEmpty()) {
            $productIds = $inventories->pluck('product_id')->unique()->filter();

            if ($productIds->isNotEmpty()) {
                // Get product details
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $products = Product::with('hsnCode')->whereIn('id', $productIds)
                        ->get()
                        ->keyBy('id');
                } else {
                    $products = Product::on($branch->connection_name)->with('hsnCode')
                        ->whereIn('id', $productIds)
                        ->get()
                        ->keyBy('id');
                }
                // Sold quantities
                $soldQuantities = $orderInventories->groupBy('product_id')->map(fn($orders) => $orders->sum('product_quantity'));

                // Build grouped inventory
                $grouped = collect();
                $inventories->groupBy('product_id')->each(function ($productInventories, $productId) use ($products, $soldQuantities, &$grouped) {
                    $product = $products->get($productId);
                    if ($product) {
                        $totalQuantity = $productInventories->sum('quantity');
                        $soldQuantity = $soldQuantities[$productId] ?? 0;
                        $purchaseAmounts = $this->calculatePurchaseAmountsForAvailableStock($productInventories, $totalQuantity);

                        $grouped->push((object) [
                            'product_id' => $productId,
                            'product' => $product,
                            'quantity' => $totalQuantity,
                            'sold_quantity' => $soldQuantity,
                            'unit' => $productInventories->first()->unit ?? 'pcs',
                            'taxable_value' => $purchaseAmounts['taxable_value'],
                            'final_value' => $purchaseAmounts['final_value'],
                            'gst_amount' => $purchaseAmounts['gst_amount'],
                            'created_at' => $productInventories->max('created_at'),
                            'updated_at' => $productInventories->max('updated_at'),
                        ]);
                    }
                });

                // Split main and sub
                $main = $grouped->filter(fn($inv) => $inv->product->reference_id === null || $inv->product->reference_id == 0);
                $subs = $grouped->filter(fn($inv) => $inv->product->reference_id !== null && $inv->product->reference_id != 0)
                    ->groupBy(fn($inv) => $inv->product->reference_id);

                // Recursive ordering
                $ordered = collect();
                foreach ($main as $index => $mainItem) {
                    $ordered = $ordered->merge($this->buildNestedInventory($mainItem, $subs));
                }

                // Include orphan subs
                $inventoriesList = $ordered->merge(
                    $grouped->reject(fn($item) => $ordered->contains(fn($o) => $o->product_id === $item->product_id))
                );
            }
        }
        // dd($inventoriesList);
        return view('inventory.index', [
            'inventories' => $inventoriesList,
        ]);
    }

    // Recursive helper function
    private function buildNestedInventory($item, $subs, $level = 0)
    {
        $item = clone $item;
        $item->level = $level;
        $ordered = collect([$item]);


        if ($subs->has($item->product_id)) {
            foreach ($subs[$item->product_id] as $child) {
                if(strtoupper($item->product->unit_types) !== 'PCS' ) {
                // if ($item->product_id == 2272) {
                    // dd($child,$item->level);
                // }
                    $subitem_Id = $child->product->id;
                    $subitem_reference_id = $child->product->reference_id;
                    // dd($child, $subitem_Id, $subitem_reference_id);

                    $getProductFormulaAndWeight = $this->getProductFormulaAndWeight($subitem_Id, $subitem_reference_id);
                    $total = $getProductFormulaAndWeight[0] * $child->quantity;
                    $child->total_used = $total;
                }
                $ordered = $ordered->merge($this->buildNestedInventory($child, $subs, $level + 1));
            }
        }
        
        return $ordered;
    }

    private function getProductFormulaAndWeight($productId, $referenceId)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $branch = $auth['branch'];
            $role = $auth['role'];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $formula = Formula::where('product_id', $productId)->first();
            } else {
                $formula = Formula::on($branch->connection_name)
                    ->where('product_id', $productId)
                    ->first();
            }

            $quantity = 0; // default if not found

            if ($formula) {
                $ingredients = json_decode($formula->ingredients, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($ingredients)) {
                    foreach ($ingredients as $ingredient) {
                        if (
                            isset($ingredient['product_id']) &&
                            $ingredient['product_id'] == $referenceId
                        ) {
                            $quantity = $ingredient['quantity']/$formula->quantity;;
                            break;
                        }
                    }
                }
            }

            return [$quantity];
        } catch (\Throwable $e) {
            // You can log the error for debugging
            \Log::error("Error fetching formula for product {$productId}: " . $e->getMessage());
            return [null]; // Return null if anything goes wrong
        }
    }

    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            foreach ($request->products as $productId => $product) {

                // NEW CODE: Handle new product creation
                if (empty($product['product_id']) && !empty($product['reference_id'])) {
                    // Get reference product data first
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $referenceProduct = Product::find($product['reference_id']);
                    } else {
                        $referenceProduct = Product::on($branch->connection_name)->find($product['reference_id']);
                    }

                    if ($referenceProduct) {
                        // Start with reference product data
                        $newProduct = $referenceProduct->replicate();

                        // Update with new product data from frontend
                        $newProduct->product_name = $product['name'];
                        $newProduct->barcode = $product['barcode'] ?? '';
                        $newProduct->mrp = $product['mrp'];
                        $newProduct->sale_rate_a = $product['sale_rate'];
                        $newProduct->purchase_rate = $product['purchase_price'];
                        $newProduct->reference_id = $product['reference_id'];

                        $attributes = $newProduct->getAttributes();
                        unset($attributes['id']);

                        // Insert the new product and get ID
                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            $createdProduct = Product::create($attributes);
                            $product['product_id'] = $createdProduct->id;
                        } else {
                            $createdProduct = Product::on($branch->connection_name)->create($attributes);
                            $product['product_id'] = $createdProduct->id;
                        }
                    }
                } elseif (!empty($product['product_id'])) {

                    // Prepare update data for existing product
                    $updateData = [
                        'product_name' => $product['name'],
                        'barcode' => $product['barcode'] ?? '',
                        'mrp' => $product['mrp'],
                        'sale_rate_a' => $product['sale_rate'],
                        'purchase_rate' => $product['purchase_price'],
                    ];

                    // Update the existing product
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Product::where('id', $product['product_id'])->update($updateData);
                    } else {
                        Product::on($branch->connection_name)->where('id', $product['product_id'])->update($updateData);
                    }
                }
                // END NEW CODE

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $inventory = Inventory::where('product_id', $product['product_id'])
                        ->whereNull('purchase_id')
                        ->whereNull('one_to_many_id')
                        ->whereNull('many_to_one_id')
                        ->where('type', 'in')->first();
                } else {
                    $inventory = Inventory::on($branch->connection_name)->where('product_id', $product['product_id'])
                        ->whereNull('purchase_id')
                        ->whereNull('one_to_many_id')
                        ->whereNull('many_to_one_id')
                        ->where('type', 'in')->first();
                }

                if (empty($product['qty']) || $product['qty'] == 0) {
                    continue; // skip if no qty entered
                }

                if ($inventory) {
                    if ($inventory->total_qty == $inventory->quantity) {
                        $inventory->update([
                            'total_qty' => $product['qty'],
                            'quantity' => $product['qty']
                        ]);
                    } else {
                        $inventory->update([
                            'total_qty' => $product['qty']
                        ]);
                    }
                } else {
                    $inventory = [
                        'product_id' => $product['product_id'],
                        // 'barcode'        => $product['barcode'] ?? null,
                        'quantity' => $product['qty'],
                        'total_qty' => $product['qty'],
                        'mrp' => $product['mrp'] ?? 0,
                        'sale_price' => $product['sale_rate'] ?? 0,
                        'purchase_price' => $product['purchase_price'] ?? 0,
                        // 'gst'            => $product['gst_percent'] ?? 0,
                        'gst' => $product['gst'] ?? 'off',
                        'gst_p' => $product['gst'] == 'on' ? $product['gst_p'] : 0,
                        'reason' => $product['reason'] ?? null,
                        'type' => 'in', // or allow it from input
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::insert($inventory);
                    } else {
                        Inventory::on($branch->connection_name)->insert($inventory);
                    }
                }

            }

            return redirect()->route('inventory.index')->with('success', 'Inventory saved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function inventorystore(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        dd($request->all());

        try {
            if (empty($request->product_id) && !empty($request->reference_id)) {
                // Get reference product data first
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $referenceProduct = Product::find($request->reference_id);
                } else {
                    $referenceProduct = Product::on($branch->connection_name)->find($request->reference_id);
                }

                if ($referenceProduct) {
                    // Start with reference product data
                    $newProduct = $referenceProduct->replicate();

                    // Update with new product data from frontend
                    $newProduct->product_name = $request->name;
                    $newProduct->barcode = $request->barcode ?? '';
                    $newProduct->mrp = $request->mrp;
                    $newProduct->sale_rate_a = $request->sale_rate;
                    $newProduct->purchase_rate = $request->purchase_price;
                    $newProduct->reference_id = $request->reference_id;
                    $attributes = $newProduct->getAttributes();
                    unset($attributes['id']);

                    // Insert the new product and get ID
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $product = Product::create($attributes);
                        $product['product_id'] = $product->id;
                    } else {
                        $product = Product::on($branch->connection_name)->create($attributes);
                        $product['product_id'] = $product->id;
                    }
                }
            }
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $inventory = Inventory::where('product_id', $product['product_id'])
                    ->whereNull('purchase_id')
                    ->whereNull('one_to_many_id')
                    ->whereNull('many_to_one_id')
                    ->where('type', 'in')->first();
            } else {
                $inventory = Inventory::on($branch->connection_name)->where('product_id', $product['product_id'])
                    ->whereNull('purchase_id')
                    ->whereNull('one_to_many_id')
                    ->whereNull('many_to_one_id')
                    ->where('type', 'in')->first();
            }

            if (empty($request->qty) || $request->qty == 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'New product created from reference.',
                    'product_id' => $product->id
                ], 200);
            }

            if ($inventory) {
                if ($inventory->total_qty == $inventory->quantity) {
                    $inventory->update([
                        'total_qty' => $request->qty,
                        'quantity' => $request->qty
                    ]);
                } else {
                    $inventory->update([
                        'total_qty' => $request->qty
                    ]);
                }
            } else {

                $newInventory = [
                    'product_id' => $product->id,
                    'quantity' => $request->qty,
                    'total_qty' => $request->qty,
                    'mrp' => $request->mrp ?? 0,
                    'sale_price' => $request->sale_rate ?? 0,
                    'purchase_price' => $request->purchase_price ?? 0,
                    'gst' => $request->gst ?? 'off',
                    'gst_p' => ($request->gst === 'on') ? ($request->gst_p ?? 0) : 0,
                    'reason' => $request->reason ?? null,
                    'type' => 'in', // default type
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::insert($newInventory);
                } else {
                    Inventory::on($branch->connection_name)->insert($newInventory);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'New product created from reference.',
                'product_id' => $product->id
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    // You can fetch products here if needed
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Fetch branch-wise products
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $products = Product::with([
                'hsnCode',
                'inventories' => function ($query) {
                    $query->whereNull('purchase_id')
                        ->whereNull('one_to_many_id')
                        ->whereNull('many_to_one_id')
                        ->where('type', 'in');
                }
            ])->get();
        } else {
            $products = Product::on($branch->connection_name)->with([
                'hsnCode',
                'inventories' => function ($query) {
                    $query->whereNull('purchase_id')
                        ->whereNull('one_to_many_id')
                        ->whereNull('many_to_one_id')
                        ->where('type', 'in');
                }
            ])->get();
        }

        $main = $products->whereNull('reference_id');
        $subs = $products->whereNotNull('reference_id')->groupBy('reference_id');

        $ordered = collect();

        // Loop through main products and recursively append their children
        foreach ($main as $product) {
            $ordered = $this->appendChildren($product, $products, $ordered, $subs);
        }

        // Include any orphan sub-products (not attached to any main product)
        $products = $ordered->merge($products->diff($ordered)->values());

        return view('inventory.create', compact('products'));
    }

    public function appendChildren($product, $products, $ordered, $subs)
    {
        $ordered->push($product);

        if (isset($subs[$product->id])) {
            foreach ($subs[$product->id] as $child) {
                $ordered = $this->appendChildren($child, $products, $ordered, $subs);
            }
        }

        return $ordered;
    }

    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $inventories = Inventory::with('product')->where('product_id', $id)->get();
        } else {
            $inventories = Inventory::on($branch->connection_name)->with('product')->where('product_id', $id)->get();
        }

        return response()->json([
            'success' => true,
            'inventories' => $inventories
        ]);
    }

    /**
     * Calculate purchase amounts for available stock using FIFO method
     */
    private function calculatePurchaseAmountsForAvailableStock($productInventories, $availableQuantity)
    {
        // Sort inventories by created_at (FIFO - First In, First Out)
        $sortedInventories = $productInventories->sortBy('created_at');

        $remainingQuantity = $availableQuantity;
        $totalTaxableValue = 0;
        $totalFinalValue = 0;

        // Process positive quantities (stock in) until we have accounted for available quantity
        foreach ($sortedInventories as $inventory) {
            if ($remainingQuantity == 0) {
                break;
            }

            // Handle Stock In (positive remaining quantity)
            if ($remainingQuantity > 0 && $inventory->quantity > 0) {
                // Take the minimum of remaining or available quantity
                $quantityToConsider = min($remainingQuantity, $inventory->quantity);

                $taxableValue = $quantityToConsider * $inventory->purchase_price;
                $gstAmount = ($taxableValue * $inventory->gst_p) / 100;
                $finalValue = $taxableValue + $gstAmount;

                $totalTaxableValue += $taxableValue;
                $totalFinalValue += $finalValue;

                $remainingQuantity -= $quantityToConsider;
            }

            // Handle Stock Out (negative remaining quantity)
            elseif ($remainingQuantity < 0 && $inventory->quantity < 0) {
                // Take the maximum of remaining or available quantity (both are negative)
                $quantityToConsider = max($remainingQuantity, $inventory->quantity);

                $taxableValue = $quantityToConsider * $inventory->purchase_price;
                $gstAmount = ($taxableValue * $inventory->gst_p) / 100;
                $finalValue = $taxableValue + $gstAmount;

                $totalTaxableValue += $taxableValue;
                $totalFinalValue += $finalValue;

                $remainingQuantity -= $quantityToConsider;
            }
        }

        return [
            'taxable_value' => round($totalTaxableValue, 2),
            'final_value' => round($totalFinalValue, 2),
            'gst_amount' => round($totalFinalValue - $totalTaxableValue, 2),
            // 'average_purchase_price' => $availableQuantity > 0 ? round($totalTaxableValue / $availableQuantity, 2) : 0
        ];
    }

    public function dataCorrection()
    {
        $inventories = Inventory::whereNull('purchase_id')
            ->whereNull('one_to_many_id')
            ->whereNull('many_to_one_id')
            ->where('type', 'in')
            ->get();

        // Step 2: Group by product_id
        $grouped = $inventories->groupBy('product_id');

        DB::beginTransaction();
        try {
            foreach ($grouped as $productId => $items) {
                if ($items->count() <= 1) {
                    continue; // Nothing to merge
                }

                // Step 3: Sum qty and total_qty
                $totalQty = $items->sum('quantity');
                $totalTotalQty = $items->sum('total_qty');

                // Step 4: Keep the first item and update it
                $primary = $items->first();
                $primary->quantity = $totalQty;
                $primary->total_qty = $totalTotalQty;
                $primary->save();

                // Step 5: Delete the rest
                $idsToDelete = $items->filter(fn($item) => $item->id != $primary->id);

                // === LOGGING ===
                echo "\n========== Product ID: {$productId} ==========\n";
                echo "✔️ Updated Primary Record:\n";
                echo json_encode($primary->toArray(), JSON_PRETTY_PRINT) . "\n";

                echo "🗑️ Deleted Records:\n";
                foreach ($idsToDelete as $other) {
                    echo json_encode($other->toArray(), JSON_PRETTY_PRINT) . "\n";
                }

                Inventory::whereIn('id', $idsToDelete->pluck('id')->all())->delete();
            }
            DB::commit();
            return response()->json(['message' => 'Data correction completed!']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
