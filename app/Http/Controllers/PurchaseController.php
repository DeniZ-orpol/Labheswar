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
        $purchaseReceipt = PurchaseReceipt::on($branchConnection)->with(['purchaseParty','createUser','updateUser'])->get();
        // $purchase = Purchase::on($branchConnection)->with('purchaseReceipt')->paginate();

        return view('purchase.index', compact(['parties','purchaseReceipt']));
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

            // Validate the request - using correct field names from your form
            $validate = $request->validate([
                'bill_date' => 'required|date',
                'party_name' => 'required|string|max:255',
                'bill_no' => 'required|string|max:255',
                'delivery_date' => 'nullable|date',
                'gst' => 'required|string|in:on,off', // This matches your form field name

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
            ]);

            \DB::beginTransaction();

            try {
                // Convert GST setting to numeric value
                $gstRate = ($validate['gst'] === 'on') ? 18 : 0; // Using 'gst' field name

                // Initialize totals
                $subtotal = 0;
                $totalDiscount = 0;
                $totalGstAmount = 0;
                $finalTotalAmount = 0;

                // First, create the purchase receipt
                $purchaseReceiptId = \DB::connection($branchConnection)->table('purchase_receipt')->insertGetId([
                    'bill_date' => $validate['bill_date'],
                    'purchase_party_id' => $validate['party_name'],
                    'bill_no' => $validate['bill_no'],
                    'delivery_date' => $validate['delivery_date'],
                    'gst_status' => $validate['gst'], // Store 'on' or 'off'
                    'subtotal' => 0, // Will update after calculating
                    'total_discount' => 0, // Will update after calculating
                    'total_gst_amount' => 0, // Will update after calculating
                    'total_amount' => 0, // Will update after calculating
                    'receipt_status' => 'completed',
                    'created_by' => session('branch_user_id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Loop through each product row and create purchase records
                foreach ($validate['product'] as $index => $productId) {

                    // Get product details using branch connection
                    $product = \DB::connection($branchConnection)->table('products')->find($productId);

                    if (!$product) {
                        continue; // Skip if product not found
                    }

                    // Get values for this specific row using index
                    $box = $validate['box'][$index] ?? 0;
                    $pcs = $validate['pcs'][$index] ?? 0;
                    $free = $validate['free'][$index] ?? 0;
                    $purchaseRate = $validate['purchase_rate'][$index] ?? 0;
                    $discountPercent = $validate['discount_percent'][$index] ?? 0;
                    $discountLumpsum = $validate['discount_lumpsum'][$index] ?? 0;

                    // Calculate amounts step by step
                    // Base amount = purchase rate * total quantity (box + pcs)
                    $baseAmount = $purchaseRate * ($box + $pcs);

                    // Calculate discount amounts
                    $percentDiscountAmount = 0;
                    if ($discountPercent > 0) {
                        $percentDiscountAmount = $baseAmount * ($discountPercent / 100);
                    }

                    // Total discount = percentage discount + lumpsum discount
                    $itemTotalDiscount = $percentDiscountAmount + $discountLumpsum;

                    // Amount after discount
                    $amountAfterDiscount = $baseAmount - $itemTotalDiscount;

                    // GST amount calculation
                    $itemGstAmount = 0;
                    if ($gstRate > 0) {
                        $itemGstAmount = $amountAfterDiscount * ($gstRate / 100);
                    }

                    // Final amount = amount after discount + GST
                    $finalItemAmount = $amountAfterDiscount + $itemGstAmount;

                    // Create purchase record with purchase_receipt_id reference
                    \DB::connection($branchConnection)->table('purchase')->insert([
                        'purchase_receipt_id' => $purchaseReceiptId, // Link to purchase receipt
                        'bill_date' => $validate['bill_date'],
                        'purchase_party_id' => $validate['party_name'],
                        'bill_no' => $validate['bill_no'],
                        'delivery_date' => $validate['delivery_date'],
                        'gst' => $validate['gst'], // Store 'on' or 'off' as string
                        'product_id' => $productId,
                        'product' => $product->product_name,
                        'mrp' => $product->mrp ?? 0,
                        'box' => $box,
                        'pcs' => $pcs,
                        'free' => $free,
                        'p_rate' => $purchaseRate,
                        'discount' => $discountPercent,
                        'lumpsum' => $discountLumpsum,
                        'amount' => $finalItemAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update running totals for receipt
                    $subtotal += $baseAmount;
                    $totalDiscount += $itemTotalDiscount;
                    $totalGstAmount += $itemGstAmount;
                    $finalTotalAmount += $finalItemAmount;

                    // Update product stock (optional - if you want to update inventory)
                    $totalPcs = ($box * ($product->converse_box ?? 1)) + $pcs + $free;

                    // Uncomment next lines if you want to update stock:
                    // \DB::connection($branchConnection)->table('products')
                    //     ->where('id', $productId)
                    //     ->increment('stock_quantity', $totalPcs);
                }

                // Update the purchase receipt with calculated totals
                \DB::connection($branchConnection)->table('purchase_receipt')
                    ->where('id', $purchaseReceiptId)
                    ->update([
                        'subtotal' => $subtotal,
                        'total_discount' => $totalDiscount,
                        'total_gst_amount' => $totalGstAmount,
                        'total_amount' => $finalTotalAmount,
                        'updated_at' => now(),
                    ]);

                \DB::commit();

                return redirect()->route('purchase.index')
                    ->with('success', 'Purchase Receipt #' . $purchaseReceiptId . ' created successfully in ' . session('branch_name') . '! Total Amount: ₹' . number_format($finalTotalAmount, 2));

            } catch (Exception $e) {
                \DB::rollback();
                \Log::error('Purchase creation failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error creating purchase: ' . $e->getMessage())
                    ->withInput();
            }

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form fields.');
        } catch (Exception $ex) {
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
