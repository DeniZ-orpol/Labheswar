<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Formula;
use App\Traits\BranchAuthTrait;
use Exception;

class FormulaController extends Controller
{
    //
    use BranchAuthTrait;

    /**
     * asd
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $formulas = Formula::with('product')->orderBy('created_at')->paginate(10);
        } else {
            $formulas = Formula::on($branch->connection_name)->with('product')->orderBy('created_at')->paginate(10);
        }
        if ($request->ajax()) {
            return view('formula.rows', compact('formulas'))->render();
        }

        return view('formula.index', compact('formulas'));
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

        return view('formula.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $validated = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required',
            'auto_production' => 'nullable',

            // Ingredients fields validation
            'ingredient_product_id' => 'array',
            'ingredient_product_id.*' => 'required',
            'ingredient_quantity' => 'array',
            'ingredient_quantity.*' => 'required',
            // 'ingredient_unit' => 'array',
            // 'ingredient_unit.*' => 'required',
        ]);

        try {
            $ingredients = [];

            foreach ($validated['ingredient_product_id'] as $index => $productId) {
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $product = Product::find($productId);
                } else {
                    $product = Product::on($branch->connection_name)->find($productId);
                }

                if (!$product) {
                    continue; // Skip if product not found
                }
                $ingredients[] = [
                    'product_id' => $productId ?? '',
                    'quantity' => $validated['ingredient_quantity'][$index] ?? 0,
                    // 'unit' => $validate['ingredient_unit'][$index] ?? ''
                ];
            }
            $data = [
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'auto_production' => isset($validated['auto_production']) && $validated['auto_production'] == "on" ? 1 : 0,
                'ingredients' => !empty($ingredients) ? json_encode($ingredients) : null,
            ];
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Formula::create($data);
            } else {
                Formula::on($branch->connection_name)->create($data);
            }

            return redirect()->route('formula.index')
                ->with('success', 'Formula created successfully!');
        } catch (Exception $ex) {
            return redirect()->back()
                ->with('error', "Can't create Formula" . $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $formula = Formula::with('product')->findOrFail($id);
            $connection = config('database.default');
        } else {
            $formula = Formula::on($branch->connection_name)->with('product')->findOrFail($id);
            $connection = $branch->connection_name;
        }

        $ingredientsRaw = is_string($formula->ingredients)
            ? json_decode($formula->ingredients, true)
            : $formula->ingredients;

        $ingredientsWithProduct = collect($ingredientsRaw)->map(function ($ingredient) use ($connection) {
            $product = Product::on($connection)->find($ingredient['product_id']);
            return [
                'product' => $product,
                'qty' => $ingredient['quantity'],
            ];
        });

        return view('formula.show', [
            'formula' => $formula,
            'ingredientsWithProduct' => $ingredientsWithProduct,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $formula = Formula::with('product')->findOrFail($id);
            $connection = config('database.default');
        } else {
            $formula = Formula::on($branch->connection_name)->with('product')->findOrFail($id);
            $connection = $branch->connection_name;
        }

        $ingredientsRaw = is_string($formula->ingredients)
            ? json_decode($formula->ingredients, true)
            : $formula->ingredients;

        $ingredientsWithProduct = collect($ingredientsRaw)->map(function ($ingredient) use ($connection) {
            $product = Product::on($connection)->find($ingredient['product_id']);
            return [
                'product' => $product,
                'qty' => $ingredient['quantity'] ?? 0,
            ];
        });

        return view('formula.edit', compact('formula', 'ingredientsWithProduct'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Validate inputs
        $request->validate([
            'product_id' => 'required',
            // 'qty' => 'required|integer|min:0',
            'ingredient_product_id' => 'required|array',
            'ingredient_product_id.*' => 'required',
            'ingredient_quantity' => 'required|array',
            'ingredient_quantity.*' => 'required',
            'quantity' => 'required',
            'auto_production' => 'nullable',
        ]);

        try {
            // Prepare ingredients array
            $ingredients = [];
            foreach ($request->ingredient_product_id as $index => $ingredientId) {
                $ingredients[] = [
                    'product_id' => $ingredientId,
                    'quantity' => $request->ingredient_quantity[$index] ?? 0,
                ];
            }

            $data = [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'auto_production' => isset($request->auto_production) && $request->auto_production == "on" ? 1 : 0,
                'ingredients' => json_encode($ingredients)
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $formula = Formula::findOrFail($id);
            } else {
                $formula = Formula::on($branch->connection_name)->findOrFail($id);
            }
            $formula->update($data);

            return redirect()->route('formula.index')->with('success', 'formula updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Formula update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update formula data. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Delete the formula from the current branch connection
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Formula::where('id', $id)->delete();
        } else {
            Formula::on($branch->connection_name)->where('id', $id)->delete();
        }

        return redirect()->route('formula.index')->with('success', 'Formula deleted successfully.');
    }

    public function searchFormula(Request $request)
    {
        // Authenticate and get branch configuration
        $authResult = $this->authenticateAndConfigureBranch();

        if (is_array($authResult) && isset($authResult['success']) && !$authResult['success']) {
            return response()->json(['products' => []]);
        }

        $user = $authResult['user'];
        $branch = $authResult['branch'];
        $role = $authResult['role'];
        $search = $request->get('search', '');

        try {
            // SUPER ADMIN logic
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $query = Formula::with('product')
                    ->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'LIKE', '%' . $search . '%');
                    });

                // Try exact match first
                $exactMatch = Formula::with('product')
                    ->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', trim($search));
                    })
                    ->first();

                if ($exactMatch) {
                    return response()->json([
                        'success' => true,
                        'products' => [$exactMatch],
                        'exact_match' => true,
                        'auto_select' => true
                    ]);
                }

                $products = $query->limit(10)->get();
            } else {
                $branchConnection = $branch->connection_name;

                $query = Formula::on($branchConnection)->with('product')
                    ->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'LIKE', '%' . $search . '%');
                    });

                $exactMatch = Formula::on($branchConnection)->with('product')
                    ->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', trim($search));
                    })
                    ->first();

                if ($exactMatch) {
                    return response()->json([
                        'success' => true,
                        'products' => [$exactMatch],
                        'exact_match' => true,
                        'auto_select' => true
                    ]);
                }

                $products = $query->limit(10)->get();
            }

            // Word-acronym search logic (optional fuzzy matching)
            $allProducts = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Formula::with('product')->get()
                : Formula::on($branch->connection_name)->with('product')->get();

            $acronymMatches = $allProducts->filter(function ($formula) use ($search) {
                if (!$formula->product || !$formula->product->product_name)
                    return false;

                $words = preg_split('/\s+/', strtolower($formula->product->product_name));
                $initials = implode('', array_map(fn($word) => $word[0] ?? '', $words));

                return stripos($initials, strtolower($search)) !== false;
            })->take(10);

            // Merge and remove duplicates
            $finalResults = $acronymMatches->merge($products)->unique('id')->take(10);

            $finalResults->transform(function ($formula) use ($branch, $role) {
                $branchConnection = strtoupper($role->role_name) === 'SUPER ADMIN' ? null : $branch->connection_name;
                $formula->one_piece_cost = $this->calculateOnePieceCost($formula, $branchConnection);
                return $formula;
            });

            return response()->json([
                'success' => true,
                'products' => $finalResults->values(),
                'exact_match' => false,
                'auto_select' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'products' => [],
                'message' => 'Error searching products: ' . $e->getMessage()
            ]);
        }
    }

    public function calculateOnePieceCost($formula, $branchConnection = null)
    {
        $ingredients = is_array($formula->ingredients) ? $formula->ingredients : json_decode($formula->ingredients, true);

        $totalCost = 0;

        foreach ($ingredients as $ingredient) {
            $productId = $ingredient['product_id'] ?? null;
            $quantity = $ingredient['quantity'] ?? 0;

            if (!$productId || $quantity <= 0)
                continue;

            $Inventoryproducts = $branchConnection
                ? Inventory::on($branchConnection)
                    ->where('product_id', $productId)
                    ->select('*', 'purchase_price as purchase_rate')
                    ->where('type', 'in')
                    ->orderBy('id', 'asc') // FIFO (oldest first)
                    ->get()
                : Inventory::where('product_id', $productId)
                    ->select('*', 'purchase_price as purchase_rate')
                    ->where('type', 'in')
                    ->orderBy('id', 'asc')
                    ->get();

            $purchaseRate = null;

            foreach ($Inventoryproducts as $Inventoryproduct) {
                if ($Inventoryproduct->quantity > 0) {
                    $purchaseRate = $Inventoryproduct->purchase_rate;
                    break; // stop after first with non-zero quantity
                }
            }
            
            // Load from correct DB connection
            $product = $branchConnection
                ? Product::on($branchConnection)->find($productId)
                : Product::find($productId);
            if (empty($purchaseRate) || $purchaseRate == 0) {
                if (!empty($product->purchase_rate) && $product->purchase_rate != 0) {
                    $purchaseRate = $product->purchase_rate;
                } else {
                    $purchaseRate = $product->sale_rate_a;
                }
            }
            $totalCost += $quantity * $purchaseRate;
        }
        return $totalCost / $formula->quantity;
    }

}
