<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\PurchaseReceipt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $parties = PurchaseParty::forDatabase($branchConnection)->get();
        $purchaseReceipt = PurchaseReceipt::forDatabase($branchConnection)->orderByDesc('id')->paginate(10);

        if ($purchaseReceipt->isNotEmpty()) {
            $purchaseReceiptModel = new PurchaseParty();
            $purchaseReceiptModel->setDynamicTable($branchConnection);
            $purchaseReceiptModel->loadRelationsForPaginator($purchaseReceipt, ['purchaseParty', 'createUser', 'updateUser']);
        }
        // ->withDynamic(['purchaseParty', 'createUser', 'updateUser'])
        // ->orderByDesc('id')
        // ->get();
        // dd($purchaseReceipt);
        // $purchase = Purchase::on($branchConnection)->with('purchaseReceipt')->paginate();

        return view('purchase.index', compact(['parties', 'purchaseReceipt']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $parties = PurchaseParty::forDatabase($branchConnection)->get();
        $products = Product::forDatabase($branchConnection)->get();
        // dd($products);

        return view('purchase.create', compact(['parties', 'products']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if user is logged in as branch
            if (session('user_type') !== 'branch' || !session('branch_connection')) {
                return redirect()->back()->with('error', 'Branch session not found. Please login again.');
            }

            // Get branch connection name from session
            $branchConnection = session('branch_connection');

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
                'product.*' => 'required|exists:products,id',
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
                $purchaseReceiptId = PurchaseReceipt::forDatabase($branchConnection)->insertGetId([
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
                    'created_by' => session('branch_user_id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Loop through each product and create purchase records with calculated values
                foreach ($validate['product'] as $index => $productId) {
                    // Get product details for reference
                    $product = Product::forDatabase($branchConnection)->find($productId);

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Create purchase record with calculated values from frontend
                    Purchase::forDatabase($branchConnection)->insert([
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
                        'box' => $validate['box'][$index] ?? 0,
                        'pcs' => $validate['pcs'][$index] ?? 0,
                        'free' => $validate['free'][$index] ?? 0,
                        'p_rate' => $validate['purchase_rate'][$index] ?? 0,
                        'discount' => $validate['discount_percent'][$index] ?? 0,
                        'lumpsum' => $validate['discount_lumpsum'][$index] ?? 0,

                        // Calculated values from frontend (store these for future reference)
                        'amount' => $validate['final_amount'][$index], // Final calculated amount

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

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
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $parties = PurchaseParty::forDatabase($branchConnection)->get();
        $products = Product::forDatabase($branchConnection)->get();
        
        $purchaseReceipt = PurchaseReceipt::forDatabase($branchConnection)
            ->withDynamic(['purchaseParty', 'createUser', 'updateUser'])
            ->where('id', $id)
            ->first();

        $purchaseItems = Purchase::forDatabase($branchConnection)
            ->where('purchase_receipt_id', $id)->get();

        // dd($purchaseItems);
        return view('purchase.edit', compact(['parties', 'products', 'purchaseReceipt', 'purchaseItems']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if user is logged in as branch
            if (session('user_type') !== 'branch' || !session('branch_connection')) {
                return redirect()->back()->with('error', 'Branch session not found. Please login again.');
            }

            $branchConnection = session('branch_connection');

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
                'product.*' => 'required|exists:products,id',
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
            // $purchaseReceipt = \DB::connection($branchConnection)
            $purchaseReceipt = PurchaseReceipt::forDatabase($branchConnection)
                ->where('id', $id)
                ->first();
            // ->table('purchase_receipt')

            if (!$purchaseReceipt) {
                return redirect()->route('purchase.index')
                    ->with('error', 'Purchase receipt not found.');
            }

            \DB::beginTransaction();

            try {
                // Update purchase receipt with calculated totals from frontend
                PurchaseReceipt::forDatabase($branchConnection)
                    ->where('id', $id)
                    ->update([
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

                        'updated_by' => session('branch_user_id'),
                        'updated_at' => now(),
                    ]);

                // Get existing purchase items
                $existingItems = Purchase::forDatabase($branchConnection)
                    ->where('purchase_receipt_id', $id)
                    ->get()
                    ->keyBy('id');

                $processedItemIds = [];

                // Process each product from the form
                foreach ($validate['product'] as $index => $productId) {
                    // Get product details for reference
                    $product = Product::forDatabase($branchConnection)->find($productId);

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Check if this is an existing item or new item
                    $itemId = $validate['purchase_item_ids'][$index] ?? null;

                    $purchaseData = [
                        'bill_date' => $validate['bill_date'],
                        'purchase_party_id' => $validate['party_name'],
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
                        // Update existing item
                        Purchase::forDatabase($branchConnection)
                            ->where('id', $itemId)
                            ->where('purchase_receipt_id', $id)
                            ->update($purchaseData);

                        $processedItemIds[] = $itemId;
                    } else {
                        // Create new item
                        $purchaseData['purchase_receipt_id'] = $id;
                        $purchaseData['created_at'] = now();

                        $newItemId = Purchase::forDatabase($branchConnection)->insertGetId($purchaseData);
                        $processedItemIds[] = $newItemId;
                    }
                }

                // Delete items that were removed (not in the current form submission)
                $itemsToDelete = $existingItems->keys()->diff($processedItemIds);
                if ($itemsToDelete->isNotEmpty()) {
                    Purchase::forDatabase($branchConnection)
                        ->whereIn('id', $itemsToDelete->toArray())
                        ->where('purchase_receipt_id', $id)
                        ->delete();
                }

                \DB::commit();

                return redirect()->route('purchase.index')
                    ->with('success', 'Purchase Receipt #' . $id . ' updated successfully! Total Amount: ₹' . number_format($validate['receipt_total_amount'], 2));

            } catch (Exception $e) {
                \DB::rollback();
                \Log::error('Purchase update failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error updating purchase: ' . $e->getMessage())
                    ->withInput();
            }

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form fields.');
        } catch (Exception $ex) {
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
            if (session('user_type') !== 'branch' || !session('branch_connection')) {
                return redirect()->back()->with('error', 'Branch session not found. Please login again.');
            }

            $branchConnection = session('branch_connection');

            // Check if purchase receipt exists
            $purchaseReceipt = PurchaseReceipt::forDatabase($branchConnection)
                ->where('id', $id)
                ->first();

            if (!$purchaseReceipt) {
                return redirect()->route('purchase.index')
                    ->with('error', 'Purchase receipt not found.');
            }

            \DB::beginTransaction();

            try {
                // Delete purchase items first
                Purchase::forDatabase($branchConnection)
                    ->where('purchase_receipt_id', $id)
                    ->delete();

                // Delete purchase receipt
                PurchaseReceipt::forDatabase($branchConnection)
                    ->where('id', $id)
                    ->delete();

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
}
