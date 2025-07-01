<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockInHand;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class StockInHandController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $stocks = StockInHand::with('product')->get();
        } else {
            $stocks = StockInHand::on($branch->connection_name)->with('product')->get();
        }

        return view('stockInHand.index', compact('stocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $products = Product::get();
        } else {
            $products = Product::on($branch->connection_name)->get();
        }
        return view('stockInHand.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            $validated = $request->validate([
                'product' => 'required',
                'price' => 'nullable|numeric|min:0',
                'qty_in_hand' => 'nullable|',
                'qty_in_sold' => 'nullable|',
                'inventory_value' => 'nullable|string|max:255',
                'sale_value' => 'nullable|string|max:255',
                'available_stock' => 'nullable|string|max:255'
            ]);

            $data = [
                'product_id' => $validated['product'],
                'price' => $validated['price'] ?? 0,
                'qty_in_hand' => $validated['qty_in_hand'] ?? 'Profit',
                'qty_sold' => $validated['qty_in_sold'] ?? 'Loose',
                'inventory_value' => $validated['inventory_value'] ?? 'Profit',
                'sale_value' => $validated['sale_value'] ?? 'Loose',
                'available_stock' => $validated['available_stock'] ?? 'Profit',
                'status' => '1'
            ];

            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                StockInHand::create($data);
            } else {
                StockInHand::on($branch->connection_name)->create($data);
            }

            return redirect()->route('stock-in-hand.index')->with('success', 'Stock in hand created successfully.');

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockInHand $stockInHand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $stock = StockInHand::with('product')->where('id', $id)->firstOrFail();
            $products = Product::get();
        } else {
            $stock = StockInHand::on($branch->connection_name)
                ->with('product')->where('id', $id)
                ->firstOrFail();
            $products = Product::on($branch->connection_name)->get();

        }

        return view('stockInHand.edit', compact('stock', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            $validated = $request->validate([
                'product' => 'required',
                'price' => 'nullable|numeric|min:0',
                'qty_in_hand' => 'nullable|',
                'qty_in_sold' => 'nullable|',
                'inventory_value' => 'nullable|string|max:255',
                'sale_value' => 'nullable|string|max:255',
                'available_stock' => 'nullable|string|max:255'
            ]);

            $data = [
                'product_id' => $validated['product'],
                'price' => $validated['price'] ?? 0,
                'qty_in_hand' => $validated['qty_in_hand'] ?? 'Profit',
                'qty_sold' => $validated['qty_in_sold'] ?? 'Loose',
                'inventory_value' => $validated['inventory_value'] ?? 'Profit',
                'sale_value' => $validated['sale_value'] ?? 'Loose',
                'available_stock' => $validated['available_stock'] ?? 'Profit',
                'status' => '1'
            ];

            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $stock = StockInHand::with('product')->where('id', $id)->firstOrFail();
            } else {
                $stock = StockInHand::on($branch->connection_name)
                ->with('product')->where('id', $id)
                ->firstOrFail();
            }

            $stock->update($data);

            return redirect()->route('stock-in-hand.index')->with('success', 'Stock in hand updated successfully.');

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockInHand $stockInHand)
    {
        //
    }
}
