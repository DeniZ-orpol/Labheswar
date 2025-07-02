<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\PurchaseReceipt;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
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
            $parties = PurchaseParty::get();
            $purchaseReceipt = PurchaseReceipt::with(['purchaseParty', 'createUser', 'updateUser'])
                ->orderByDesc('id')->paginate(10);
        } else {
            $parties = PurchaseParty::on($branch->connection_name)->get();
            $purchaseReceipt = PurchaseReceipt::on($branch->connection_name)
                ->with(['purchaseParty', 'createUser', 'updateUser'])
                ->orderByDesc('id')->paginate(10);
        }

        return view('purchase.index', compact(['parties', 'purchaseReceipt']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $parties = PurchaseParty::get();
            $products = Product::get();
            $purchaseItems = Purchase::get();
        } else {
            $parties = PurchaseParty::on($branch->connection_name)->get();
            $products = Product::on($branch->connection_name)->get();
            $purchaseItems = Purchase::on($branch->connection_name)->get();
        }

        return view('purchase.create', compact(['parties', 'products', 'purchaseItems']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if user is logged in as branch
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];
            $role = $auth['role'];

            // Validate the request - including calculated fields from frontend
            $validate = $request->validate([
                'bill_date' => 'date',
                'party_name' => 'required|string|max:255',
                'bill_no' => 'required|string|max:255',
                'delivery_date' => 'nullable|date',
                'gst' => 'required|string|in:on,off',

                // Receipt totals (calculated in frontend)
                'receipt_subtotal' => 'required|numeric|min:0',
                'receipt_total_discount' => 'required|numeric|min:0',
                'receipt_total_gst_amount' => 'required|numeric|min:0',
                'receipt_total_amount' => 'required|numeric|min:0',

                // Array validation for multiple purchase items
                'product' => 'required|array|min:1',
                'product.*' => 'required',
                'box' => 'array',
                'box.*' => 'nullable|numeric|min:0',
                'pcs' => 'array',
                'pcs.*' => 'nullable|numeric|min:0',
                'free' => 'array',
                'free.*' => 'nullable|numeric|min:0',
                'purchase_rate' => 'array',
                'purchase_rate.*' => 'numeric|min:0',
                'discount_percent' => 'array',
                'discount_percent.*' => 'nullable|numeric|min:0|max:100',
                'discount_lumpsum' => 'array',
                'discount_lumpsum.*' => 'nullable|numeric|min:0',
                'amount' => 'array',
                'amount.*' => 'numeric|min:0',

                // Calculated fields from frontend
                'total_pcs' => 'array',
                'total_pcs.*' => 'numeric|min:0',
                'base_amount' => 'array',
                'base_amount.*' => 'numeric|min:0',
                'discount_amount' => 'array',
                'discount_amount.*' => 'numeric|min:0',
                'sgst_rate' => 'array',
                'sgst_rate.*' => 'numeric|min:0',
                'cgst_rate' => 'array',
                'cgst_rate.*' => 'numeric|min:0',
                'sgst_amount' => 'array',
                'sgst_amount.*' => 'numeric|min:0',
                'cgst_amount' => 'array',
                'cgst_amount.*' => 'numeric|min:0',
                'final_amount' => 'array',
                'final_amount.*' => 'numeric|min:0',
            ]);

            \DB::beginTransaction();

            try {
                // Create purchase receipt with calculated totals from frontend
                $purchaseReceiptData = [
                    'bill_date' => $validate['bill_date'],
                    'purchase_party_id' => $validate['party_name'],
                    'bill_no' => $validate['bill_no'],
                    'delivery_date' => $validate['delivery_date'],
                    'gst_status' => $validate['gst'],

                    // Use calculated totals from frontend
                    'subtotal' => $validate['receipt_subtotal'],
                    'total_discount' => $validate['receipt_total_discount'],
                    'total_gst_amount' => $validate['receipt_total_gst_amount'],
                    'total_amount' => $validate['receipt_total_amount'],

                    'receipt_status' => 'completed',
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $purchaseReceiptId = PurchaseReceipt::insertGetId($purchaseReceiptData);
                } else {
                    $purchaseReceiptId = PurchaseReceipt::on($branch->connection_name)->insertGetId($purchaseReceiptData);
                }

                // Loop through each product and create purchase records with calculated values
                foreach ($validate['product'] as $index => $productId) {
                    // Get product details for reference

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $product = Product::find($productId);
                    } else {
                        $product = Product::on($branch->connection_name)->find($productId);
                    }

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Calculate total quantity including free
                    $boxQuantity = $validate['box'][$index] ?? 0;
                    $pcsQuantity = $validate['pcs'][$index] ?? 0;
                    $freeQuantity = $validate['free'][$index] ?? 0;
                    $totalPcs = $validate['total_pcs'][$index] ?? 0;
                    $totalWithFree = $totalPcs + $freeQuantity;

                    // Create purchase record with calculated values from frontend
                    $purchaseData = [
                        'bill_date' => $validate['bill_date'],
                        'purchase_receipt_id' => $purchaseReceiptId,
                        // 'purchase_party_id' => $validate['party_name'],
                        'bill_no' => $validate['bill_no'],
                        'delivery_date' => $validate['delivery_date'],
                        'gst' => $validate['gst'],
                        'product_id' => $productId,
                        'product' => $product->product_name,
                        'mrp' => $product->mrp ?? 0,

                        // Original form values
                        'box' => $boxQuantity,
                        'pcs' => $pcsQuantity,
                        'free' => $freeQuantity,
                        'p_rate' => $validate['purchase_rate'][$index] ?? 0,
                        'discount' => $validate['discount_percent'][$index] ?? 0,
                        'lumpsum' => $validate['discount_lumpsum'][$index] ?? 0,

                        // Calculated values from frontend (store these for future reference)
                        'amount' => $validate['final_amount'][$index], // Final calculated amount

                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Purchase::insert($purchaseData);
                    } else {
                        Purchase::on($branch->connection_name)->insert($purchaseData);
                    }

                    if ($totalWithFree > 0) {
                        $inventoryData = [
                            'product_id' => $productId,
                            'type' => 'in', // or 'in' - adjust based on your inventory types
                            'quantity' => $totalWithFree,
                            'unit' => $product->unit_types ?? 'pcs',
                            'reason' => 'Purchase Bill #' . $validate['bill_no'] . ' - Receipt #' . $purchaseReceiptId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Inventory::create($inventoryData);
                        } else {
                            Inventory::on($branch->connection_name)->create($inventoryData);
                        }
                    }

                    // Optional: Update product stock if needed
                    // $totalPcsWithFree = $validate['total_pcs'][$index] + ($validate['free'][$index] ?? 0);
                    // \DB::connection($branchConnection)->table('products')
                    //     ->where('id', $productId)
                    //     ->increment('stock_quantity', $totalPcsWithFree);
                }

                \DB::commit();

                return redirect()->route('purchase.index')
                    ->with('success', 'Purchase Receipt #' . $purchaseReceiptId . ' created successfully in ' . session('branch_name') . '! Total Amount: ₹' . number_format($validate['receipt_total_amount'], 2));
            } catch (Exception $e) {
                dd($e->getMessage());
                \DB::rollback();
                \Log::error('Purchase creation failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error creating purchase: ' . $e->getMessage())
                    ->withInput();
            }
        } catch (ValidationException $e) {
            dd($e->getMessage());
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form fields.');
        } catch (Exception $ex) {
            dd($e->getMessage());
            \Log::error('Purchase store error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating purchase: ' . $ex->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $parties = PurchaseParty::get();
            $products = Product::get();
            $purchaseReceipt = PurchaseReceipt::withDynamic(['purchaseParty', 'createUser', 'updateUser'])
                ->where('id', $id)
                ->first();

            $purchaseItems = Purchase::with('product')
                ->where('purchase_receipt_id', $id)
                ->get()
                ->map(function ($item) {
                    $item->productInfo = $item->getRelation('product'); // alias it manually
                    return $item;
                });
        } else {
            $parties = PurchaseParty::on($branch->connection_name)->get();
            $products = Product::on($branch->connection_name)->get();
            $purchaseReceipt = PurchaseReceipt::on($branch->connection_name)
                ->withDynamic(['purchaseParty', 'createUser', 'updateUser'])
                ->where('id', $id)
                ->first();

            $purchaseItems = Purchase::on($branch->connection_name)
                ->with('product')
                ->where('purchase_receipt_id', $id)
                ->get()
                ->map(function ($item) {
                    $item->productInfo = $item->getRelation('product'); // alias it manually
                    return $item;
                });
        }

        return view('purchase.edit', compact(['products', 'purchaseReceipt', 'purchaseItems']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if user is logged in as branch
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];
            $role = $auth['role'];

            // Validate the request - including calculated fields from frontend
            $validate = $request->validate([
                'bill_date' => 'required|date',
                'party_name' => 'required|string|max:255',
                'bill_no' => 'required|string|max:255',
                'delivery_date' => 'nullable|date',
                'gst' => 'required|string|in:on,off',

                // Receipt totals (calculated in frontend)
                'receipt_subtotal' => 'required|numeric|min:0',
                'receipt_total_discount' => 'required|numeric|min:0',
                'receipt_total_gst_amount' => 'required|numeric|min:0',
                'receipt_total_amount' => 'required|numeric|min:0',

                // Array validation for multiple purchase items
                'product' => 'required|array|min:1',
                'product.*' => 'required',
                'box' => 'required|array',
                'box.*' => 'nullable|numeric|min:0',
                'pcs' => 'required|array',
                'pcs.*' => 'nullable|numeric|min:0',
                'free' => 'required|array',
                'free.*' => 'nullable|numeric|min:0',
                'purchase_rate' => 'required|array',
                'purchase_rate.*' => 'required|numeric|min:0',
                'discount_percent' => 'required|array',
                'discount_percent.*' => 'nullable|numeric|min:0|max:100',
                'discount_lumpsum' => 'required|array',
                'discount_lumpsum.*' => 'nullable|numeric|min:0',
                'amount' => 'required|array',
                'amount.*' => 'required|numeric|min:0',

                // Calculated fields from frontend
                'total_pcs' => 'required|array',
                'total_pcs.*' => 'required|numeric|min:0',
                'base_amount' => 'required|array',
                'base_amount.*' => 'required|numeric|min:0',
                'discount_amount' => 'required|array',
                'discount_amount.*' => 'required|numeric|min:0',
                'sgst_rate' => 'required|array',
                'sgst_rate.*' => 'required|numeric|min:0',
                'cgst_rate' => 'required|array',
                'cgst_rate.*' => 'required|numeric|min:0',
                'sgst_amount' => 'required|array',
                'sgst_amount.*' => 'required|numeric|min:0',
                'cgst_amount' => 'required|array',
                'cgst_amount.*' => 'required|numeric|min:0',
                'final_amount' => 'required|array',
                'final_amount.*' => 'required|numeric|min:0',

                // Optional: Purchase item IDs for updating existing records
                'purchase_item_ids' => 'nullable|array',
                'purchase_item_ids.*' => 'nullable|numeric',
            ]);

            // Check if purchase receipt exists
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $purchaseReceipt = PurchaseReceipt::where('id', $id)->first();
            } else {
                $purchaseReceipt = PurchaseReceipt::on($branch->connection_name)
                    ->where('id', $id)
                    ->first();
            }

            if (!$purchaseReceipt) {
                return redirect()->route('purchase.index')
                    ->with('error', 'Purchase receipt not found.');
            }

            \DB::beginTransaction();

            try {
                $purchaseReceiptData = [
                    'bill_date' => $validate['bill_date'],
                    // 'purchase_party_id' => $validate['party_name'],
                    'bill_no' => $validate['bill_no'],
                    'delivery_date' => $validate['delivery_date'],
                    'gst_status' => $validate['gst'],

                    // Use calculated totals from frontend
                    'subtotal' => $validate['receipt_subtotal'],
                    'total_discount' => $validate['receipt_total_discount'],
                    'total_gst_amount' => $validate['receipt_total_gst_amount'],
                    'total_amount' => $validate['receipt_total_amount'],

                    'updated_by' => session('branch_user_id'),
                    'updated_at' => now(),
                ];
                // Update purchase receipt with calculated totals from frontend

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    PurchaseReceipt::where('id', $id)
                        ->update($purchaseReceiptData);

                    // Get existing purchase items
                    $existingItems = Purchase::where('purchase_receipt_id', $id)
                        ->get()
                        ->keyBy('id');
                } else {
                    PurchaseReceipt::on($branch->connection_name)
                        ->where('id', $id)
                        ->update($purchaseReceiptData);

                    // Get existing purchase items
                    $existingItems = Purchase::on($branch->connection_name)
                        ->where('purchase_receipt_id', $id)
                        ->get()
                        ->keyBy('id');
                }

                $processedItemIds = [];

                // Process each product from the form
                foreach ($validate['product'] as $index => $productId) {
                    // Get product details for reference
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $product = Product::find($productId);
                    } else {
                        $product = Product::on($branch->connection_name)->find($productId);
                    }

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Check if this is an existing item or new item
                    $itemId = $validate['purchase_item_ids'][$index] ?? null;

                    $purchaseData = [
                        'bill_date' => $validate['bill_date'],
                        // 'purchase_party_id' => $validate['party_name'],
                        'bill_no' => $validate['bill_no'],
                        'delivery_date' => $validate['delivery_date'],
                        'gst' => $validate['gst'],
                        'product_id' => $productId,
                        'product' => $product->product_name,
                        'mrp' => $product->mrp ?? 0,

                        // Original form values
                        'box' => $validate['box'][$index] ?? 0,
                        'pcs' => $validate['pcs'][$index] ?? 0,
                        'free' => $validate['free'][$index] ?? 0,
                        'p_rate' => $validate['purchase_rate'][$index] ?? 0,
                        'discount' => $validate['discount_percent'][$index] ?? 0,
                        'lumpsum' => $validate['discount_lumpsum'][$index] ?? 0,

                        // Calculated values from frontend
                        // 'total_pcs' => $validate['total_pcs'][$index],
                        // 'base_amount' => $validate['base_amount'][$index],
                        // 'discount_amount' => $validate['discount_amount'][$index],
                        // 'sgst_rate' => $validate['sgst_rate'][$index],
                        // 'cgst_rate' => $validate['cgst_rate'][$index],
                        // 'sgst_amount' => $validate['sgst_amount'][$index],
                        // 'cgst_amount' => $validate['cgst_amount'][$index],
                        'amount' => $validate['final_amount'][$index],

                        'updated_at' => now(),
                    ];

                    if ($itemId && isset($existingItems[$itemId])) {
                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            // Update existing item
                            Purchase::where('id', $itemId)
                                ->where('purchase_receipt_id', $id)
                                ->update($purchaseData);

                            $processedItemIds[] = $itemId;
                        } else {
                            // Update existing item
                            Purchase::on($branch->connection_name)
                                ->where('id', $itemId)
                                ->where('purchase_receipt_id', $id)
                                ->update($purchaseData);

                            $processedItemIds[] = $itemId;
                        }

                    } else {
                        // Create new item
                        $purchaseData['purchase_receipt_id'] = $id;
                        $purchaseData['created_at'] = now();

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            $newItemId = Purchase::insertGetId($purchaseData);
                        } else {
                            $newItemId = Purchase::on($branch->connection_name)->insertGetId($purchaseData);
                        }
                        $processedItemIds[] = $newItemId;
                    }
                }

                // Delete items that were removed (not in the current form submission)
                $itemsToDelete = $existingItems->keys()->diff($processedItemIds);
                if ($itemsToDelete->isNotEmpty()) {

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Purchase::whereIn('id', $itemsToDelete->toArray())
                            ->where('purchase_receipt_id', $id)
                            ->delete();
                    } else {
                        Purchase::on($branch->connection_name)
                            ->whereIn('id', $itemsToDelete->toArray())
                            ->where('purchase_receipt_id', $id)
                            ->delete();
                    }
                }

                \DB::commit();

                return redirect()->route('purchase.index')
                    ->with('success', 'Purchase Receipt #' . $id . ' updated successfully! Total Amount: ₹' . number_format($validate['receipt_total_amount'], 2));
            } catch (Exception $e) {
                dd('aaa: ', $e->getMessage());
                \DB::rollback();
                \Log::error('Purchase update failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error updating purchase: ' . $e->getMessage())
                    ->withInput();
            }
        } catch (ValidationException $e) {
            dd('validate: ', $e->getMessage());
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form fields.');
        } catch (Exception $ex) {
            dd('ccc: ', $ex->getMessage());
            \Log::error('Purchase update error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating purchase: ' . $ex->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Check if user is logged in as branch
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];
            $role = $auth['role'];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                // Check if purchase receipt exists
                $purchaseReceipt = PurchaseReceipt::where('id', $id)->first();
            } else {
                // Check if purchase receipt exists
                $purchaseReceipt = PurchaseReceipt::on($branch->connection_name)
                    ->where('id', $id)
                    ->first();
            }

            if (!$purchaseReceipt) {
                return redirect()->route('purchase.index')
                    ->with('error', 'Purchase receipt not found.');
            }

            \DB::beginTransaction();

            try {
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    // Delete purchase items first
                    Purchase::where('purchase_receipt_id', $id)->delete();

                    // Delete purchase receipt
                    PurchaseReceipt::where('id', $id)->delete();
                } else {
                    // Delete purchase items first
                    Purchase::on($branch->connection_name)
                        ->where('purchase_receipt_id', $id)
                        ->delete();

                    // Delete purchase receipt
                    PurchaseReceipt::on($branch->connection_name)
                        ->where('id', $id)
                        ->delete();
                }

                \DB::commit();

                return redirect()->route('purchase.index')
                    ->with('success', 'Purchase Receipt #' . $id . ' deleted successfully!');
            } catch (Exception $e) {
                \DB::rollback();
                \Log::error('Purchase deletion failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error deleting purchase: ' . $e->getMessage());
            }
        } catch (Exception $ex) {
            \Log::error('Purchase destroy error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting purchase: ' . $ex->getMessage());
        }
    }

    public function getPurchaseHistory(Request $request)
    {
        $authResult = $this->authenticateAndConfigureBranch();

        if (isset($authResult['success']) && $authResult['success'] === false) {
            return response()->json($authResult);
        }

        $user = $authResult['user'];
        $branch = $authResult['branch'];
        $role = $authResult['role'];
        $productId = $request->get('product_id');

        if (!$productId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID is required'
            ]);
        }

        try {
            $purchaseHistory = collect();

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                // Super admin can see history from all active branches
                $branches = $branch; // This is a collection for super admin

                foreach ($branches as $branchItem) {
                    if (!testBranchConnection($branchItem)) {
                        continue;
                    }

                    $branchConnection = getBranchConnection($branchItem);

                    // Get purchase history from purchase_details and purchase_receipts tables
                    $branchHistory = DB::table('purchase_details as pd')
                        ->join('purchase_receipts as pr', 'pd.purchase_receipt_id', '=', 'pr.id')
                        ->leftJoin('parties as p', 'pr.party_id', '=', 'p.id')
                        ->where('pd.product_id', $productId)
                        ->select([
                            'pr.bill_date',
                            'pr.bill_no',
                            'p.party_name',
                            'pd.box',
                            'pd.pcs',
                            'pd.total_pcs',
                            'pd.purchase_rate',
                            'pd.final_amount',
                            'pr.created_at',
                            DB::raw("'{$branchItem->branch_name}' as branch_name")
                        ])
                        ->orderBy('pr.created_at', 'desc')
                        ->limit(10) // Get more from each branch, we'll limit final result
                        ->get();

                    $purchaseHistory = $purchaseHistory->merge($branchHistory);
                }

                // Sort all results by date and take latest 3
                $purchaseHistory = $purchaseHistory->sortByDesc('created_at')->take(3);
            } else {
                // Regular user - only their branch history
                if (!testBranchConnection($branch)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot connect to branch database'
                    ]);
                }

                $branchConnection = getBranchConnection($branch);

                $purchaseHistory = $branchConnection->table('purchase as pd')
                    ->join('purchase_receipt as pr', 'pd.purchase_receipt_id', '=', 'pr.id')
                    ->leftJoin('purchase_party as p', 'pr.purchase_party_id', '=', 'p.id')
                    ->where('pd.product_id', $productId)
                    ->select([
                        'pr.bill_date',
                        'pr.bill_no',
                        'p.party_name',
                        'pd.box',
                        'pd.pcs',
                        'pd.p_rate',
                        'pd.discount',
                        'pd.lumpsum',
                        'pd.amount',
                        'pr.created_at'
                    ])
                    ->orderBy('pr.created_at', 'desc')
                    ->limit(3)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'history' => $purchaseHistory->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching purchase history: ' . $e->getMessage()
            ]);
        }
    }
}
