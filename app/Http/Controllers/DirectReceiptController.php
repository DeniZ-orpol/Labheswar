<?php

namespace App\Http\Controllers;

use App\Models\DirectReceipt;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Formula;
use App\Models\Inventory;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectReceiptController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

         $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $directReceipts = DirectReceipt::with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $directReceipts = DirectReceipt::on($branch->connection_name)->with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        }
        if ($request->ajax()) {
            return view('directreceipt.rows', compact('directReceipts'))->render();
        }

        return view('directreceipt.index', compact('directReceipts'));
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

        return view('directreceipt.create');
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

        $request->validate([
            'ledger' => 'required|string',
            // 'dr_no' => 'required|string',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            'cost' => 'required',
            'mrp' => 'required|array',
            'mrp.*' => 'required|numeric',
            'sale_rate' => 'required|array',
            'sale_rate.*' => 'required|numeric',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0',
            'amount' => 'required|array',
            'amount.*' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $products = [];
        $ingredientTotals = []; 
        $ingredientUsages = []; 
        $count = count($request->product_id);
        for ($i = 0; $i < $count; $i++) {
            
            $formulaId = $request->product_id[$i];
            $issuedQty = $request->qty[$i];
            
            $formula = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Formula::findOrFail($formulaId)
                : Formula::on($branch->connection_name)->findOrFail($formulaId);

            $products[] = [
                'product_search' => $request->product_search[$i],
                'product_id' => $request->product_id[$i],
                'formula_product_id' => $formula->product_id,
                'cost' => $request->cost[$i],
                'mrp' => $request->mrp[$i],
                'sale_rate' => $request->sale_rate[$i],
                'qty' => $request->qty[$i],
                'amount' => $request->amount[$i],
            ];
            
            $ingredients = is_array($formula->ingredients)
                ? $formula->ingredients
                : json_decode($formula->ingredients, true);

            foreach ($ingredients as $ingredient) {
                $ingredientProductId = $ingredient['product_id'];
                $requiredQty = $ingredient['quantity'] * $issuedQty;

                if (!isset($ingredientTotals[$ingredientProductId])) {
                    $ingredientTotals[$ingredientProductId] = [
                        'required_qty' => 0,
                        'formula_id' => $formulaId,
                    ];
                }

                $ingredientTotals[$ingredientProductId]['required_qty'] += $requiredQty;
            }
        }

        foreach ($ingredientTotals as $productId => $ingredientData) {
            $totalRequiredQty = $ingredientData['required_qty'];
            $formulaId = $ingredientData['formula_id'];
            $inventoryEntries = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Inventory::where('product_id', $productId)->get()
                : Inventory::on($branch->connection_name)->where('product_id', $productId)->get(); 

            $formula = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Formula::findOrFail($formulaId)
                : Formula::on($branch->connection_name)->findOrFail($formulaId);
            if(!$formula->auto_production){
                $availableQty = $inventoryEntries->sum('quantity');
    
                if ($totalRequiredQty > $availableQty) {
                    $productName = Product::find($productId)->name ?? 'Unknown';
                    return redirect()->back()->with('error', "Insufficient stock for ingredient: {$productName}. Required: {$totalRequiredQty}, Available: {$availableQty}")->withInput();
                }
            }

            $ingredientUsages[] = [
                'product_id' => $productId,
                'required_qty' => $totalRequiredQty,
            ];
        }

        \DB::beginTransaction();

        try {
            $directReceiptData = [
                'ledger' => $request->ledger,
                // 'dr_no' => $request->dr_no,
                'date' => $request->date,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $directReceipt_no = DirectReceipt::count() + 1;
                $directReceiptData['dr_no'] = 'DR-' . $directReceipt_no;
                $directReceipt = DirectReceipt::create($directReceiptData);
            } else {
                $directReceipt_no = DirectReceipt::on($branch->connection_name)->count() + 1;
                $directReceiptData['dr_no'] = 'DR-' . $directReceipt_no;
                $directReceipt = DirectReceipt::on($branch->connection_name)->create($directReceiptData);
            }

            foreach ($ingredientUsages as $usage) {
                $productId = $usage['product_id'];
                $qtyToDeduct = $usage['required_qty'];

                $rawProduct = strtoupper($role->role_name) === 'SUPER ADMIN'
                    ? Product::find($productId)
                    : Product::on($branch->connection_name)->find($productId);

                $deduction = [
                    'product_id' => $productId,
                    'type' => 'out',
                    'total_qty' => -$qtyToDeduct,
                    'quantity' => -$qtyToDeduct,
                    'unit' => $rawProduct->unit_types ?? 'pcs',
                    'reason' => "Used in formula direct receipt #{$request->dr_no}",
                    'gst' => 'off',
                    'gst_p' => 0,
                    'mrp' => $rawProduct->mrp ?? 0,
                    'sale_price' => $rawProduct->sale_rate_a ?? 0,
                    'purchase_price' => $rawProduct->purchase_rate ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::create($deduction);
                } else {
                    Inventory::on($branch->connection_name)->create($deduction);
                }
            }

            foreach ($products as $product) {
                $productId = $product['product_id'];
                $formulaProductId = $product['formula_product_id'];
                $qty = $product['qty'];

                $finishedProduct = strtoupper($role->role_name) === 'SUPER ADMIN'
                    ? Product::find($productId)
                    : Product::on($branch->connection_name)->find($productId);

                $inInventory = [
                    'product_id' => $formulaProductId,
                    'type' => 'in',
                    'total_qty' => $qty,
                    'quantity' => $qty,
                    'unit' => $finishedProduct->unit_types ?? 'pcs',
                    'reason' => "Stock received from Direct Receipt #{$request->dr_no}",
                    'gst' => 'off',
                    'gst_p' => 0,
                    'mrp' => $product['mrp'] ?? $finishedProduct->mrp,
                    'sale_price' => $product['sale_rate'] ?? $finishedProduct->sale_rate_a ,
                    'purchase_price' =>  $product['cost'] ?? $finishedProduct->purchase_rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::create($inInventory);
                } else {
                    Inventory::on($branch->connection_name)->create($inInventory);
                }
            }

            \DB::commit();

            return redirect()->route('directreceipt.index')->with('success', 'Direct Receipt created successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Direct Receipt creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating Direct Receipt: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $directReceipt = DirectReceipt::with('ledger')->findOrFail($id);
        } else {
            $directReceipt = DirectReceipt::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }
        
        $directReceipt->products = json_decode($directReceipt->products, true);


        return view('directreceipt.show', compact('directReceipt', 'branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $directReceipt = DirectReceipt::with('ledger')->findOrFail($id);
        } else {
            $directReceipt = DirectReceipt::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }

        $directReceipt->products = json_decode($directReceipt->products, true);
        return view('directreceipt.edit', compact('directReceipt'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        $request->validate([
            'ledger' => 'required|string',
            'dr_no' => 'required|string',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            'cost' => 'required',
            'mrp' => 'required|array',
            'mrp.*' => 'required|numeric',
            'sale_rate' => 'required|array',
            'sale_rate.*' => 'required|numeric',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0',
            'amount' => 'required|array',
            'amount.*' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $products = [];
        $count = count($request->product_id);
        for ($i = 0; $i < $count; $i++) {
            $products[] = [
                'product_search' => $request->product_search[$i],
                'product_id' => $request->product_id[$i],
                'cost' => $request->cost[$i],
                'mrp' => $request->mrp[$i],
                'sale_rate' => $request->sale_rate[$i],
                'qty' => $request->qty[$i],
                'amount' => $request->amount[$i],
            ];
        }

        \DB::beginTransaction();

        try {
            $directReceiptData = [
                'ledger' => $request->ledger,
                'dr_no' => $request->dr_no,
                'date' => $request->date,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'updated_at' => now(),
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $directReceipt = DirectReceipt::findOrFail($id);
                $directReceipt->update($directReceiptData);
            } else {
                $directReceipt = DirectReceipt::on($branch->connection_name)->findOrFail($id);
                $directReceipt->update($directReceiptData);
            }

            \DB::commit();

            return redirect()->route('directreceipt.index')->with('success', 'Direct Receipt updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Direct Receipt update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating Direct Receipt: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $directReceipt = DirectReceipt::findOrFail($id);
                $directReceipt->delete();
            } else {
                $directReceipt = DirectReceipt::on($branch->connection_name)->findOrFail($id);
                $directReceipt->delete();
            }

            return redirect()->route('directreceipt.index')->with('success', 'Direct Receipt deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Direct Receipt deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting Direct Receipt: ' . $e->getMessage());
        }
    }
}
