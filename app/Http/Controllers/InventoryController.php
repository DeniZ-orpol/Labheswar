<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
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
                    $products = Product::whereIn('id', $productIds)
                        ->get()
                        ->keyBy('id');
                } else {
                    $products = Product::on($branch->connection_name)
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

                        // Create single inventory record for this product
                        $groupedInventory = (object) [
                            'product_id' => $productId,
                            'product' => $product,
                            'type' => 'calculated',
                            'quantity' => $totalQuantity,
                            'unit' => $productInventories->first()->unit ?? 'pcs',
                            'reason' => 'Total Stock',
                            'created_at' => $productInventories->max('created_at'),
                            'updated_at' => $productInventories->max('updated_at')
                        ];

                        $groupedInventories->push($groupedInventory);
                    }
                });

                $inventories = $groupedInventories;
            }
        }

        return view('inventory.index', compact('inventories'));
    }

    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            // $existing = Inventory::on($branch->connection_name)
            //     ->where('product_id', $request->product_id)
            //     ->first();

            // if ($existing) {
            //     // Update quantity based on type
            //     $newQty = $existing->quantity;
            //     if (strtoupper($request->type) == 'IN') {
            //         $newQty += $request->quantity;
            //     } elseif (strtoupper($request->type) == 'OUT') {
            //         $newQty -= $request->quantity;
            //     }

            //     Inventory::on($branch->connection_name)
            //         ->where('product_id', $request->product_id)
            //         ->update([
            //             'quantity' => $newQty,
            //             'reason' => $request->reason,
            //             'updated_at' => now(),
            //         ]);
            // } else {
            // Insert new inventory row
            $data = [
                'product_id' => $request->product_id,
                'quantity' => strtoupper($request->type) == 'IN' ? $request->quantity : -$request->quantity,
                'type' => $request->type,
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
}
