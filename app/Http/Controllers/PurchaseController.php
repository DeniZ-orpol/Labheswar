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

        $parties = PurchaseParty::on($branchConnection)->get();
        $purchaseReceipt = PurchaseReceipt::on($branchConnection)->with(['purchaseParty', 'createUser', 'updateUser'])->get();
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

        $parties = PurchaseParty::on($branchConnection)->get();
        $products = Product::on($branchConnection)->get();
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
                $purchaseReceiptId = \DB::connection($branchConnection)->table('purchase_receipt')->insertGetId([
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
                    $product = \DB::connection($branchConnection)->table('products')->find($productId);

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Create purchase record with calculated values from frontend
                    \DB::connection($branchConnection)->table('purchase')->insert([
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
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
