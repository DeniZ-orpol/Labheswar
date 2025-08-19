<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductionHouse;
use App\Models\Branch;
use App\Models\PurchaseParty;
use App\Models\Stockissue;
use Illuminate\Http\Request;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockissueController extends Controller
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
            $stockIssues = Stockissue::with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $stockIssues = Stockissue::on($branch->connection_name)->with('ledger')->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($request->ajax()) {
            return view('stockissue.rows', compact('stockIssues'))->render();
        }
        return view('stockissue.index', compact('stockIssues'));
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

        $branches = Branch::where('id', '!=', $branch->id)->get();

        return view('stockissue.create', compact(['branches']));
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
            'issue_no' => 'required|string',
            'date' => 'required|date',
            'to_branch' => 'nullable',
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
        $productDeduction = [];
        $count = count($request->product_id);

        // STEP 1: Collect formula products & accumulate ingredient qtys
        for ($i = 0; $i < $count; $i++) {
            $product_id = $request->product_id[$i];
            $requiredQty = $request->qty[$i];

            // Fetch total available stock
            $inventoryEntries = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Inventory::where('product_id', $product_id)->get()
                : Inventory::on($branch->connection_name)->where('product_id', $product_id)->get();

            $totalAvailable = $inventoryEntries->sum('quantity');

            if ($requiredQty > $totalAvailable) {
                $productName = Product::find($product_id)->product_name ?? 'Unknown';
                return back()->with('error', "Insufficient stock for {$productName}. Required: {$requiredQty}, Available: {$totalAvailable}");
            }

            // Store for later deduction
            $productDeduction[] = [
                'product_id' => $product_id,
                'qty' => $requiredQty,
                'amount' => $request->amount[$i],
                'index' => $i
            ];
            $products[] = [
                'product_search' => $request->product_search[$i],
                'product_id' => $product_id,
                'mrp' => $request->mrp[$i],
                'sale_rate' => $request->sale_rate[$i],
                'qty' => $requiredQty,
                'amount' => $request->amount[$i],
            ];
        }

        // Step 2: Transaction to create StockIssue and deduct stock
        \DB::beginTransaction();

        try {
            $ledger = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? PurchaseParty::where('id', $request->ledger)->first()
                : PurchaseParty::on($branch->connection_name)->where('id', $request->ledger)->first();

            $stockIssueData = [
                'ledger' => $request->ledger,
                'issue_no' => $request->issue_no,
                'date' => $request->date,
                'from_branch' => $request->to_branch ? $branch->id : null,
                'to_branch' => $request->to_branch ? $request->to_branch : null,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'status' => $ledger->ledger_category == 'production_house' ? 'pending' : 'transferred',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $stockIssue = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Stockissue::create($stockIssueData)
                : Stockissue::on($branch->connection_name)->create($stockIssueData);

            foreach ($productDeduction as $entry) {
                $productId = $entry['product_id'];
                $qtyToDeduct = $entry['qty'];
                $i = $entry['index'];

                $product = strtoupper($role->role_name) === 'SUPER ADMIN'
                    ? Product::find($productId)
                    : Product::on($branch->connection_name)->find($productId);

                $deduction = [
                    'product_id' => $productId,
                    'type' => 'out',
                    'total_qty' => -$qtyToDeduct,
                    'quantity' => -$qtyToDeduct,
                    'unit' => $product->unit_types ?? 'pcs',
                    'reason' => "Stock issued in #{$request->issue_no}",
                    'gst' => 'off',
                    'gst_p' => 0,
                    'mrp' => $request->mrp[$i] ?? $product->mrp,
                    'sale_price' => $request->sale_rate[$i] ?? $product->sale_rate_a,
                    'purchase_price' => $product->purchase_rate ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::create($deduction);
                } else {
                    Inventory::on($branch->connection_name)->create($deduction);
                }
                if ($ledger->ledger_category == 'production_house') {
                    $productionData = [
                        'product_id' => $productId,
                        'ledger' => $request->ledger,
                        'mrp' => $request->mrp[$i] ?? $product->mrp,
                        'sale_rate' => $request->sale_rate[$i] ?? $product->sale_rate_a,
                        'qty' => $entry['qty'],
                        'amount' => $entry['amount'] ?? 0,
                        'type' => 'in',
                        'date' => $request->date,
                    ];
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        ProductionHouse::create($productionData);
                    } else {
                        ProductionHouse::on($branch->connection_name)->create($productionData);
                    }
                } else {
                    $toBranch = Branch::where('id', $request->to_branch)->first();
                    if ($toBranch) {
                        // Configure the branch connection dynamically
                        configureBranchConnection($toBranch);
                        // Set the correct DB connection to use
                        $isMasterTarget = $toBranch->database_name === env('DB_DATABASE');

                        // Set connection only if not master
                        $toConnection = $isMasterTarget ? null : $toBranch->connection_name;

                        // dd($toConnection);
                        $branchProduct = $isMasterTarget
                            ? Product::where('product_name', $product->product_name)->where('barcode', $product->barcode)->first()
                            : Product::on($toConnection)->where('product_name', $product->product_name)->where('barcode', $product->barcode)->first();
                    } else {
                        // Fallback to master connection if toBranch is null
                        $branchProduct = Product::where('product_name', $product->product_name)->where('barcode', $product->barcode)->first();
                    }

                    if (!$branchProduct) {
                        $newProduct = $product->replicate();

                        $attributes = $newProduct->getAttributes();
                        unset($attributes['id']);
                        unset($attributes['company']);
                        unset($attributes['category_id']);
                        unset($attributes['hsn_code_id']);

                        // Insert the new product and get ID
                        $isMasterTarget
                            ? Product::create($attributes)
                            : Product::on($toConnection)->create($attributes);
                    }
                }
            }

            \DB::commit();

            return redirect()->route('stockissue.index')->with('success', 'Stock Issue created successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Stock Issue creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating Stock Issue: ' . $e->getMessage())->withInput();
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
            $stockIssue = Stockissue::with('ledger')->findOrFail($id);
        } else {
            $stockIssue = Stockissue::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }

        $stockIssue->fromBranch = Branch::find($stockIssue->from_branch);
        $stockIssue->toBranch = Branch::find($stockIssue->to_branch);
        $stockIssue->products = json_decode($stockIssue->products, true);


        return view('stockissue.show', compact('stockIssue', 'branch'));
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
            $stockIssue = Stockissue::with('ledger')->findOrFail($id);
        } else {
            $stockIssue = Stockissue::on($branch->connection_name)->with('ledger')->findOrFail($id);
        }

        $stockIssue->products = json_decode($stockIssue->products, true);
        return view('stockissue.edit', compact('stockIssue'));
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
            'issue_no' => 'required|string',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            // 'cost' => 'required',
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
                // 'cost' => $request->cost[$i],
                'mrp' => $request->mrp[$i],
                'sale_rate' => $request->sale_rate[$i],
                'qty' => $request->qty[$i],
                'amount' => $request->amount[$i],
            ];
        }

        \DB::beginTransaction();

        try {
            $stockIssueData = [
                'ledger' => $request->ledger,
                'issue_no' => $request->issue_no,
                'date' => $request->date,
                'products' => json_encode($products),
                'total_amount' => $request->total_amount,
                'status' => "pending",
                'updated_at' => now(),
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $stockIssue = Stockissue::findOrFail($id);
                $stockIssue->update($stockIssueData);
            } else {
                $stockIssue = Stockissue::on($branch->connection_name)->findOrFail($id);
                $stockIssue->update($stockIssueData);
            }

            \DB::commit();

            return redirect()->route('stockissue.index')->with('success', 'Stock Issue updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Stock Issue update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating Stock Issue: ' . $e->getMessage())->withInput();
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
                $stockIssue = Stockissue::findOrFail($id);
                $stockIssue->delete();
            } else {
                $stockIssue = Stockissue::on($branch->connection_name)->findOrFail($id);
                $stockIssue->delete();
            }

            return redirect()->route('stockissue.index')->with('success', 'Stock Issue deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Stock Issue deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting Stock Issue: ' . $e->getMessage());
        }
    }

    /**
     * Display ledger-wise pending stock issues.
     */
    public function pending()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        $query = strtoupper($role->role_name) === 'SUPER ADMIN'
            ? ProductionHouse::with('ledger')
            : ProductionHouse::on($branch->connection_name)
                ->with('ledger');

        // Filter by ledger_category
        $pendingStock = $query->get()->filter(function ($issue) {
            return $issue->getRelation('ledger') && $issue->getRelation('ledger')->ledger_category === 'production_house';
        });

        $pendingStock = $pendingStock->groupBy('ledger');

        $pendingTotals = [];
        $qtyTotals = [];

        // Loop through each ledger to aggregate data
        foreach ($pendingStock as $ledgerId => $stock) {
            // Initialize total variables
            $totalQty = 0;
            $totalAmount = 0;

            // Sum qty and amount based on stock type (in or out)
            foreach ($stock as $item) {
                if ($item->type === 'in') {
                    $totalQty += $item->qty;    // Add quantity for 'in' type
                    $totalAmount += $item->amount;  // Add amount for 'in' type
                } elseif ($item->type === 'out') {
                    $totalQty -= $item->qty;    // Subtract quantity for 'out' type
                    $totalAmount -= $item->amount;  // Subtract amount for 'out' type
                }
            }

            // Store the results for each ledger
            $pendingTotals[$ledgerId] = $totalAmount;
        }

        return view('stockissue.pending', compact('pendingStock', 'pendingTotals'));
    }

    /**
     * Display product-wise pending stock issues for a specific ledger.
     */
    public function pendingLedgerDetails($ledgerId)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        $query = strtoupper($role->role_name) === 'SUPER ADMIN'
            ? ProductionHouse::with(['ledger', 'product'])->where('ledger', $ledgerId)
            : ProductionHouse::on($branch->connection_name)->with('ledger')->where('ledger', $ledgerId);

        $stockIssues = $query->get();

        if ($stockIssues->isEmpty() || !$stockIssues->first()->getRelation('ledger') || $stockIssues->first()->getRelation('ledger')->ledger_category !== 'production_house') {
            abort(404, 'Ledger not found or not a production house.');
        }

        $groupedProducts = [];
        $ledgerName = $stockIssues->first()->getRelation('ledger')->party_name ?? "NA";

        foreach ($stockIssues as $product) {

            $key = md5(strtolower($product->product_id ?? '') . '_' . ($product->mrp ?? 0) . '_' . ($product->sale_rate ?? 0));
            if (!isset($groupedProducts[$key])) {
                $groupedProducts[$key] = [
                    'product' => $product->product->product_name ?? 'N/A',
                    'mrp' => $product->mrp ?? 0,
                    'sale_rate' => $product->sale_rate ?? 0,
                    'qty' => 0,
                    'amount' => 0,
                ];
            }

            if ($product->type === 'in') {
                $groupedProducts[$key]['qty'] += $product->qty ?? 0;
                $groupedProducts[$key]['amount'] += $product->amount ?? 0;
            } elseif ($product->type === 'out') {
                $groupedProducts[$key]['qty'] -= $product->qty ?? 0; // Subtract for 'out'
                $groupedProducts[$key]['amount'] -= $product->amount ?? 0; // Subtract for 'out'
            }
        }

        $mergedProductList = array_values($groupedProducts);

        return view('stockissue.pending_ledger_details', compact('mergedProductList', 'ledgerName'));
    }

    /**
     * Search for stock issue receipt when stock transfer to other branch for creating stock receipt
     * @param \Illuminate\Http\Request $request
     * @param mixed $branchId
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, $branchId)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $role = $auth['role'];
        $branch = $auth['branch'];

        $sourceBranch = Branch::find($branchId);
        if (!$sourceBranch) {
            return response()->json(['error' => 'Source branch not found'], 404);
        }

        if (strtolower($sourceBranch->code) == 'master') {
            $connectionName = config('database.default');
        } else {
            configureBranchConnection($sourceBranch);
            $connectionName = $sourceBranch->connection_name;
        }

        // Base query for stock issues using the SOURCE branch connection
        $query = Stockissue::on($connectionName);

        // Filter by transfers FROM this source branch
        $stockIssue = $query->where('from_branch', $branchId)
            ->where('issue_no', $request->input('issue_no'))
            ->first();

        if ($stockIssue) {
            $products = json_decode($stockIssue->products, true);

            // Map source branch product IDs to current login branch product IDs
            foreach ($products as &$product) {
                $currentProduct = Product::on($branch->connection_name)
                    ->where('product_name', $product['product_search'])
                    ->first();

                if ($currentProduct) {
                    $product['product_id'] = $currentProduct->id;
                } else {
                    $product['product_id'] = null;
                }
            }
            unset($product);

            // Assign back to the model
            $stockIssue->products = $products;
        }

        return response()->json($stockIssue);
    }

}
