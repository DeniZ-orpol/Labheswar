<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Traits\BranchAuthTrait;
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

    public function index()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $inventories = Inventory::get();
        } else {
            $inventories = Inventory::on($branch->connection_name)->get();
        }

        if ($inventories->isNotEmpty()) {
            $productIds = $inventories->pluck('product_id')->unique()->filter();

            if ($productIds->isNotEmpty()) {
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


                // Group by product and calculate total quantity
                $groupedInventories = collect();

                $inventories->groupBy('product_id')->each(function ($productInventories, $productId) use ($products, &$groupedInventories) {
                    $product = $products->get($productId);

                    if ($product) {
                        $totalQuantity = 0;

                        // Calculate total quantity for this product
                        foreach ($productInventories as $inventory) {
                            $totalQuantity += $inventory->quantity;
                        }

                        // Only proceed if there's available stock
                        if ($totalQuantity > 0) {
                            // Calculate purchase amounts using FIFO method for available quantity only
                            $purchaseAmounts = $this->calculatePurchaseAmountsForAvailableStock($productInventories, $totalQuantity);

                            // Create single inventory record for this product
                            $groupedInventory = (object) [
                                'product_id' => $productId,
                                'product' => $product,
                                // 'type' => 'calculated',
                                'quantity' => $totalQuantity,
                                'unit' => $productInventories->first()->unit ?? 'pcs',
                                // 'reason' => 'Total Stock',
                                'taxable_value' => $purchaseAmounts['taxable_value'],
                                'final_value' => $purchaseAmounts['final_value'],
                                'gst_amount' => $purchaseAmounts['gst_amount'],
                                'created_at' => $productInventories->max('created_at'),
                                'updated_at' => $productInventories->max('updated_at')
                            ];

                            $groupedInventories->push($groupedInventory);
                        } else {
                            // If no available stock, still show the product with zero amounts
                            $groupedInventory = (object) [
                                'product_id' => $productId,
                                'product' => $product,
                                // 'type' => 'calculated',
                                'quantity' => $totalQuantity,
                                'unit' => $productInventories->first()->unit ?? 'pcs',
                                // 'reason' => 'Total Stock',
                                'taxable_value' => 0,
                                'final_value' => 0,
                                'gst_amount' => 0,
                                'created_at' => $productInventories->max('created_at'),
                                'updated_at' => $productInventories->max('updated_at')
                            ];

                            $groupedInventories->push($groupedInventory);
                        }
                    }
                });

                $inventories = $groupedInventories;
            }
        }

        return view('inventory.index', compact('inventories'));
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
            if ($remainingQuantity <= 0) {
                break;
            }

            // Only process positive quantities (stock in)
            if ($inventory->quantity > 0) {
                // Take the minimum of remaining quantity or current inventory quantity
                $quantityToConsider = min($remainingQuantity, $inventory->quantity);

                // Calculate taxable value for this portion
                $taxableValue = $quantityToConsider * $inventory->purchase_price;
                $totalTaxableValue += $taxableValue;

                // Calculate GST amount and final value
                $gstAmount = ($taxableValue * $inventory->gst_p) / 100;
                $finalValue = $taxableValue + $gstAmount;
                $totalFinalValue += $finalValue;

                // Reduce remaining quantity
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

    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $product = Product::with('hsnCode')->find($request->product_id);
            } else {
                $product = Product::on($branch->connection_name)->with('hsnCode')->find($request->product_id);
            }
            // Insert new inventory row
            $data = [
                'product_id' => $request->product_id,
                'quantity' => strtoupper($request->type) == 'IN' ? $request->quantity : -$request->quantity,
                'total_qty' => strtoupper($request->type) == 'IN' ? $request->quantity : -$request->quantity,
                'unit' => $product->unit_types,
                'type' => $request->type,
                'mrp' => $request->mrp ?? 0,
                'sale_price' => $request->sale_price ?? 0,
                'purchase_price' => $request->purchase_price ?? 0,
                'gst' => $request->gst,
                'gst_p' => $request->gst == 'on' ? $product->hsnCode->gst : 0,
                'reason' => $request->reason,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Inventory::insert($data);
            } else {
                Inventory::on($branch->connection_name)->insert($data);
            }

            return redirect()->route('inventory.index')->with('success', 'Inventory saved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
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
            $products = Product::get();
        } else {
            $products = Product::on($branch->connection_name)->get();
        }

        return view('inventory.create', compact('products'));
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
}
