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

        $inventories = Inventory::on($branch->connection_name)->get();

        if ($inventories->isNotEmpty()) {
            $productIds = $inventories->pluck('product_id')->unique()->filter();

            if ($productIds->isNotEmpty()) {
                $products = Product::on($branch->connection_name)
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                // Attach products to inventories using Laravel's setRelation method
                $inventories->each(function ($inventory) use ($products) {
                    $inventory->setRelation('product', $products->get($inventory->product_id));
                });
            }
        }

        return view('inventory.index', compact('inventories'));
    }

    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        try {
            $existing = Inventory::on($branch->connection_name)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existing) {
                // Update quantity based on type
                $newQty = $existing->quantity;
                if (strtoupper($request->type) == 'IN') {
                    $newQty += $request->quantity;
                } elseif (strtoupper($request->type) == 'OUT') {
                    $newQty -= $request->quantity;
                }

                Inventory::on($branch->connection_name)
                    ->where('product_id', $request->product_id)
                    ->update([
                        'quantity' => $newQty,
                        'reason' => $request->reason,
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new inventory row
                Inventory::on($branch->connection_name)->insert([
                    'product_id' => $request->product_id,
                    'quantity' => strtoupper($request->type) == 'IN' ? $request->quantity : -$request->quantity,
                    'type' => $request->type,
                    'reason' => $request->reason,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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

        // Fetch branch-wise products
        $products = Product::on($branch->connection_name)->get();

        return view('inventory.create', compact('products'));
    }
}
