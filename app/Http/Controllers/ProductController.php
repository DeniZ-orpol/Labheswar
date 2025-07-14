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
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $search = $request->get('search');

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $query = Product::with(['category', 'hsnCode', 'pCompany']);
        } else {
            $query = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('barcode', 'LIKE', '%' . $search . '%')
                    ->orWhere('search_option', 'LIKE', '%' . $search . '%');
            });
        }

        $products = $query->orderByDesc('id')->paginate(20);

        // Preserve search parameter in pagination links
        if ($search) {
            $products->appends(['search' => $search]);
        }

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
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
            $branch = $auth['branch'];

            $validate = $request->validate([
                'product_barcode' => 'required|string|max:255',
                'product_name' => 'required|string|max:255',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'search_option' => 'nullable|string',
                'unit_type' => 'nullable|string',
                'product_company' => 'nullable|string',
                'product_category' => 'nullable|string',
                'hsn_code' => 'nullable|string',
                'hsn_code_id' => 'nullable',
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
                'price_1' => 'nullable|numeric|min:0',
                'price_2' => 'nullable|numeric|min:0',
                'price_3' => 'nullable|numeric|min:0',
                'price_4' => 'nullable|numeric|min:0',
                'price_5' => 'nullable|numeric|min:0',
                'kg_1' => 'nullable|numeric|min:0',
                'kg_2' => 'nullable|numeric|min:0',
                'kg_3' => 'nullable|numeric|min:0',
                'kg_4' => 'nullable|numeric|min:0',
                'kg_5' => 'nullable|numeric|min:0',
                // 'gst_active' => 'nullable'
            ]);

            // Upload product image (optional)
            $path = null;
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                // Create branch-specific directory
                $uploadPath = public_path('uploads/' . $branch->connection_name . '/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file to branch-specific folder
                $file->move($uploadPath, $filename);

                // Store path as: branch_connection/products/filename.jpg
                $path = 'uploads/' . $branch->connection_name . '/products/' . $filename;
            }

            // Handle Company - use branch connection
            // Get or create related company
            $companyId = null;
            if (!empty($validate['product_company'])) {
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $company = Company::firstOrCreate(
                        ['name' => $validate['product_company']],
                        ['name' => $validate['product_company'], 'status' => 1]
                    );
                } else {
                    $company = Company::on($branch->connection_name)->firstOrCreate(
                        ['name' => $validate['product_company']],
                        ['name' => $validate['product_company'], 'status' => 1]
                    );
                }
                $companyId = $company->id;
            }

            // Handle Category - use branch connection
            $categoryId = null;
            if (!empty($validate['product_category'])) {
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $category = Category::firstOrCreate(
                        ['name' => $validate['product_category']],
                        ['name' => $validate['product_category'], 'status' => 1]
                    );
                } else {
                    $category = Category::on($branch->connection_name)->firstOrCreate(
                        ['name' => $validate['product_category']],
                        ['name' => $validate['product_category'], 'status' => 1]
                    );
                }
                $categoryId = $category->id;
            }

            // Handle HSN Code - use branch connection
            // $hsnCodeId = null;
            // if (!empty($validate['hsn_code'])) {
            //     // Prepare GST data as JSON
            //     $gstData = [
            //         'SGST' => (float) ($validate['sgst'] ?? 0),
            //         'CGST' => (float) ($validate['cgst'] ?? 0),
            //         'IGST' => (float) ($validate['igst'] ?? 0), // cgst_2 is IGST
            //         'CESS' => (float) ($validate['cess'] ?? 0)
            //     ];

            //     $hsnCode = HsnCode::on($branch->connection_name)->firstOrCreate(
            //         ['hsn_code' => $validate['hsn_code']],
            //         [
            //             'hsn_code' => $validate['hsn_code'],
            //             'gst' => json_encode($gstData)
            //         ]
            //     );
            //     // Update GST if HSN code already exists
            //     if (!$hsnCode->wasRecentlyCreated) {
            //         $hsnCode->update(['gst' => json_encode($gstData)]);
            //     }
            //     $hsnCodeId = $hsnCode->id;
            // }

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
                'hsn_code_id' => $validate['hsn_code_id'] ?? null,
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
                'bonus_use' => $validate['bonus_use'] == 'yes' ? 1 : 0,
                'price_1' => $validate['price_1'] ?? 0,
                'price_2' => $validate['price_2'] ?? 0,
                'price_3' => $validate['price_3'] ?? 0,
                'price_4' => $validate['price_4'] ?? 0,
                'price_5' => $validate['price_5'] ?? 0,
                'kg_1' => $validate['kg_1'] ?? 0,
                'kg_2' => $validate['kg_2'] ?? 0,
                'kg_3' => $validate['kg_3'] ?? 0,
                'kg_4' => $validate['kg_4'] ?? 0,
                'kg_5' => $validate['kg_5'] ?? 0,
            ];

            // Create the product using branch connection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Product::create($data);
            } else {
                Product::on($branch->connection_name)->create($data);
            }

            // Redirect to product index with success message and product data
            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');
        } catch (Exception $ex) {
            dd($ex->getMessage());
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

        // If Super Admin, use `branch` from route or query
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $product = Product::with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->firstOrFail();
        }

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        // If Super Admin, use `branch` from route or query
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $product = Product::with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->firstOrFail();
        }

        // Pass page and search parameters to the view
        $page = $request->get('page');
        $search = $request->get('search');

        return view('products.edit', compact('product', 'page', 'search'));
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
                'product_barcode' => 'required|string|max:255',
                'product_name' => 'required|string|max:255',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'search_option' => 'nullable|string',
                'unit_type' => 'nullable|string',
                'product_company' => 'nullable|string',
                'company_id' => 'nullable',
                'product_category' => 'nullable|string',
                'category_id' => 'nullable',
                'hsn_code' => 'nullable|string',
                'hsn_code_id' => 'nullable',
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
                'price_1' => 'nullable|numeric|min:0',
                'price_2' => 'nullable|numeric|min:0',
                'price_3' => 'nullable|numeric|min:0',
                'price_4' => 'nullable|numeric|min:0',
                'price_5' => 'nullable|numeric|min:0',
                'kg_1' => 'nullable|numeric|min:0',
                'kg_2' => 'nullable|numeric|min:0',
                'kg_3' => 'nullable|numeric|min:0',
                'kg_4' => 'nullable|numeric|min:0',
                'kg_5' => 'nullable|numeric|min:0',
                // 'gst_active' => 'nullable'
            ]);

            // dd($request->all());
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $product = Product::with(['category', 'hsnCode', 'pCompany'])
                    ->where('id', $id)
                    ->first();
            } else {
                $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                    ->where('id', $id)
                    ->first();
            }

            // Upload new image if available
            $path = $product->image; // Keep existing image by default
            if ($request->hasFile('product_image')) {
                // Delete old image if exists
                if ($product->image) {
                    $oldImagePath = public_path($product->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $file = $request->file('product_image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                // Create branch-specific directory
                $uploadPath = public_path('uploads/' . $branch->connection_name . '/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file to branch-specific folder
                $file->move($uploadPath, $filename);

                // Store path as: branch_connection/products/filename.jpg
                $path = 'uploads/' . $branch->connection_name . '/products/' . $filename;
            }

            // Handle Company - use branch connection
            // $companyId = $product->company; // Keep existing company by default
            // if (!empty($validate['product_company'])) {
            //     $company = Company::on($branch->connection_name)->firstOrCreate(
            //         ['name' => $validate['product_company']],
            //         ['name' => $validate['product_company'], 'status' => 1]
            //     );
            //     $companyId = $company->id;
            // }

            // Handle Category - use branch connection
            // $categoryId = $product->category_id; // Keep existing category by default
            // if (!empty($validate['product_category'])) {
            //     $category = Category::on($branch->connection_name)->firstOrCreate(
            //         ['name' => $validate['product_category']],
            //         ['name' => $validate['product_category'], 'status' => 1]
            //     );
            //     $categoryId = $category->id;
            // }

            // Handle HSN Code - use branch connection
            // $hsnCodeId = $product->hsn_code_id; // Keep existing HSN code by default
            // if (!empty($validate['hsn_code'])) {
            //     // Prepare GST data as JSON
            //     $gstData = [
            //         'SGST' => (float) ($validate['sgst'] ?? 0),
            //         'CGST' => (float) ($validate['cgst'] ?? 0),
            //         'IGST' => (float) ($validate['igst'] ?? 0),
            //         'CESS' => (float) ($validate['cess'] ?? 0)
            //     ];

            //     $hsnCode = HsnCode::on($branch->connection_name)->first(
            //         [
            //             'hsn_code' => $validate['hsn_code'],
            //             // 'gst' => $validate['gst']
            //         ]
            //     );
            //     // Update GST if HSN code already exists
            //     if (!$hsnCode->wasRecentlyCreated) {
            //         $hsnCode->update(['gst' => json_encode($gstData)]);
            //     }
            //     $hsnCodeId = $hsnCode->id;
            // }

            // Prepare update data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'],
                'image' => $path,
                'search_option' => $validate['search_option'] ?? null,
                'unit_types' => $validate['unit_type'] ?? null,
                'decimal_btn' => isset($validate['decimal_btn']) ? 1 : 0,
                'company' => $validate['company_id'],
                'category_id' => $validate['category_id'],
                'hsn_code_id' => $validate['hsn_code_id'],
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
                'price_1' => $validate['price_1'] ?? 0,
                'price_2' => $validate['price_2'] ?? 0,
                'price_3' => $validate['price_3'] ?? 0,
                'price_4' => $validate['price_4'] ?? 0,
                'price_5' => $validate['price_5'] ?? 0,
                'kg_1' => $validate['kg_1'] ?? 0,
                'kg_2' => $validate['kg_2'] ?? 0,
                'kg_3' => $validate['kg_3'] ?? 0,
                'kg_4' => $validate['kg_4'] ?? 0,
                'kg_5' => $validate['kg_5'] ?? 0,
            ];

            // Update the product using branch connection
            $product->update($data);

            $redirectParams = array_filter([
                'page' => $request->get('page'),
                'search' => $request->get('search')
            ]);

            return redirect()->route('products.index', $redirectParams)->with('success', 'Product updated successfully!');
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
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $product = Product::with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->first();
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->first();
        }

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
        $role = $auth['role'];

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
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $company = Company::where('name', $row[6])->first();

                                if (!$company) {
                                    $companyId = Company::insertGetId([
                                        'name' => $row[6],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $companyId = $company->id;
                                }
                            } else {
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
                        }

                        // Handle Category - create or get existing
                        $categoryId = null;
                        if (!empty($row[7])) {
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $category = Category::where('name', $row[7])->first();

                                if (!$category) {
                                    $categoryId = Category::insertGetId([
                                        'name' => $row[7],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $categoryId = $category->id;
                                }
                            } else {
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
                        }

                        // Handle HSN Code - create or get existing
                        $hsnCodeId = null;
                        if (!empty($row[8])) {
                            $gst = $row[11] ?? '';
                            $shortName = '';
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $hsnCode = HsnCode::where('hsn_code', $row[8])
                                    ->where('gst', $gst)
                                    ->first();

                                if (!$hsnCode) {
                                    $hsnCodeId = HsnCode::insertGetId([
                                        'hsn_code' => $row[8],
                                        'gst' => $gst,
                                        'short_name' => $shortName,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $hsnCodeId = $hsnCode->id;
                                }
                            } else {
                                $hsnCode = HsnCode::on($branch->connection_name)
                                    ->where('hsn_code', $row[8])
                                    ->where('gst', $gst)
                                    ->first();

                                if (!$hsnCode) {
                                    $hsnCodeId = HsnCode::on($branch->connection_name)->insertGetId([
                                        'hsn_code' => $row[8],
                                        'gst' => $gst,
                                        'short_name' => $shortName,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $hsnCodeId = $hsnCode->id;
                                }
                            }
                        }
                        // $data = [
                        //     'product_name' => $row[0] ?? '',
                        //     'barcode' => $row[1] ?? '',
                        //     'search_option' => $row[3] ?? '',
                        //     'unit_types' => $row[4] ?? '',
                        //     'decimal_btn' => $row[5] == 1 ? 1 : 0,
                        //     'company' => $companyId ?? '',
                        //     'category_id' => $categoryId ?? '',
                        //     'hsn_code_id' => $hsnCodeId ?? '',
                        //     // 'sgst' => $row[9] ?? '',
                        //     // 'cgst1' => $row[10] ?? '',
                        //     // 'cgst2' => $row[11] ?? '',
                        //     'cess' => 0,
                        //     'mrp' => $row[13] ?? '',
                        //     'purchase_rate' => $row[14] ?? '',
                        //     'sale_rate_a' => $row[15] ?? 0,
                        //     'sale_rate_b' => 0,
                        //     'sale_rate_c' => 0,
                        //     'sale_online' => 0,
                        //     'converse_carton' => 0,
                        //     'carton_barcode' => null,
                        //     'converse_box' => 1,
                        //     'box_barcode' => null,
                        //     'negative_billing' => null,
                        //     'min_qty' => 0,
                        //     'reorder_qty' => 0,
                        //     'discount' => null,
                        //     'max_discount' => 0,
                        //     'discount_scheme' => null,
                        //     'bonus_use' => 0,
                        // ];
                        $data = [
                            'product_name' => $row[0] ?? '',
                            'barcode' => $row[1] ?? '',
                            'search_option' => $row[3] ?? '',
                            'unit_types' => $row[4] ?? '',
                            'decimal_btn' => $row[5] ?? '',
                            'company' => $companyId ?? '',
                            'category_id' => $categoryId ?? '',
                            'hsn_code_id' => $hsnCodeId ?? '',
                            // 'sgst' => $row[9] ?? '',
                            // 'cgst1' => $row[10] ?? '',
                            // 'cgst2' => $row[11] ?? '',
                            'cess' => 0,
                            'mrp' => $row[13] ?? '',
                            'purchase_rate' => $row[14] ?? '',
                            'sale_rate_a' => $row[15] ?? 0,
                            'sale_rate_b' => $row[16] ?? 0,
                            'sale_rate_c' => $row[17] ?? 0,
                            'sale_online' => $row[18] ?? 0,
                            'converse_carton' => $row[19] ?? 0,
                            'carton_barcode' => null,
                            'converse_box' => $row[20] ?? 1,
                            'box_barcode' => null,
                            'negative_billing' => $row[22] ?? null,
                            'min_qty' => $row[23] ?? 0,
                            'reorder_qty' => $row[24] ?? 0,
                            'discount' => $row[25] ?? null,
                            'max_discount' => $row[26] ?? 0,
                            'discount_scheme' => $row[27] ?? null,
                            'bonus_use' => $row[28] ?? 0,
                        ];

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Product::insert($data);
                        } else {
                            Product::on($branch->connection_name)->insert($data);
                        }
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
            $branch = $auth['branch'];

            // If Super Admin, use `branch` from route or query
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $companies = Company::where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name
                        ];
                    })
                    ->toArray();

                return response()->json(['companies' => $companies]);
            } else {
                // Normal user — get branch from auth
                $companies = Company::on($branch->connection_name)
                    ->where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name
                        ];
                    })
                    ->toArray();

                return response()->json(['companies' => $companies]);
            }

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
            $branch = $auth['branch'];

            // If Super Admin, use `branch` from route or query
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $categories = Category::where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name
                        ];
                    })
                    ->toArray();

                return response()->json(['categories' => $categories]);
            } else {
                $categories = Category::on($branch->connection_name)
                    ->where('name', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name
                        ];
                    })
                    ->toArray();

                return response()->json(['categories' => $categories]);
            }

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
            $branch = $auth['branch'];
            $role = $auth['role'];


            $search = $request->get('search', '');
            if (empty($search)) {
                return response()->json(['hsn_codes' => []]);
            }

            // If Super Admin, use `branch` from route or query
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $hsn_codes = HsnCode::where('hsn_code', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'hsn_code' => $item->hsn_code,
                            'gst' => $item->gst // This will be JSON string or array depending on your cast
                        ];
                    })
                    ->toArray();
            } else {
                $hsn_codes = HsnCode::on($branch->connection_name)
                    ->where('hsn_code', 'LIKE', "%{$search}%") // Assuming company name field is 'name'
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'hsn_code' => $item->hsn_code,
                            'gst' => $item->gst // This will be JSON string or array depending on your cast
                        ];
                    })
                    ->toArray();
            }

            return response()->json(['hsn_codes' => $hsn_codes]);
        } catch (\Throwable $th) {
            return response()->json(['hsn_codes' => []]);
        }
    }

    public function searchProduct(Request $request)
    {
        // Authenticate and get branch configuration
        $authResult = $this->authenticateAndConfigureBranch();

        if (is_array($authResult) && isset($authResult['success']) && !$authResult['success']) {
            return response()->json(['products' => []]);
        }

        $user = $authResult['user'];
        $branch = $authResult['branch'];
        $role = $authResult['role'];

        try {
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                // For Super Admin, search in the default connection or specify a branch
                $search = $request->get('search', '');

                $product = Product::with('hsnCode')->where('barcode', trim($search))->first();

                if ($product) {
                    return response()->json([
                        'success' => true,
                        'products' => [$product],
                        'exact_match' => true,
                        'auto_select' => true
                    ]);
                }

                $products = Product::with('hsnCode')->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%")
                    ->limit(10)
                    ->get();
            } else {
                // Get branch connection for regular users
                $branchConnection = $branch->connection_name;

                $search = $request->get('search', '');
                $product = Product::on($branchConnection)->with('hsnCode')->where('barcode', trim($search))->first();

                if ($product) {
                    return response()->json([
                        'success' => true,
                        'products' => [$product],
                        'exact_match' => true,
                        'auto_select' => true
                    ]);
                }

                $products = Product::on($branchConnection)
                    ->with('hsnCode')
                    ->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%")
                    ->limit(10)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'products' => $products,
                'exact_match' => false,
                'auto_select' => false
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'products' => [],
                'message' => 'Error searching products: ' . $e->getMessage()
            ]);
        }
    }
}
