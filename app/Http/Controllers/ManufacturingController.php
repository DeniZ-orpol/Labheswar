<?php

namespace App\Http\Controllers;

use App\Models\Manufacturing;
use App\Models\Category;
use App\Traits\BranchAuthTrait;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManufacturingController extends Controller
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
            $manufacturs = Manufacturing::with('product')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $manufacturs = Manufacturing::on($branch->connection_name)->with('product')->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('manufacturing.index', compact('manufacturs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

         return view('manufacturing.create');
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
        // Validate inputs
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:0',
            'ingredient_product_id' => 'required|array',
            'ingredient_product_id.*' => 'integer|exists:products,id',
            'ingredient_qty' => 'required|array',
            'ingredient_qty.*' => 'integer|min:0',
        ]);

        try {
            // Prepare ingredients array
            $ingredients = [];
            foreach ($request->ingredient_product_id as $index => $ingredientId) {
                $ingredients[] = [
                    'product_id' => $ingredientId,
                    'qty' => $request->ingredient_qty[$index] ?? 0,
                ];
            }

            // Create Manufacturing record
            // $manufacturing = new Manufacturing();
            // $manufacturing->product_id = $request->product_id;
            // $manufacturing->qty = $request->qty;
            // $manufacturing->ingredients = json_encode($ingredients);
            // $manufacturing->save();

            $data = [
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'ingredients' => json_encode($ingredients)
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Manufacturing::insert($data);
            } else {
                Manufacturing::on($branch->connection_name)->insert($data);
            }

            return redirect()->route('manufacturing.index')->with('success', 'Manufacturing added successfully!');
        } catch (\Exception $e) {
            // Log the error if you want
            \Log::error('Manufacturing save failed: '.$e->getMessage());

            // Redirect back with error message
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to save manufacturing data. Please try again.']);
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
            $manufacturing = Manufacturing::with('product')->findOrFail($id);
            $connection = config('database.default');
        } else {
            $manufacturing = Manufacturing::on($branch->connection_name)->with('product')->findOrFail($id);
            $connection = $branch->connection_name;
        }

        // Fetch ingredient product details
        $ingredientsWithProduct = collect($manufacturing->ingredients)->map(function ($ingredient) use ($connection) {
            $product = Product::on($connection)->find($ingredient['product_id']);
            return [
                'product' => $product,
                'qty' => $ingredient['qty'],
            ];
        });

        return view('manufacturing.show', [
            'manufacturing' => $manufacturing,
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
            $manufacturing = Manufacturing::with('product')->findOrFail($id);
        } else {
            $manufacturing = Manufacturing::on($branch->connection_name)->with('product')->findOrFail($id);
        }

        return view('manufacturing.edit', compact('manufacturing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manufacturing $manufacturing)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Validate inputs
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:0',
            'ingredient_product_id' => 'required|array',
            'ingredient_product_id.*' => 'integer|exists:products,id',
            'ingredient_qty' => 'required|array',
            'ingredient_qty.*' => 'integer|min:0',
        ]);

        try {
            // Prepare ingredients array
            $ingredients = [];
            foreach ($request->ingredient_product_id as $index => $ingredientId) {
                $ingredients[] = [
                    'product_id' => $ingredientId,
                    'qty' => $request->ingredient_qty[$index] ?? 0,
                ];
            }

            $data = [
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'ingredients' => json_encode($ingredients)
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $manufacturing->update($data);
            } else {
                $manufacturing->on($branch->connection_name)->update($data);
            }

            return redirect()->route('manufacturing.index')->with('success', 'Manufacturing updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Manufacturing update failed: '.$e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update manufacturing data. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manufacturing $manufacturing)
    {
        //
    }
}
