<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ManyToOne;
use App\Models\Product;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ManyToOneController extends Controller
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
            $manyToOne = ManyToOne::with(['ledger', 'product'])->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $manyToOne = ManyToOne::on($branch->connection_name)->with(['ledger', 'product'])->orderBy('created_at', 'desc')->paginate(10);
        }
        if ($request->ajax()) {
            return view('manyToOne.rows', compact('manyToOne'));
        }

        return view('manyToOne.index', compact('manyToOne'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manyToOne.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // dd($request->all());
            

            $validate = Validator::make($request->all(), [
                'hidden_ledger_id' => 'required|integer',
                'date' => 'date',
                'product_id' => 'required|integer',
                'quantity' => 'required',

                'raw_product_id' => 'required|array',
                'raw_product_id.*' => 'integer',
                'raw_product_search' => 'array',
                'raw_product_search.*' => 'string',
                'raw_quantity' => 'required|array',
                'raw_quantity.*' => 'integer|min:0',
            ]);
            if ($validate->fails()) {
                return redirect()->back()
                    ->withErrors($validate)
                    ->withInput();
            }
            $validate = $validate->validated();

            \DB::beginTransaction();

            try {
                $rawProduct = [];
                foreach ($request->raw_product_id as $index => $rawProductId) {
                    $rawProduct[] = [
                        'product_id' => $rawProductId,
                        'product_name' => $validate['raw_product_search'][$index] ?? 0,
                        'qty' => $validate['raw_quantity'][$index] ?? 0,
                    ];
                }

                $data = [
                    'ledger_id' => $validate['hidden_ledger_id'],
                    'date' => $validate['date'],
                    'conversion_item' => $validate['product_id'],
                    'qty' => $validate['quantity'],
                    'raw_item' => json_encode($rawProduct),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $entry_no = ManyToOne::count() + 1;
                    $data['entry_no'] = 'MTO-' . $entry_no;
                    $manyToOne = ManyToOne::create($data);
                    $productDetail = Product::with('hsnCode')->where('id', $validate['product_id'])->first();
                } else {
                    $entry_no = ManyToOne::on($branch->connection_name)->count() + 1;
                    $data['entry_no'] = 'MTO-' . $entry_no;
                    $manyToOne = ManyToOne::on($branch->connection_name)->create($data);

                    $productDetail = Product::on($branch->connection_name)->with('hsnCode')->where('id', $validate['product_id'])->first();
                }

                // Deduct Raw product from inventory
                foreach ($rawProduct as $product) {
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $rawProductDetails = Product::with('hsnCode')->where('id', $product['product_id'])->first();
                        // Check inventory availability - GET ALL INVENTORY ENTRIES
                        $inventoryEntries = Inventory::where('product_id', $product['product_id'])
                            ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                            ->get();

                    } else {
                        $rawProductDetails = Product::on($branch->connection_name)->with('hsnCode')->where('id', $product['product_id'])->first();
                        // Check inventory availability - GET ALL INVENTORY ENTRIES
                        $inventoryEntries = Inventory::on($branch->connection_name)
                            ->where('product_id', $product['product_id'])
                            ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                            ->get();
                    }

                    $totalAvailableStock = $inventoryEntries->sum('quantity');

                    $requiredQuantity = $product['qty'] * $validate['quantity'];

                    if ($totalAvailableStock < $requiredQuantity) {
                        $available = max(0, $totalAvailableStock);
                        $availableFormatted = rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.');
                        $manyToOne->delete();
                        \DB::rollBack();
             
                        return redirect()->back()
                        ->with('error', "Insufficient stock. Required: {$requiredQuantity}, Available: {$availableFormatted}")->withInput();
                    }

                    $deductRawQuantity = [
                        'product_id' => $product['product_id'],
                        'many_to_one_id' => $manyToOne->id,
                        'type' => 'out',
                        'total_qty' => -$requiredQuantity,
                        'quantity' => -$requiredQuantity,
                        'unit' => $rawProductDetails->unit_types ?? 'pcs',
                        'reason' => "Used in many to one conversion: {$data['entry_no']}",
                        'gst' => 'off',
                        'gst_p' => 0,
                        'mrp' => $rawProductDetails->mrp ?? 0,
                        'sale_price' => $rawProductDetails->sale_rate_a ?? 0,
                        'purchase_price' => $rawProductDetails->purchase_rate ?? 0,
                    ];

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::create($deductRawQuantity);
                    } else {
                        Inventory::on($branch->connection_name)->create($deductRawQuantity);
                    }
                }
                // Add new produced product
                $addProducedQuantity = [
                    'product_id' => $validate['product_id'],
                    'many_to_one_id' => $manyToOne->id,
                    'type' => 'in',
                    'total_qty' => $validate['quantity'],
                    'quantity' => $validate['quantity'],
                    'unit' => $productDetail->unit_types ?? 'pcs',
                    'reason' => "Produced from many to one conversion: {$data['entry_no']}",
                    'gst' => 'off',
                    'gst_p' => 0,
                    'mrp' => $productDetail->mrp ?? 0,
                    'sale_price' => $productDetail->sale_rate_a ?? 0,
                    'purchase_price' => $productDetail->purchase_rate ?? 0,
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::create($addProducedQuantity);
                } else {
                    Inventory::on($branch->connection_name)->create($addProducedQuantity);
                }

                \DB::commit();

                return redirect()->route('many-to-one.index')
                    ->with('success', 'One To Many conversion created successfully!');
            } catch (Exception $ex) {
                \DB::rollback();
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => $ex->getMessage()]);
            }
        } catch (Exception $ex) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred: ' . $ex->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $manyToOne = ManyToOne::with(['ledger', 'product'])->where('id', $id)->first();
        } else {
            $manyToOne = ManyToOne::on($branch->connection_name)->with(['ledger', 'product'])->where('id', $id)->first();
        }

        $manyToOne->raw_item = json_decode($manyToOne->raw_item);

        return view('manyToOne.show', compact('manyToOne'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ManyToOne $manyToOne)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ManyToOne $manyToOne)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ManyToOne $manyToOne)
    {
        //
    }
}
