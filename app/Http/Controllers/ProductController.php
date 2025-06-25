<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\HsnCode;
use App\Models\Product;
use App\Traits\BranchAuthTrait;
use Exception;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;

class ProductController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();

        if ($auth instanceof \Illuminate\Http\JsonResponse) {
            return $auth;
        }

        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $products = collect();

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            // Get the selected branch ID from request
            $selectedBranchId = $request->get('branch_id');
            $availableBranches = $branch; // All active branches for dropdown

            if (!$selectedBranchId) {
                // No branch selected - return empty collection with message
                $products = collect();
                $selectedBranch = null;
                $showNoBranchMessage = true;

                return view('products.index', compact(
                    'products',
                    'role',
                    'availableBranches',
                    'selectedBranch',
                    'showNoBranchMessage'
                ));
            }

            // Find the selected branch
            $selectedBranch = $branch->where('id', $selectedBranchId)->first();

            if (!$selectedBranch) {
                // Invalid branch ID - redirect with error
                return redirect()->route('products.index')
                    ->with('error', 'Invalid branch selected');
            }

            // Configure connection for selected branch
            configureBranchConnection($selectedBranch);

            // Get products for the selected branch with pagination
            $products = Product::on($selectedBranch->connection_name)
                ->with(['category', 'hsnCode', 'pCompany'])
                ->orderByDesc('id')
                ->paginate(10);

            // Append branch_id to pagination links
            $products->appends($request->query());

            $showNoBranchMessage = false;

            return view('products.index', compact(
                'products',
                'role',
                'availableBranches',
                'selectedBranch',
                'showNoBranchMessage'
            ));

        } else {
            $products = Product::on($branch->connection_name)
                ->with(['category', 'hsnCode', 'pCompany'])
                ->orderByDesc('id')
                ->paginate(10);
        }

        return view('products.index', compact('products', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branch = Branch::all();
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        return view('products.create', compact('branch', 'role'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ?string $branch = null)
    {
        try {

            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];

            if (strtolower($role->role_name) === 'super admin') {
                $branchId = $request->branch;

                if (!$branchId) {
                    return redirect()->back()->with('error', 'Branch ID is required for Super Admin.');
                }

                $branch = Branch::findOrFail($branchId);
                configureBranchConnection($branch);
            } else {
                $branch = $auth['branch'];
            }

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
                'cgst' => 'nullable|numeric|min:0',
                'igst' => 'nullable|numeric|min:0',
                'cess' => 'nullable|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'purchase_rate' => 'nullable|numeric|min:0',
                'sale_rate_a' => 'nullable|numeric|min:0',
                'sale_rate_b' => 'nullable|numeric|min:0',
                'sale_rate_c' => 'nullable|numeric|min:0',
                'converse_carton' => 'nullable|numeric|min:0',
                'carton_barcode' => 'nullable|numeric|min:0',
                'converse_box' => 'nullable|numeric|min:0',
                'box_barcode' => 'nullable|numeric|min:0',
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
                // 'gst_active' => 'nullable'
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
                $company = Company::on($branch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_company']],
                    ['name' => $validate['product_company'], 'status' => 1]
                );
                $companyId = $company->id;
            }

            // Handle Category - use branch connection
            $categoryId = null;
            if (!empty($validate['product_category'])) {
                $category = Category::on($branch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_category']],
                    ['name' => $validate['product_category'], 'status' => 1]
                );
                $categoryId = $category->id;
            }

            // Handle HSN Code - use branch connection
            $hsnCodeId = null;
            if (!empty($validate['hsn_code'])) {
                // Prepare GST data as JSON
                $gstData = [
                    'SGST' => (float) ($validate['sgst'] ?? 0),
                    'CGST' => (float) ($validate['cgst_1'] ?? 0),
                    'IGST' => (float) ($validate['cgst_2'] ?? 0), // cgst_2 is IGST
                    'CESS' => (float) ($validate['cess'] ?? 0)
                ];

                $hsnCode = HsnCode::on($branch->connection_name)->firstOrCreate(
                    ['hsn_code' => $validate['hsn_code']],
                    [
                        'hsn_code' => $validate['hsn_code'],
                        'gst' => json_encode($gstData)
                    ]
                );
                // Update GST if HSN code already exists
                if (!$hsnCode->wasRecentlyCreated) {
                    $hsnCode->update(['gst' => json_encode($gstData)]);
                }
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
                'cgst1' => $validate['cgst'] ?? 0,
                'cgst2' => $validate['igst'] ?? 0, //igst
                'cess' => $validate['cess'] ?? 0,
                'mrp' => $validate['mrp'] ?? 0,
                'purchase_rate' => $validate['purchase_rate'] ?? 0,
                'sale_rate_a' => $validate['sale_rate_a'] ?? 0,
                'sale_rate_b' => $validate['sale_rate_b'] ?? 0,
                'sale_rate_c' => $validate['sale_rate_c'] ?? 0,
                'sale_online' => isset($validate['sale_online']) ? 1 : 0,
                // 'gst_active' => isset($validate['gst_active']) ? 1 : 0,
                'converse_carton' => $validate['converse_carton'] ?? 0,
                'carton_barcode' => $validate['carton_barcode'] ?? 0,
                'converse_box' => $validate['converse_box'] ?? 0,
                'box_barcode' => $validate['box_barcode'] ?? 0,
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
            $product = Product::on($branch->connection_name)->create($data);

            // Redirect to product index with success message and product data
            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');
            // ->with('product', $product);
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        // If Super Admin, use `branch` from route or query
        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;
            $branch = Branch::findOrFail($branchId);

            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
            ->where('id', $id)
            ->firstOrFail();

        return view('products.show', compact('product', 'role', 'branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        // If Super Admin, use `branch` from route or query
        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;
            $branch = Branch::findOrFail($branchId);

            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
            ->where('id', $id)
            ->firstOrFail();

        return view('products.edit', compact('product', 'role', 'branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, ?string $branchId = null)
    {
        try {

            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];

            if (strtolower($role->role_name) === 'super admin') {
                if (!$branchId) {
                    return redirect()->back()->with('error', 'Branch ID is required for Super Admin.');
                }

                $branch = Branch::findOrFail($branchId);
                configureBranchConnection($branch);
            } else {
                $branch = $auth['branch'];
            }

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
                'cgst' => 'nullable|numeric|min:0',
                'igst' => 'nullable|numeric|min:0',
                'cess' => 'nullable|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'purchase_rate' => 'nullable|numeric|min:0',
                'sale_rate_a' => 'nullable|numeric|min:0',
                'sale_rate_b' => 'nullable|numeric|min:0',
                'sale_rate_c' => 'nullable|numeric|min:0',
                'converse_carton' => 'nullable|numeric|min:0',
                'carton_barcode' => 'nullable|numeric|min:0',
                'converse_box' => 'nullable|numeric|min:0',
                'box_barcode' => 'nullable|numeric|min:0',
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
                // 'gst_active' => 'nullable'
            ]);

            // get single product data
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->first();

            // Upload new image if available
            $path = $product->image; // Keep existing image by default
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('products', $filename, 'public');

                // Optionally delete old image
                if ($product->image && \Storage::disk('public')->exists($product->image)) {
                    \Storage::disk('public')->delete($product->image);
                }
            }

            // Handle Company - use branch connection
            $companyId = $product->company; // Keep existing company by default
            if (!empty($validate['product_company'])) {
                $company = Company::on($branch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_company']],
                    ['name' => $validate['product_company'], 'status' => 1]
                );
                $companyId = $company->id;
            }

            // Handle Category - use branch connection
            $categoryId = $product->category_id; // Keep existing category by default
            if (!empty($validate['product_category'])) {
                $category = Category::on($branch->connection_name)->firstOrCreate(
                    ['name' => $validate['product_category']],
                    ['name' => $validate['product_category'], 'status' => 1]
                );
                $categoryId = $category->id;
            }

            // Handle HSN Code - use branch connection
            $hsnCodeId = $product->hsn_code_id; // Keep existing HSN code by default
            if (!empty($validate['hsn_code'])) {
                // Prepare GST data as JSON
                $gstData = [
                    'SGST' => (float) ($validate['sgst'] ?? 0),
                    'CGST' => (float) ($validate['cgst_1'] ?? 0),
                    'IGST' => (float) ($validate['cgst_2'] ?? 0), // cgst_2 is IGST
                    'CESS' => (float) ($validate['cess'] ?? 0)
                ];

                $hsnCode = HsnCode::on($branch->connection_name)->firstOrCreate(
                    ['hsn_code' => $validate['hsn_code']],
                    [
                        'hsn_code' => $validate['hsn_code'],
                        'gst' => json_encode($gstData)
                    ]
                );
                // Update GST if HSN code already exists
                if (!$hsnCode->wasRecentlyCreated) {
                    $hsnCode->update(['gst' => json_encode($gstData)]);
                }
                $hsnCodeId = $hsnCode->id;
            }

            // Prepare update data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'],
                'image' => $path,
                'search_option' => $validate['search_option'] ?? null,
                'unit_types' => $validate['unit_type'] ?? null,
                'decimal_btn' => isset($validate['decimal_btn']) ? 1 : 0,
                'company' => $companyId,
                'category_id' => $categoryId,
                'hsn_code_id' => $hsnCodeId,
                'sgst' => $validate['sgst'] ?? 0,
                'cgst1' => $validate['cgst'] ?? 0,
                'cgst2' => $validate['igst'] ?? 0,
                'cess' => $validate['cess'] ?? 0,
                'mrp' => $validate['mrp'] ?? 0,
                'purchase_rate' => $validate['purchase_rate'] ?? 0,
                'sale_rate_a' => $validate['sale_rate_a'] ?? 0,
                'sale_rate_b' => $validate['sale_rate_b'] ?? 0,
                'sale_rate_c' => $validate['sale_rate_c'] ?? 0,
                'sale_online' => isset($validate['sale_online']) ? 1 : 0,
                // 'gst_active' => isset($validate['gst_active']) ? 1 : 0,
                'converse_carton' => $validate['converse_carton'] ?? 0,
                'carton_barcode' => $validate['carton_barcode'] ?? 0,
                'converse_box' => $validate['converse_box'] ?? 0,
                'box_barcode' => $validate['box_barcode'] ?? 0,
                'converse_pcs' => $validate['converse_pcs'] ?? 0,
                'negative_billing' => $validate['negative_billing'] ?? null,
                'min_qty' => $validate['min_qty'] ?? 0,
                'reorder_qty' => $validate['reorder_qty'] ?? 0,
                'discount' => $validate['discount'] ?? null,
                'max_discount' => $validate['max_discount'] ?? 0,
                'discount_scheme' => $validate['discount_scheme'] ?? null,
                'bonus_use' => $validate['bonus_use'] == 'yes' ? 1 : 0,
                'updated_by' => session('branch_user_id'), // Track who updated the product
            ];

            // Update the product using branch connection
            $product->update($data);

            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (Exception $ex) {
            dd($ex->getMessage());
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

        $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
            ->where('id', $id)
            ->first();

        // Delete product image if exists
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function importProducts(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $request->validate([
            'excel_file' => 'required|file'
        ]);

        try {
            // Get your branch database connection name

            $file = $request->file('excel_file');
            // $extension = $file->getClientOriginalExtension();

            if ($xlsx = SimpleXLSX::parse($file->getRealPath())) {

                $rows = $xlsx->rows();
                array_shift($rows);

                foreach ($rows as $row) {
                    if (!empty($row[0]) || !empty($row[1])) { // Check if name exists

                        $companyId = null;
                        if (!empty($row[6])) {
                            $company = Company::on($branch->connection_name)
                                ->where('name', $row[6])
                                ->first();

                            if (!$company) {
                                $companyId = Company::on($branch->connection_name)->insertGetId([
                                    'name' => $row[6],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                $companyId = $company->id;
                            }
                        }

                        // Handle Category - create or get existing
                        $categoryId = null;
                        if (!empty($row[7])) {
                            $category = Category::on($branch->connection_name)
                                ->where('name', $row[7])
                                ->first();

                            if (!$category) {
                                $categoryId = Category::on($branch->connection_name)->insertGetId([
                                    'name' => $row[7],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                $categoryId = $category->id;
                            }
                        }

                        // Handle HSN Code - create or get existing
                        $hsnCodeId = null;
                        if (!empty($row[8])) {
                            $hsnCode = HsnCode::on($branch->connection_name)
                                ->where('hsn_code', $row[8])
                                ->first();

                            if (!$hsnCode) {
                                $hsnCodeId = HsnCode::on($branch->connection_name)->insertGetId([
                                    'hsn_code' => $row[8],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                $hsnCodeId = $hsnCode->id;
                            }
                        }
                        $data = [
                            'product_name' => $row[0] ?? '',
                            'barcode' => $row[1] ?? '',
                            // 'image'=> $row[2] ?? '',
                            'search_option' => $row[3] ?? '',
                            'unit_types' => $row[4] ?? '',
                            'decimal_btn' => $row[5] ?? '',
                            'company' => $companyId ?? '',
                            'category_id' => $categoryId ?? '',
                            'hsn_code_id' => $hsnCodeId ?? '',
                            'sgst' => $row[9] ?? '',
                            'cgst1' => $row[10] ?? '',
                            'cgst2' => $row[11] ?? '',
                            'cess' => $row[12] ?? '',
                            'mrp' => $row[13] ?? '',
                            'purchase_rate' => $row[14] ?? '',
                            'sale_rate_a' => $row[15] ?? '',
                            'sale_rate_b' => $row[16] ?? '',
                            'sale_rate_c' => $row[17] ?? '',
                            'sale_online' => $row[18] ?? '',
                            'converse_carton' => $row[19] ?? '',
                            'converse_box' => $row[20] ?? '',
                            'negative_billing' => $row[22] ?? '',
                            'min_qty' => $row[23] ?? '',
                            'reorder_qty' => $row[24] ?? '',
                            'discount' => $row[25] ?? '',
                            'max_discount' => $row[26] ?? '',
                            'discount_scheme' => $row[27] ?? '',
                            'bonus_use' => strtoupper($row[28]) === 'YES' ? 1 : 0,
                        ];
                        // dd($data);
                        Product::on($branch->connection_name)->insert($data);
                    }
                }
            }

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function searchCompany(Request $request)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return response()->json(['companies' => []]);
        }
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];

            // If Super Admin, use `branch` from route or query
            if (strtolower($role->role_name) === 'super admin') {
                $branchId = $request->branch;
                if (empty($branchId)) {
                    return response()->json(['companies' => []]);
                }

                $branch = Branch::findOrFail($branchId);
                configureBranchConnection($branch);
            } else {
                // Normal user — get branch from auth
                $branch = $auth['branch'];
            }

            $search = $request->get('search', '');
            if (empty($search)) {
                return response()->json(['companies' => []]);
            }

            $companies = Company::on($branch->connection_name)
                ->where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                ->limit(10)
                ->pluck('name') // Return company names
                ->toArray();

            return response()->json(['companies' => $companies]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function searchCategory(Request $request)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return response()->json(['categories' => []]);
        }
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];

            // If Super Admin, use `branch` from route or query
            if (strtolower($role->role_name) === 'super admin') {
                $branchId = $request->branch;
                if (empty($branchId)) {
                    return response()->json(['categories' => []]);
                }

                $branch = Branch::findOrFail($branchId);
                configureBranchConnection($branch);
            } else {
                // Normal user — get branch from auth
                $branch = $auth['branch'];
            }

            $search = $request->get('search', '');
            if (empty($search)) {
                return response()->json(['categories' => []]);
            }

            $categories = Category::on($branch->connection_name)
                ->where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                ->limit(10)
                ->pluck('name') // Return category names
                ->toArray();

            return response()->json(['categories' => $categories]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function searchHsnCode(Request $request)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return response()->json(['hsn_codes' => []]);
        }
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];

            // If Super Admin, use `branch` from route or query
            if (strtolower($role->role_name) === 'super admin') {
                $branchId = $request->branch;
                if (empty($branchId)) {
                    return response()->json(['hsn_codes' => []]);
                }

                $branch = Branch::findOrFail($branchId);
                configureBranchConnection($branch);
            } else {
                // Normal user — get branch from auth
                $branch = $auth['branch'];
            }

            $search = $request->get('search', '');
            if (empty($search)) {
                return response()->json(['hsn_codes' => []]);
            }

            $hsn_codes = HsnCode::on($branch->connection_name)
                ->where('hsn_code', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'hsn_code' => $item->hsn_code,
                        'gst' => $item->gst // This will be JSON string or array depending on your cast
                    ];
                })
                ->toArray();

            // dd($hsn_codes);

            return response()->json(['hsn_codes' => $hsn_codes]);
        } catch (\Throwable $th) {
            return response()->json(['hsn_codes' => []]);
        }
    }
}
