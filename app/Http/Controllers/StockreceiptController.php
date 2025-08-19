<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Formula;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Stockreceipt;
use Illuminate\Http\Request;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockreceiptController extends Controller
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
            $stockReceipts = Stockreceipt::with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $stockReceipts = Stockreceipt::on($branch->connection_name)->with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        }
        if ($request->ajax()) {
            return view('stockreceipt.rows', compact('stockReceipts'));
        }

        return view('stockreceipt.index', compact('stockReceipts'));
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

        $branches = Branch::whereNot('id', $branch->id)->get();

        return view('stockreceipt.create', compact(['branches', 'branch']));
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
            'date' => 'required|date',
            'entry_no' => 'required|string',
            'from_branch' => 'nullable',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
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
            $productId = $request->product_id[$i];
            $receivedQty = $request->qty[$i];

            $products[] = [
                'product_search' => $request->product_search[$i],
                'product_id' => $productId,
                'mrp' => $request->mrp[$i],
                'sale_rate' => $request->sale_rate[$i],
                'qty' => $receivedQty,
                'amount' => $request->amount[$i],
            ];
        }

        // Step 2: Transaction to create StockReceipt and add stock
        DB::beginTransaction();

        try {
            $stockReceiptData = [
                'ledger' => $request->ledger,
                'entry_no' => $request->entry_no,
                'date' => $request->date,
                'from_branch' => $request->from_branch ? $request->from_branch : null,
                'to_branch' => $request->from_branch ? $branch->id : null,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $stockReceipt = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Stockreceipt::create($stockReceiptData)
                : Stockreceipt::on($branch->connection_name)->create($stockReceiptData);

            // Add inventory for ingredients
            foreach ($products as $product) {
                // Get product details
                $rawProduct = strtoupper($role->role_name) === 'SUPER ADMIN'
                    ? Product::find($product['product_id'])
                    : Product::on($branch->connection_name)->find($product['product_id']);

                if ($rawProduct) {
                    $addition = [
                        'product_id' => $product['product_id'], // Fixed: use array notation
                        'type' => 'in',
                        'total_qty' => $product['qty'], // Fixed: use array notation
                        'quantity' => $product['qty'], // Fixed: use array notation
                        'unit' => $rawProduct->unit_types ?? 'pcs',
                        'reason' => "Received in stock receipt #{$request->entry_no}", // Fixed: use entry_no
                        'gst' => 'off',
                        'gst_p' => 0,
                        'mrp' => $product['mrp'], // Use from products array
                        'sale_price' => $product['sale_rate'], // Use from products array
                        'purchase_price' => $rawProduct->purchase_rate ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Create inventory entry
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::create($addition);
                    } else {
                        Inventory::on($branch->connection_name)->create($addition);
                    }
                }
            }

            DB::commit();

            return redirect()->route('stockreceipt.index')->with('success', 'Stock Receipt created successfully.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Stock Receipt creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating Stock Receipt: ' . $e->getMessage())->withInput();
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
            $stockReceipt = Stockreceipt::with('ledger')->findOrFail($id);
        } else {
            $stockReceipt = Stockreceipt::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }

        $stockReceipt->products = json_decode($stockReceipt->products, true);

        return view('stockreceipt.show', compact('stockReceipt', 'branch'));
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
            $stockReceipt = Stockreceipt::with('ledger')->findOrFail($id);
        } else {
            $stockReceipt = Stockreceipt::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }

        $stockReceipt->products = json_decode($stockReceipt->products, true);
        return view('stockreceipt.edit', compact('stockReceipt'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        $request->validate([
            'ledger' => 'required|string',
            'receipt_no' => 'required|string',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            'mrp' => 'required|array',
            'mrp.*' => 'required|numeric',
            'purchase_rate' => 'required|array',
            'purchase_rate.*' => 'required|numeric',
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
                'mrp' => $request->mrp[$i],
                'purchase_rate' => $request->purchase_rate[$i],
                'qty' => $request->qty[$i],
                'amount' => $request->amount[$i],
            ];
        }

        DB::beginTransaction();

        try {
            $stockReceiptData = [
                'ledger' => $request->ledger,
                'receipt_no' => $request->receipt_no,
                'date' => $request->date,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'status' => "pending",
                'updated_at' => now(),
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $stockReceipt = Stockreceipt::findOrFail($id);
                $stockReceipt->update($stockReceiptData);
            } else {
                $stockReceipt = Stockreceipt::on($branch->connection_name)->findOrFail($id);
                $stockReceipt->update($stockReceiptData);
            }

            DB::commit();

            return redirect()->route('stockreceipt.index')->with('success', 'Stock Receipt updated successfully.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Stock Receipt update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating Stock Receipt: ' . $e->getMessage())->withInput();
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
                $stockReceipt = Stockreceipt::findOrFail($id);
                $stockReceipt->delete();
            } else {
                $stockReceipt = Stockreceipt::on($branch->connection_name)->findOrFail($id);
                $stockReceipt->delete();
            }

            return redirect()->route('stockreceipt.index')->with('success', 'Stock Receipt deleted successfully.');
        } catch (Exception $e) {
            Log::error('Stock Receipt deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting Stock Receipt: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of pending stock receipts grouped by ledger.
     */
    public function pending()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $ledgerData = Stockreceipt::select('ledger')
                ->selectRaw('COUNT(*) as pending_count')
                ->where('status', 'pending')
                ->groupBy('ledger')
                ->with('ledger')
                ->paginate(10);

            $ledgerTotals = [];

            foreach ($ledgerData as $ledgerItem) {
                $ledger = $ledgerItem->ledger;
                $stockReceipts = Stockreceipt::where('ledger', $ledger)
                    ->where('status', 'pending')
                    ->get();

                $totalIssuePrice = 0;

                foreach ($stockReceipts as $stockReceipt) {
                    $products = is_array($stockReceipt->products) ? $stockReceipt->products : json_decode($stockReceipt->products, true);

                    foreach ($products as $product) {
                        $formulaId = $product['product_id'];
                        $receivedQty = $product['qty'];

                        $formula = Formula::find($formulaId);
                        if (!$formula) {
                            continue;
                        }

                        $ingredients = is_array($formula->ingredients)
                            ? $formula->ingredients
                            : json_decode($formula->ingredients, true);

                        foreach ($ingredients as $ingredient) {
                            $price = 0;
                            $productId = $ingredient['product_id'];
                            $requiredQty = $ingredient['quantity'] * $receivedQty;

                            $productModel = Product::find($productId);
                            if ($productModel) {
                                $price = $productModel->purchase_rate ?? 0;
                            }

                            $totalIssuePrice += $price * $requiredQty;
                        }
                    }
                }

                $ledgerTotals[$ledger] = $totalIssuePrice;
            }
        } else {
            $ledgerData = Stockreceipt::on($branch->connection_name)
                ->select('ledger')
                ->selectRaw('COUNT(*) as pending_count')
                ->where('status', 'pending')
                ->groupBy('ledger')
                ->with('ledger')
                ->paginate(10);

            $ledgerTotals = [];

            foreach ($ledgerData as $ledgerItem) {
                $ledger = $ledgerItem->ledger;
                $stockReceipts = Stockreceipt::on($branch->connection_name)
                    ->where('ledger', $ledger)
                    ->where('status', 'pending')
                    ->get();

                $totalIssuePrice = 0;

                foreach ($stockReceipts as $stockReceipt) {
                    $products = is_array($stockReceipt->products) ? $stockReceipt->products : json_decode($stockReceipt->products, true);

                    foreach ($products as $product) {
                        $formulaId = $product['product_id'];
                        $receivedQty = $product['qty'];

                        $formula = Formula::on($branch->connection_name)->find($formulaId);
                        if (!$formula) {
                            continue;
                        }

                        $ingredients = is_array($formula->ingredients)
                            ? $formula->ingredients
                            : json_decode($formula->ingredients, true);

                        foreach ($ingredients as $ingredient) {
                            $price = 0;
                            $productId = $ingredient['product_id'];
                            $requiredQty = $ingredient['quantity'] * $receivedQty;

                            $productModel = Product::on($branch->connection_name)->find($productId);
                            if ($productModel) {
                                $price = $productModel->purchase_rate ?? 0;
                            }

                            $totalIssuePrice += $price * $requiredQty;
                        }
                    }
                }

                $ledgerTotals[$ledger] = $totalIssuePrice;
            }
        }

        return view('stockreceipt.pending', ['ledgerData' => $ledgerData, 'ledgerTotals' => $ledgerTotals]);
    }

    /**
     * Display detailed pending stock receipt info for a ledger, including stock calculation.
     */
    public function ledgerPendingDetails($ledger)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $stockReceipts = Stockreceipt::where('ledger', $ledger)
                ->where('status', 'pending')
                ->get();
        } else {
            $stockReceipts = Stockreceipt::on($branch->connection_name)
                ->where('ledger', $ledger)
                ->where('status', 'pending')
                ->get();
        }

        $ingredientTotals = []; // key: product_id, value: total_required_qty
        $formulasData = []; // key: formula_id, value: ['formula' => Formula, 'issued_qty' => total issued qty]

        foreach ($stockReceipts as $stockReceipt) {
            $products = is_array($stockReceipt->products) ? $stockReceipt->products : json_decode($stockReceipt->products, true);

            foreach ($products as $product) {
                $formulaId = $product['product_id'];
                $receivedQty = $product['qty'];

                $formula = strtoupper($role->role_name) === 'SUPER ADMIN'
                    ? Formula::find($formulaId)
                    : Formula::on($branch->connection_name)->find($formulaId);

                if (!$formula) {
                    continue;
                }

                if (!isset($formulasData[$formulaId])) {
                    $formulasData[$formulaId] = [
                        'formula' => $formula,
                        'issued_qty' => 0,
                    ];
                }
                $formulasData[$formulaId]['issued_qty'] += $receivedQty;

                $ingredients = is_array($formula->ingredients)
                    ? $formula->ingredients
                    : json_decode($formula->ingredients, true);

                foreach ($ingredients as $ingredient) {
                    $ingredientProductId = $ingredient['product_id'];
                    $requiredQty = $ingredient['quantity'] * $receivedQty;

                    if (!isset($ingredientTotals[$ingredientProductId])) {
                        $ingredientTotals[$ingredientProductId] = 0;
                    }

                    $ingredientTotals[$ingredientProductId] += $requiredQty;
                }
            }
        }

        $stockAvailability = [];

        foreach ($ingredientTotals as $productId => $totalRequiredQty) {
            $inventoryEntries = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Inventory::where('product_id', $productId)->get()
                : Inventory::on($branch->connection_name)->where('product_id', $productId)->get();

            $availableQty = $inventoryEntries->sum('quantity');

            $product = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Product::find($productId)
                : Product::on($branch->connection_name)->find($productId);

            $stockAvailability[] = [
                'product' => $product,
                'required_qty' => $totalRequiredQty,
                'available_qty' => $availableQty,
            ];
        }

        return view('stockreceipt.ledger_pending_details', compact('ledger', 'stockAvailability', 'formulasData'));
    }
}
