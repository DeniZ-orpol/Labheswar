<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
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
        if (!session('branch_connection')) {
            return redirect()->route('login')->with('error', 'Please login as branch user.');
        }

        $branchConnection = session('branch_connection');

        $inventories = Inventory::on($branchConnection)
            ->with('product') // Eloquent relationship
            ->get();

        return view('inventory.index', compact('inventories'));
    }

    public function store(Request $request)
    {
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->route('login')->with('error', 'Please login as branch user.');
        }

        // $request->validate([
        //     'product_id' => 'required|integer',
        //     'type' => 'required|in:IN,OUT',
        //     'quantity' => 'required|numeric|min:0',
        //     'reason' => 'nullable|string|max:255',
        // ]);

        $branchConnection = session('branch_connection');

        try {
            $existing = DB::connection($branchConnection)->table('inventory')
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

                DB::connection($branchConnection)->table('inventory')
                    ->where('product_id', $request->product_id)
                    ->update([
                        'quantity' => $newQty,
                        'reason' => $request->reason,
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new inventory row
                DB::connection($branchConnection)->table('inventory')->insert([
                    'product_id' => $request->product_id,
                    'quantity' => $request->type == 'IN' ? $request->quantity : -$request->quantity,
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
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->route('login')->with('error', 'Please login as branch user.');
        }

        $branchConnection = session('branch_connection');

        // Fetch branch-wise products
        $products = Product::on($branchConnection)->get();

        return view('inventory.create', compact('products'));
    }
}
