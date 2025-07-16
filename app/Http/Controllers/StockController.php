<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\ChalanReceipt;
use App\Models\User;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class StockController extends Controller
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


        $stocks = ChalanReceipt::on($branch->connection_name)
            ->get();

        // Get all branches from master database for mapping
        $branches = Branch::all()->keyBy('id');
        $users = User::all()->keyBy('id');

        // Manually attach branch data to each stock record
        $stocks->each(function ($stock) use ($branches) {
            $stock->fromBranch = $branches->get($stock->from_branch);
            $stock->toBranch = $branches->get($stock->to_branch);
        });
        $stocks->each(function ($stock) use ($users) {
            $stock->user = $users->get($stock->user_id);
        });

        return view('stock.index', compact(['stocks', 'branches', 'users']));
    }

    public function exportRecordPdf($id)
    {

        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];

        // Get all stocks matching the chalan_id
        $chalan = ChalanReceipt::on($branch->connection_name)
        ->with(['stocks.product']) // Optional: eager-load product name
        ->where('id', $id)
        ->first();

        if (!$chalan) {
            abort(404, 'Chalan receipt not found.');
        }

         // Get all branches from master database for mapping
        $branches = Branch::all()->keyBy('id');
        $users = User::all()->keyBy('id');

        // Manually attach branch data to each stock record
            $chalan->fromBranch = $branches->get($chalan->from_branch);
            $chalan->toBranch = $branches->get($chalan->to_branch);
            $chalan->user = $users->get($chalan->user_id);


        $pdf = Pdf::loadView('stock.chalan_pdf', [
            'chalan' => $chalan,
            'stocks' => $chalan->stocks,
            'branchData' => $chalan->fromBranch,
            'toBranchData' => $chalan->toBranch,
            'userData' => $chalan->user,
        ]);



        return $pdf->stream('chalan_' . $id . '.pdf');

    }
            

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $branches = Branch::where('id', '!=', $branch->id)->get();
        $products = Product::on($branch->connection_name)->get();

        return view('stock.create', compact(['branches', 'products']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $branch = $auth['branch'];
            $user = $auth['user'];

            $validate = $request->validate([
                'branch' => 'required|string|max:255',
                'date' => 'date',
                'receipt_total_amount' => 'required',
                // Array validation for multiple purchase items
                'product' => 'required|array|min:1',
                'product.*' => 'required',
                'prate' => 'array',
                'prate.*' => 'nullable|numeric|min:0',
                'box' => 'array',
                'box.*' => 'nullable|numeric|min:0',
                'pcs' => 'array',
                'pcs.*' => 'nullable|numeric|min:0',
                'amount' => 'array',
                'amount.*' => 'numeric|min:0',

                // Calculated fields from frontend
                'total_pcs' => 'array',
                'total_pcs.*' => 'numeric|min:0',
            ]);

            // Check inventory availability before starting transaction
            $inventoryCheckErrors = [];

            foreach ($validate['product'] as $index => $productId) {
                $product = Product::on($branch->connection_name)->find($productId);

                if (!$product) {
                    $inventoryCheckErrors[] = "Product with ID {$productId} not found";
                    continue;
                }

                $requestedQuantity = $validate['total_pcs'][$index] ?? 0;

                if ($requestedQuantity <= 0) {
                    continue; // Skip if no quantity requested
                }

                // Calculate current stock from inventory
                $currentStock = Inventory::on($branch->connection_name)
                    ->where('product_id', $productId)
                    ->selectRaw('SUM(CASE WHEN type = "in" THEN quantity ELSE -quantity END) as available_stock')
                    ->value('available_stock') ?? 0;

                if ($currentStock < $requestedQuantity) {
                    $inventoryCheckErrors[] = "Insufficient stock for product '{$product->product_name}'. Available: {$currentStock}, Requested: {$requestedQuantity}";
                }
            }

            // If there are inventory errors, return with error message
            if (!empty($inventoryCheckErrors)) {
                dd(111);
                return redirect()->back()
                    ->with('error', 'Stock transfer failed: ' . implode(', ', $inventoryCheckErrors))
                    ->withInput();
            }

            \DB::beginTransaction();

            try {
                $count = ChalanReceipt::on($branch->connection_name)->count() + 1;

                $chalanData = [
                    'chalan_no' =>"TR-" .$count,
                    'from_branch' => $branch->id,
                    'to_branch' => $validate['branch'],
                    'user_id' => $user->id,
                    'date' => $validate['date'] ?? now()->format('Y-m-d'),
                    'total_amount' => $validate['receipt_total_amount']
                ];
                $chalanReceipt = ChalanReceipt::on($branch->connection_name)->create($chalanData);

                foreach ($validate['product'] as $index => $productId) {
                    $product = Product::on($branch->connection_name)->find($productId);

                    if (!$product) {
                        continue; // Skip if product not found (already handled in inventory check)
                    }

                    $requestedQuantity = $validate['total_pcs'][$index] ?? 0;

                    if ($requestedQuantity <= 0) {
                        continue; // Skip if no quantity requested
                    }

                    // Create stock transfer record
                    $transferData = [
                        'chalan_id' => $chalanReceipt->id,
                        'date' => $validate['date'] ?? now()->format('Y-m-d'),
                        'product_id' => $validate['product'][$index],
                        'prate' => $validate['prate'][$index] ?? 0,
                        'box' => $validate['box'][$index] ?? 0,
                        'pcs' => $validate['pcs'][$index] ?? 0,
                        'amount' => $validate['amount'][$index] ?? 0
                    ];

                    Stock::on($branch->connection_name)->create($transferData);

                    // Create outgoing inventory record for current branch
                    $outgoingInventoryData = [
                        'product_id' => $productId,
                        'purchase_id' => null,
                        'type' => 'out',
                        'quantity' => '-'.$requestedQuantity,
                        'mrp' => $validate['prate'][$index] ?? 0,
                        'sale_price' => $validate['prate'][$index] ?? 0,
                        'purchase_price' => null,
                        'unit' => $product->unit_types ?? 'PCS',
                        'reason' => 'Stock Transferred To ' . Branch::find($validate['branch'])->name ?? 'Branch',
                        'gst' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    Inventory::on($branch->connection_name)->create($outgoingInventoryData);
                    // Get selected branch and configure connection
                    $selectedBranch = Branch::where('id', $validate['branch'])->firstOrFail();
                    configureBranchConnection($selectedBranch);
                    // Find or create product in destination branch
                    $branchProduct = Product::on($selectedBranch->connection_name)
                        ->where('product_name', $product->product_name)
                        ->orWhere('barcode', $product->barcode)
                        ->first();

                    if (!$branchProduct) {
                        $branchProduct = Product::on($selectedBranch->connection_name)->create([
                            'product_name' => $product->product_name,
                            'barcode' => $product->barcode,
                            'image' => $product->image,
                            'search_option' => $product->search_option,
                            'unit_types' => $product->unit_types,
                            'decimal_btn' => $product->decimal_btn,
                            'cess' => $product->cess,
                            'mrp' => $product->mrp,
                            'purchase_rate' => $product->purchase_rate,
                            'sale_rate_a' => $product->sale_rate_a,
                            'sale_rate_b' => $product->sale_rate_b,
                            'sale_rate_c' => $product->sale_rate_c,
                            'sale_online' => $product->sale_online,
                            'gst_active' => $product->gst_active,
                            'converse_carton' => $product->converse_carton,
                            'carton_barcode' => $product->carton_barcode,
                            'converse_box' => $product->converse_box,
                            'box_barcode' => $product->box_barcode,
                            'converse_pcs' => $product->converse_pcs,
                            'negative_billing' => $product->negative_billing,
                            'min_qty' => $product->min_qty,
                            'reorder_qty' => $product->reorder_qty,
                            'discount' => $product->discount,
                            'max_discount' => $product->max_discount,
                            'discount_scheme' => $product->discount_scheme,
                            'bonus_use' => $product->bonus_use,
                            'price_1' => $product->price_1,
                            'price_2' => $product->price_2,
                            'price_3' => $product->price_3,
                            'price_4' => $product->price_4,
                            'price_5' => $product->price_5,
                            'Kg_1' => $product->Kg_1,
                            'Kg_2' => $product->Kg_2,
                            'Kg_3' => $product->Kg_3,
                            'Kg_4' => $product->Kg_4,
                            'Kg_5' => $product->Kg_5,
                        ]);
                    }

                    // Create incoming inventory record for destination branch
                    $incomingInventoryData = [
                        'product_id' => $branchProduct->id,
                        'purchase_id' => null,
                        'type' => 'in',
                        'quantity' => $requestedQuantity,
                        'mrp' => $validate['prate'][$index] ?? 0,
                        'sale_price' => $validate['prate'][$index] ?? 0,
                        'purchase_price' => null,
                        'unit' => $branchProduct->unit_types ?? 'PCS',
                        'reason' => 'Stock Transferred From ' . $branch->name,
                        'gst' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    Inventory::on($selectedBranch->connection_name)->create($incomingInventoryData);
                }

                \DB::commit();

                return redirect()->route('stock.index')
                    ->with('success', 'Stock transferred successfully');

            } catch (Exception $e) {
                dd($e->getMessage());
                \DB::rollback();
                \Log::error('Stock Transfer failed: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Error transferring stock: ' . $e->getMessage())
                    ->withInput();
            }

        } catch (Exception $ex) {
            dd($ex->getMessage());
            \Log::error('Stock Transfer error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('error', 'Error processing stock transfer: ' . $ex->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        //
    }
}
