<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\HsnCode;
use App\Models\Inventory;
use App\Models\PopularProducts;
use App\Models\Product;
use App\Traits\BranchAuthTrait;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use BranchAuthTrait;
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

    // public function findProductByBarcode($barcode)
    // {
    //     $matchingProducts = [];
    //     $branches = Branch::where('status', 'active')->get();

    //     foreach ($branches as $branch) {
    //         try {
    //             $product = Product::on($branch->connection_name)
    //                 ->where('barcode', $barcode)
    //                 ->value('id');

    //             if ($product) {
    //                 $matchingProducts[] = [
    //                     'branch' => $branch->name,
    //                     'branch_connection' => $branch->connection_name,
    //                     'product' => $product
    //                 ];
    //             }
    //         } catch (\Exception $e) {
    //             $matchingProducts[] = [
    //                 'branch' => $branch->name,
    //                 'error' => $e->getMessage()
    //             ];
    //         }
    //     }

    //     if (empty($matchingProducts)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Product not found in any branch.'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $matchingProducts
    //     ]);
    // }


    /**
     * Find product by barcode from auth branch
     * @param mixed $barcode
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function findProductByBarcode($barcode)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            $products = Product::on($branch->connection_name)
                ->where('barcode', $barcode)
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching products found in this branch.'
                ], 404);
            }

            // Simple product data for frontend popup
            $enhancedProducts = $products->map(function ($product) use ($branch) {
                // Get inventory for this product
                $inventory = Inventory::on($branch->connection_name)
                    ->where('product_id', $product->id)
                    ->first();

                // Check if loose quantity is supported (using decimal_btn field)
                $isLooseQuantityProduct = $product->decimal_btn == 1;

                // Determine product price based on unit type
                $productPrice = 0;
                $priceUnit = '';

                if ($isLooseQuantityProduct) {
                    // For loose quantity products (KG, LITER), price is per base unit
                    $productPrice = $product->sale_rate_a; // Price per KG or LITER
                    $priceUnit = strtoupper($product->unit_types); // KG, LITER, etc.
                } else {
                    // For fixed quantity products (PCS), price is per piece
                    $productPrice = $product->sale_rate_a; // Price per PCS
                    $priceUnit = 'PCS';
                }

                // Simple product data for popup
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'barcode' => $product->barcode,
                    'image' => $product->image,
                    'unit_types' => $product->unit_types,
                    'decimal_btn' => $product->decimal_btn,
                    'company' => $product->company,
                    'mrp' => $product->mrp,
                    'sale_rate_a' => $product->sale_rate_a,
                    'sale_rate_b' => $product->sale_rate_b,
                    'sale_rate_c' => $product->sale_rate_c,
                    'gst_percentage' => $product->sgst + $product->cgst1 + $product->cgst2 + $product->cess,
                    'min_qty' => $product->min_qty,
                    'discount' => $product->discount,
                    'max_discount' => $product->max_discount,

                    // Price information for popup
                    'product_price' => $productPrice, // Price per unit (1 KG, 1 LITER, or 1 PCS)
                    'price_unit' => $priceUnit, // KG, LITER, PCS

                    // Inventory information
                    'available_quantity' => $inventory ? $inventory->quantity : 0,
                    'in_stock' => $inventory ? $inventory->quantity > 0 : false,

                    // Loose quantity support flag
                    'is_loose_quantity' => $isLooseQuantityProduct,

                    // Additional product details
                    'hsn_code_id' => $product->hsn_code_id,
                    'category_id' => $product->category_id,
                    'sgst' => $product->sgst,
                    'cgst1' => $product->cgst1,
                    'cgst2' => $product->cgst2,
                    'cess' => $product->cess,
                ];
            });

            return response()->json([
                'success' => true,
                'branch' => $branch->name,
                'branch_connection' => $branch->connection_name,
                'data' => $enhancedProducts
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            configureBranchConnection($userBranch);

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
                $company = Company::on($userBranch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_company']],
                    ['name' => $validate['product_company'], 'status' => 1]
                );
                $companyId = $company->id;
            }

            // Handle Category - use branch connection
            $categoryId = null;
            if (!empty($validate['product_category'])) {
                $category = Category::on($userBranch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_category']],
                    ['name' => $validate['product_category'], 'status' => 1]
                );
                $categoryId = $category->id;
            }

            // Handle HSN Code - use branch connection
            $hsnCodeId = null;
            if (!empty($validate['hsn_code'])) {
                $hsnCode = HsnCode::on($userBranch->connection_name)->firstOrCreate(
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
            $product = Product::on($userBranch->connection_name)->create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    // public function showAllProducts()
    // {
    //     $allProducts = [];
    //     $branches = Branch::where('status', 'active')->get();

    //     foreach ($branches as $branch) {
    //         try {
    //             $this->configureBranchConnection($branch);
    //             $products = Product::on($branch->connection_name)->get();

    //             $allProducts[] = [
    //                 'branch' => $branch->name,
    //                 'products' => $products
    //             ];
    //         } catch (\Exception $e) {
    //             $allProducts[] = [
    //                 'branch' => $branch->name,
    //                 'error' => $e->getMessage()
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $allProducts
    //     ]);
    // }

    /**
     * Show all Products for login branch
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showAllProducts(Request $request)
    {
        try {
            // Get authenticated user from token
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch from master database
            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            // If user has no branch or branch is inactive
            if (!$userBranch) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No accessible branch found for user'
                ]);
            }

            $allProducts = [];

            try {
                // Dynamically configure branch database connection
                configureBranchConnection($userBranch);

                // Fetch products from branch database
                $products = Product::on($userBranch->connection_name)->get();

                $allProducts[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'products' => $products
                ];
            } catch (\Exception $e) {
                $allProducts[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $allProducts,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of getCategories
     * @return mixed|\Illuminate\Http\JsonResponse
     * Get All categories from login branch
     */
    public function getCategories()
    {
        try {
            // Get authenticated user from token
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch from master database
            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            // If user has no branch or branch is inactive
            if (!$userBranch) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No accessible branch found for user'
                ]);
            }

            $allCategories = [];

            try {
                // Dynamically configure branch database connection
                configureBranchConnection($userBranch);

                // Fetch products from branch database
                $categories = Category::on($userBranch->connection_name)->get();

                $allCategories[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'categories' => $categories
                ];
            } catch (\Exception $e) {
                $allCategories[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $allCategories,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of getCompanies
     * @return mixed|\Illuminate\Http\JsonResponse
     * Get all companies from login branch
     */
    public function getCompanies()
    {
        try {
            // Get authenticated user from token
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch from master database
            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            // If user has no branch or branch is inactive
            if (!$userBranch) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No accessible branch found for user'
                ]);
            }

            $allCompanies = [];

            try {
                // Dynamically configure branch database connection
                configureBranchConnection($userBranch);

                // Fetch products from branch database
                $companies = Company::on($userBranch->connection_name)->get();

                $allCompanies[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'companies' => $companies
                ];
            } catch (\Exception $e) {
                $allCompanies[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $allCompanies,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of getHsnCode
     * @return mixed|\Illuminate\Http\JsonResponse
     * Get HSN Code from login branch
     */
    public function getHsnCode()
    {
        try {
            // Get authenticated user from token
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch from master database
            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            // If user has no branch or branch is inactive
            if (!$userBranch) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No accessible branch found for user'
                ]);
            }

            $allHsnCode = [];

            try {
                // Dynamically configure branch database connection
                configureBranchConnection($userBranch);

                // Fetch products from branch database
                $hsnCodes = HsnCode::on($userBranch->connection_name)->get();

                $allHsnCode[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'companies' => $hsnCodes
                ];
            } catch (\Exception $e) {
                $allHsnCode[] = [
                    'branch' => $userBranch->name,
                    'connection' => $userBranch->connection_name,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $allHsnCode,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Search product by barcode, product_name or search_option
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function searchProduct(Request $request)
    {
        try {
            // Get authenticated user from token
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch from master database
            $userBranch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            // If user has no branch or branch is inactive
            if (!$userBranch) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No accessible branch found for user'
                ]);
            }

            // Configure branch database connection
            configureBranchConnection($userBranch);

            $request->validate([
                'search_keyword' => 'required|string|min:1',
                'search_type' => 'nullable'
            ]);

            $searchKeyword = trim($request->input('search_keyword'));
            $searchType = strtolower($request->input('search_type'));

            // Build search query based on search type
            $query = Product::on($userBranch->connection_name);

            if ($searchType === 'barcode') {
                $query->where('barcode', $searchKeyword);
            } else {
                $query->where('product_name', 'LIKE', '%' . $searchKeyword . '%')
                    ->orWhere('search_option', 'LIKE', '%' . $searchKeyword . '%');
            }

            $products = $query->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'branch' => $userBranch->name,
                    'branch_code' => $userBranch->code,
                    'search_query' => $searchKeyword,
                    'search_type' => $searchType,
                    'total_found' => $products->count(),
                    'products' => $products
                ]
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $ex->getMessage()
            ], 500);
        }

    }

    public function getUserPopularProducts(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];

            // Use Eloquent on dynamic connection
            $popularSelections = PopularProducts::on($branch->connection_name)
                ->where('user_id', $user->id)
                ->with('product') // eager load product
                ->orderByDesc('count')
                ->take(10)
                ->get();

            if ($popularSelections->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No popular products found for this user.'
                ], 404);
            }

            // Prepare data: attach selection count to product
            $popularProducts = $popularSelections->map(function ($selection) {
                $product = $selection->product;
                $product->selection_count = $selection->count;
                return $product;
            });

            return response()->json([
                'success' => true,
                'branch' => $branch->name,
                'data' => $popularProducts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while fetching popular products.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function showCategoriesFromAllBranches()
    // {
    //     $allCategories = [];
    //     $branches = Branch::where('status', 'active')->get();

    //     foreach ($branches as $branch) {
    //         try {
    //             $categories = Category::on($branch->connection_name)->get();

    //             $allCategories[] = [
    //                 'branch' => $branch->name,
    //                 'connection' => $branch->connection_name,
    //                 'categories' => $categories
    //             ];
    //         } catch (\Exception $e) {
    //             \Log::error("Error in branch '{$branch->name}' (conn: {$branch->connection_name}): " . $e->getMessage());

    //             $allCategories[] = [
    //                 'branch' => $branch->name,
    //                 'error' => $e->getMessage()
    //             ];
    //         }
    //     }

    //     return response()->json($allCategories);
    // }

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
