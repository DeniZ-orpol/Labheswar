<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\OneToMany;
use App\Models\Product;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class OneToManyController extends Controller
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
            $oneToMany = OneToMany::with(['ledger', 'rawItem'])->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $oneToMany = OneToMany::on($branch->connection_name)->with(['ledger', 'rawItem'])->orderBy('created_at', 'desc')->paginate(10);
        }
         if ($request->ajax()) {
            return view('oneToMany.rows', compact('oneToMany'));
        }

        return view('oneToMany.index', compact('oneToMany'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('oneToMany.create');
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

            $validate = $request->validate([
                'hidden_ledger_id' => 'required|integer',
                'date' => 'date',
                'raw_product_id' => 'required|integer',
                'raw_quantity' => 'required',

                'add_product_id' => 'required|array',
                'add_product_id.*' => 'integer',
                'add_product_search' => 'array',
                'add_product_search.*' => 'string',
                'add_quantity' => 'required|array',
                'add_quantity.*' => 'integer|min:0',
            ]);
            // dd($request->all());

            \DB::beginTransaction();

            try {
                $produceProduct = [];
                foreach ($request->add_product_id as $index => $addProductId) {
                    $produceProduct[] = [
                        'product_id' => $addProductId,
                        'product_name' => $validate['add_product_search'][$index] ?? 0,
                        'qty' => $validate['add_quantity'][$index] ?? 0,
                    ];
                }

                $data = [
                    'ledger_id' => $validate['hidden_ledger_id'],
                    'date' => $validate['date'],
                    'raw_item' => $validate['raw_product_id'],
                    'qty' => $validate['raw_quantity'],
                    'item_to_create' => json_encode($produceProduct),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $entry_no = OneToMany::count() + 1;
                    $data['entry_no'] = 'OTM-' . $entry_no;
                    $oneToMany = OneToMany::create($data);
                    $rawProduct = Product::with('hsnCode')->where('id', $validate['raw_product_id'])->first();
                    // Check inventory availability - GET ALL INVENTORY ENTRIES
                    $inventoryEntries = Inventory::where('product_id', $validate['raw_product_id'])
                        ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                        ->get();
                } else {
                    $entry_no = OneToMany::on($branch->connection_name)->count() + 1;
                    $data['entry_no'] = 'OTM-' . $entry_no;
                    $oneToMany = OneToMany::on($branch->connection_name)->create($data);

                    $rawProduct = Product::on($branch->connection_name)->with('hsnCode')->where('id', $validate['raw_product_id'])->first();
                    // Check inventory availability - GET ALL INVENTORY ENTRIES
                    $inventoryEntries = Inventory::on($branch->connection_name)
                        ->where('product_id', $validate['raw_product_id'])
                        ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                        ->get();
                }

                if($inventoryEntries->isEmpty()){
                    $inventory = [
                        'product_id' => $validate['raw_product_id'],
                        'quantity' => 0,
                        'total_qty' => 0,
                        'mrp' => $product->mrp ?? 0,
                        'sale_price' => $product->sale_rate_a ?? 0,
                        'purchase_price' => $product->purchase_price ?? 0,
                        'gst' => 'off',
                        'gst_p' => 0,
                        'reason' => null,
                        'type' => 'in',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::insert($inventory);
                    } else {
                        Inventory::on($branch->connection_name)->insert($inventory);
                    }
                }
                // Calculate total available stock
                $totalAvailableStock = $inventoryEntries->sum('quantity');

                $requiredQuantity = $validate['raw_quantity'];

                if ($totalAvailableStock < $requiredQuantity) {
                    $available = max(0, $totalAvailableStock);
                        $availableFormatted = rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.');
                        $oneToMany->delete();
                        \DB::rollBack();
                        return redirect()->back()
                        ->with('error', "Insufficient stock. Required: {$requiredQuantity}, Available: {$availableFormatted}")->withInput();
                }

                $deductedRawQuantity = [
                    'product_id' => $validate['raw_product_id'],
                    'one_to_many_id' => $oneToMany->id,
                    'type' => 'out',
                    'total_qty' => -$validate['raw_quantity'],
                    'quantity' => -$validate['raw_quantity'],
                    'unit' => $rawProduct->unit_types ?? 'pcs',
                    'reason' => "Used in one to many conversion: {$data['entry_no']}",
                    'gst' => 'off',
                    'gst_p' => 0,
                    'mrp' => $rawProduct->mrp ?? 0,
                    'sale_price' => $rawProduct->sale_rate_a ?? 0,
                    'purchase_price' => $rawProduct->purchase_rate ?? 0,
                ];

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    Inventory::create($deductedRawQuantity);
                } else {
                    Inventory::on($branch->connection_name)->create($deductedRawQuantity);
                }
                // dd($produceProduct);
                foreach ($produceProduct as $product) {
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $producedProductDetails = Product::with('hsnCode')->where('id', $product['product_id'])->first();
                    } else {
                        $producedProductDetails = Product::on($branch->connection_name)->with('hsnCode')->where('id', $product['product_id'])->first();
                    }
                    if ($product['qty'] > 0) {
                        $createInventory = [
                            'product_id' => $product['product_id'],
                            'one_to_many_id' => $oneToMany->id,
                            'type' => 'in',
                            'total_qty' => $product['qty'],
                            'quantity' => $product['qty'],
                            'unit' => $producedProductDetails->unit_types ?? 'pcs',
                            'reason' => "Produced from conversion: {$data['entry_no']}",
                            'gst' => 'off',
                            'gst_p' => 0,
                            'mrp' => $producedProductDetails->mrp ?? 0,
                            'sale_price' => $producedProductDetails->sale_rate_a ?? 0,
                            'purchase_price' => $producedProductDetails->purchase_rate ?? 0,
                        ];
                    }
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::create($createInventory);
                    } else {
                        Inventory::on($branch->connection_name)->create($createInventory);
                    }
                }

                \DB::commit();

                return redirect()->route('one-to-many.index')
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
            $oneToMany = OneToMany::with([
                'ledger',
                'rawItem' => function ($query) {
                    $query->withSum('inventories as available_qty', 'quantity');
                }
            ])
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $oneToMany = OneToMany::on($branch->connection_name)->with([
                'ledger',
                'rawItem' => function ($query) {
                    $query->withSum('inventories as available_qty', 'quantity');
                }
            ])
                ->where('id', $id)
                ->firstOrFail();
        }

        // Decode JSON string to array for item_to_create
        $oneToMany->item_to_create = json_decode($oneToMany->item_to_create);

        return view('oneToMany.show', compact('oneToMany'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        // If Super Admin, use `branch` from route or query
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $oneToMany = OneToMany::with([
                'ledger',
                'rawItem' => function ($query) {
                    $query->withSum('inventories as available_qty', 'quantity');
                }
            ])
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $oneToMany = OneToMany::on($branch->connection_name)->with([
                'ledger',
                'rawItem' => function ($query) {
                    $query->withSum('inventories as available_qty', 'quantity');
                }
            ])
                ->where('id', $id)
                ->firstOrFail();
        }

        // Decode JSON string to array for item_to_create
        $oneToMany->item_to_create = json_decode($oneToMany->item_to_create);

        return view('oneToMany.edit', compact('oneToMany'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            $validate = $request->validate([
                'date' => 'date',
                'raw_product_id' => 'required|integer',
                'raw_quantity' => 'required',

                'add_product_id' => 'required|array',
                'add_product_id.*' => 'integer',
                'add_product_search' => 'array',
                'add_product_search.*' => 'string',
                'add_quantity' => 'required|array',
                'add_quantity.*' => 'integer|min:0',
            ]);

            \DB::beginTransaction();

            try {

                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $oneToMany = OneToMany::with([
                        'ledger',
                        'rawItem' => function ($query) {
                            $query->withSum('inventories as available_qty', 'quantity');
                        }
                    ])
                        ->where('id', $id)
                        ->firstOrFail();
                } else {
                    $oneToMany = OneToMany::on($branch->connection_name)->with([
                        'ledger',
                        'rawItem' => function ($query) {
                            $query->withSum('inventories as available_qty', 'quantity');
                        }
                    ])
                        ->where('id', $id)
                        ->firstOrFail();
                }

                $previousProductData = json_decode($oneToMany->item_to_create, true) ?? [];
                $previousProductIds = array_column($previousProductData, 'product_id');

                $newProductData = [];
                $newProductIds = [];
                foreach ($request->add_product_id as $index => $addProductId) {
                    $newProductData[] = [
                        'product_id' => $addProductId,
                        'product_name' => $validate['add_product_search'][$index] ?? '',
                        'qty' => $validate['add_quantity'][$index] ?? 0,
                    ];
                    $newProductIds[] = $addProductId;
                }

                // Determine products to delete, add, and update
                $productsToDelete = array_diff($previousProductIds, $newProductIds);
                $productsToAdd = array_diff($newProductIds, $previousProductIds);
                $productsToUpdate = array_intersect($previousProductIds, $newProductIds);

                // Handle raw material quantity change
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    // Update raw material inventory entries
                    $rawInventoryEntry = Inventory::where('one_to_many_id', $id)
                        ->where('product_id', $oneToMany->raw_item)
                        ->where('type', 'out')
                        ->first();

                    $rawInventoryEntry->update([
                        'total_qty' => -$validate['raw_quantity'],
                        'quantity' => -$validate['raw_quantity']
                    ]);
                    $rawProduct = Product::with('hsnCode')->where('id', $validate['raw_product_id'])->first();
                    $totalAvailableStock = Inventory::where('product_id', $validate['raw_product_id'])
                        ->orderBy('created_at', 'asc')
                        ->sum('quantity');
                } else {
                    // Update raw material inventory entries
                    $rawInventoryEntry = Inventory::on($branch->connection_name)->where('one_to_many_id', $id)
                        ->where('product_id', $oneToMany->raw_item)
                        ->where('type', 'out')
                        ->first();
                    $rawInventoryEntry->update([
                        'total_qty' => -$validate['raw_quantity'],
                        'quantity' => -$validate['raw_quantity']
                    ]);
                    $rawProduct = Product::on($branch->connection_name)
                        ->with('hsnCode')
                        ->where('id', $validate['raw_product_id'])
                        ->first();
                    $totalAvailableStock = Inventory::on($branch->connection_name)
                        ->where('product_id', $validate['raw_product_id'])
                        ->orderBy('created_at', 'asc')
                        ->sum('quantity');
                }

                $requiredQuantity = $validate['raw_quantity'];

                if ($totalAvailableStock < $requiredQuantity) {
                    throw new \Exception("Insufficient stock. Required: {$requiredQuantity}, Available: {$totalAvailableStock}");
                }

                // Handle deleted products - delete permanently from table
                foreach ($productsToDelete as $productId) {
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Inventory::where('one_to_many_id', $id)
                            ->where('product_id', $productId)
                            ->where('type', 'in')
                            ->delete();
                    } else {
                        Inventory::on($branch->connection_name)
                            ->where('purchase_id', $id)
                            ->where('product_id', $productId)
                            ->where('type', 'in')
                            ->delete();
                    }
                }

                // Handle updated products - set old entries to 0 and create new ones
                foreach ($productsToUpdate as $productId) {
                    $newProductInfo = collect($newProductData)->firstWhere('product_id', $productId);
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $productInventories = Inventory::where('one_to_many_id', $id)
                            ->where('product_id', $productId)
                            ->where('type', 'in')
                            ->get();
                    } else {
                        $productInventories = Inventory::on($branch->connection_name)
                            ->where('one_to_many_id', $id)
                            ->where('product_id', $productId)
                            ->where('type', 'in')
                            ->get();
                    }
                    // Update existing entries
                    foreach ($productInventories as $inventory) {
                        $inventory->update([
                            'total_qty' => $newProductInfo['qty'],
                            'quantity' => $newProductInfo['qty'],
                            'updated_at' => now()
                        ]);
                    }
                }

                // Handle new products - create inventory entries
                foreach ($productsToAdd as $productId) {
                    $newProductInfo = collect($newProductData)->firstWhere('product_id', $productId);

                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        $producedProductDetails = Product::with('hsnCode')->where('id', $productId)->first();
                    } else {
                        $producedProductDetails = Product::on($branch->connection_name)
                            ->with('hsnCode')
                            ->where('id', $productId)
                            ->first();
                    }
                    if ($newProductInfo['qty'] > 0) {
                        $newInventory = [
                            'product_id' => $productId,
                            'one_to_many_id' => $id,
                            'type' => 'in',
                            'total_qty' => $newProductInfo['qty'],
                            'quantity' => $newProductInfo['qty'],
                            'unit' => $producedProductDetails->unit_types ?? 'pcs',
                            'reason' => "Produced from conversion: {$oneToMany->entry_no}",
                            'gst' => 'off',
                            'gst_p' => 0,
                            'mrp' => $producedProductDetails->mrp ?? 0,
                            'sale_price' => $producedProductDetails->sale_rate_a ?? 0,
                            'purchase_price' => $producedProductDetails->purchase_rate ?? 0,
                        ];

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Inventory::create($newInventory);
                        } else {
                            Inventory::on($branch->connection_name)->create($newInventory);
                        }
                    }
                }

                $data = [
                    'date' => $validate['date'],
                    'qty' => $validate['raw_quantity'],
                    'item_to_create' => json_encode($newProductData),
                    'updated_at' => now()
                ];

                $oneToMany->update($data);

                \DB::commit();

                return redirect()->route('one-to-many.index')
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
     * Remove the specified resource from storage.
     */
    public function destroy(OneToMany $oneToMany)
    {
        //
    }
}
