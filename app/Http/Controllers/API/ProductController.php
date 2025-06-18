<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\HsnCode;
use App\Models\Product;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function findProductByBarcode($barcode)
    {
        $matchingProducts = [];
        $branches = Branch::where('status', 'active')->get();

        foreach ($branches as $branch) {
            try {
                $product = Product::on($branch->connection_name)
                    ->where('barcode', $barcode)
                    ->value('id');

                if ($product) {
                    $matchingProducts[] = [
                        'branch' => $branch->name,
                        'branch_connection' => $branch->connection_name,
                        'product' => $product
                    ];
                }
            } catch (\Exception $e) {
                $matchingProducts[] = [
                    'branch' => $branch->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (empty($matchingProducts)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in any branch.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $matchingProducts
        ]);
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

            $validate = $request->validate([
                'product_barcode' => 'required|string|max:255',
                'product_name' => 'required|string|max:255',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'search_option' => 'nullable|string',
                'unit_type' => 'nullable|string',
                'product_company' => 'nullable|string',
                'product_category' => 'nullable|string',
                'hsn_code' => 'nullable|string',
                'sgst' => 'nullable|numeric|min:0',
                'cgst_1' => 'nullable|numeric|min:0',
                'cgst_2' => 'nullable|numeric|min:0',
                'cess' => 'nullable|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'purchase_rate' => 'nullable|numeric|min:0',
                'sale_rate_a' => 'nullable|numeric|min:0',
                'sale_rate_b' => 'nullable|numeric|min:0',
                'sale_rate_c' => 'nullable|numeric|min:0',
                'converse_carton' => 'nullable|numeric|min:0',
                'converse_boc' => 'nullable|numeric|min:0',
                'converse_pcs' => 'nullable|numeric|min:0',
                'negative_billing' => 'nullable',
                'min_qty' => 'nullable|numeric|min:0',
                'reorder_qty' => 'nullable|numeric|min:0',
                'discount' => 'nullable',
                'max_discount' => 'nullable|numeric|min:0|max:100',
                'discount_scheme' => 'nullable|string',
                'bonus_use' => 'nullable',
                'decimal_btn' => 'nullable',
                'sale_online' => 'nullable',
                'gst_active' => 'nullable'
            ]);

            // upload product image
            // dd($validate);

            // Upload product image (optional)
            $path = null;
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('products', $filename, 'public');
            }

            // Handle Company - use branch connection
            // Get or create related company
            $companyId = null;
            if (!empty($validate['product_company'])) {
                $company = Company::on($branchConnection)->firstOrCreate(
                    ['name' => $validate['product_company']],
                    ['name' => $validate['product_company'], 'status' => 1]
                );
                $companyId = $company->id;
            }

            // Handle Category - use branch connection
            $categoryId = null;
            if (!empty($validate['product_category'])) {
                $category = Category::on($branchConnection)->firstOrCreate(
                    ['name' => $validate['product_category']],
                    ['name' => $validate['product_category'], 'status' => 1]
                );
                $categoryId = $category->id;
            }

            // Handle HSN Code - use branch connection
            $hsnCodeId = null;
            if (!empty($validate['hsn_code'])) {
                $hsnCode = HsnCode::on($branchConnection)->firstOrCreate(
                    ['hsn_code' => $validate['hsn_code']],
                    ['hsn_code' => $validate['hsn_code']]
                );
                $hsnCodeId = $hsnCode->id;
            }

            // Prepare product data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'],
                'image' => $path, // nullable image
                'search_option' => $validate['search_option'] ?? null,
                'unit_types' => $validate['unit_type'] ?? null,
                'decimal_btn' => isset($validate['decimal_btn']) ? 1 : 0,
                'company' => $companyId,
                'category_id' => $categoryId,
                'hsn_code_id' => $hsnCodeId,
                'sgst' => $validate['sgst'] ?? 0,
                'cgst1' => $validate['cgst_1'] ?? 0,
                'cgst2' => $validate['cgst_2'] ?? 0,
                'cess' => $validate['cess'] ?? 0,
                'mrp' => $validate['mrp'] ?? 0,
                'purchase_rate' => $validate['purchase_rate'] ?? 0,
                'sale_rate_a' => $validate['sale_rate_a'] ?? 0,
                'sale_rate_b' => $validate['sale_rate_b'] ?? 0,
                'sale_rate_c' => $validate['sale_rate_c'] ?? 0,
                'sale_online' => isset($validate['sale_online']) ? 1 : 0,
                'gst_active' => isset($validate['gst_active']) ? 1 : 0,
                'converse_carton' => $validate['converse_carton'] ?? 0,
                'converse_box' => $validate['converse_boc'] ?? 0,
                'converse_pcs' => $validate['converse_pcs'] ?? 0,
                'negative_billing' => $validate['negative_billing'] ?? null,
                'min_qty' => $validate['min_qty'] ?? 0,
                'reorder_qty' => $validate['reorder_qty'] ?? 0,
                'discount' => $validate['discount'] ?? null,
                'max_discount' => $validate['max_discount'] ?? 0,
                'discount_scheme' => $validate['discount_scheme'] ?? null,
                'bonus_use' => $validate['bonus_use'] == 'yes' ? 1 : 0
            ];

            // Create the product using branch connection
            $product = Product::on($branchConnection)->create($data);

            // Redirect to product index with success message and product data
            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');
            // ->with('product', $product);
        } catch (Exception $ex) {
            // dd($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function showAllProducts()
    {
        $allProducts = [];
        $branches = Branch::where('status', 'active')->get();

        foreach ($branches as $branch) {
            try {
                $products = Product::on($branch->connection_name)->get();

                $allProducts[] = [
                    'branch' => $branch->name,
                    'products' => $products
                ];
            } catch (\Exception $e) {
                $allProducts[] = [
                    'branch' => $branch->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $allProducts
        ]);
    }


    public function showCategoriesFromAllBranches()
    {
        $allCategories = [];
        $branches = Branch::where('status', 'active')->get();

        foreach ($branches as $branch) {
            try {
                $categories = Category::on($branch->connection_name)->get();

                $allCategories[] = [
                    'branch' => $branch->name,
                    'connection' => $branch->connection_name,
                    'categories' => $categories
                ];
            } catch (\Exception $e) {
                \Log::error("Error in branch '{$branch->name}' (conn: {$branch->connection_name}): " . $e->getMessage());

                $allCategories[] = [
                    'branch' => $branch->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json($allCategories);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
