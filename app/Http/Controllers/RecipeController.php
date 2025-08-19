<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $recipes = Recipe::with('product')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $recipes = Recipe::on($branch->connection_name)->with('product')->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('recipe.index', compact('recipes'));
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

        return view('recipe.create');
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
            // 'ingredients' => 'required',

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
                'ingredients' => !empty($ingredients) ? json_encode($ingredients) : null
            ];
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Recipe::create($data);
            } else {
                Recipe::on($branch->connection_name)->create($data);
            }

            return redirect()->route('recipe.index')
                ->with('success', 'Recipe created successfully!');
        } catch (Exception $ex) {
            return redirect()->back()
                ->with('error', "Can't create Recipe" . $ex->getMessage());
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
            $recipe = Recipe::with('product')->findOrFail($id);
            $connection = config('database.default');
        } else {
            $recipe = Recipe::on($branch->connection_name)->with('product')->findOrFail($id);
            $connection = $branch->connection_name;
        }

        $ingredientsRaw = is_string($recipe->ingredients)
            ? json_decode($recipe->ingredients, true)
            : $recipe->ingredients;

        $ingredientsWithProduct = collect($ingredientsRaw)->map(function ($ingredient) use ($connection) {
            $product = Product::on($connection)->find($ingredient['product_id']);
            return [
                'product' => $product,
                'qty' => $ingredient['quantity'],
            ];
        });

        return view('recipe.show', [
            'recipe' => $recipe,
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
            $recipe = Recipe::with('product')->findOrFail($id);
            $connection = config('database.default');
        } else {
            $recipe = Recipe::on($branch->connection_name)->with('product')->findOrFail($id);
            $connection = $branch->connection_name;
        }

         $ingredientsRaw = is_string($recipe->ingredients)
            ? json_decode($recipe->ingredients, true)
            : $recipe->ingredients;

        $ingredientsWithProduct = collect($ingredientsRaw)->map(function ($ingredient) use ($connection) {
            $product = Product::on($connection)->find($ingredient['product_id']);
            return [
                'product' => $product,
                'qty' => $ingredient['quantity'] ?? 0,
            ];
        });

        return view('recipe.edit', compact('recipe','ingredientsWithProduct'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Validate inputs
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            // 'qty' => 'required|integer|min:0',
            'ingredient_product_id' => 'required|array',
            'ingredient_product_id.*' => 'integer|exists:products,id',
            'ingredient_quantity' => 'required|array',
            'ingredient_quantity.*' => 'integer|min:0',
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
                // 'qty' => $request->qty,
                'ingredients' => json_encode($ingredients)
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $recipe = Recipe::findOrFail($id);
            } else {
                $recipe = Recipe::on($branch->connection_name)->findOrFail($id);
            }

                $recipe->update($data);

            return redirect()->route('recipe.index')->with('success', 'recipe updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Recipe update failed: '.$e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update recipe data. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        //
    }
}
