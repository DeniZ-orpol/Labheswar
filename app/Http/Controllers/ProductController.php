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
    public function index()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $products = Product::on($branch->connection_name)
            ->with(['category', 'hsnCode', 'pCompany'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('products.index', compact('products'));
    }
    // public function index()
    // {
    //     // Check if user is logged in as branch
    //     // if (session('user_type') !== 'branch' || !session('branch_connection')) {
    //     //     return redirect()->route('login')->with('error', 'Please login as branch user.');
    //     // }

    //     $branchConnection = session('branch_connection');

    //     // Get product with pagination first
    //     $products = Product::forDatabase($branchConnection)->paginate(10);

    //     // Load relationships using trait method to get related data for product(For pagination only)
    //     if ($products->isNotEmpty()) {
    //         $productModel = new Product();
    //         $productModel->setDynamicTable($branchConnection);
    //         $productModel->loadRelationsForPaginator($products, ['category', 'pCompany', 'hsnCode']);
    //     }

    //     return view('products.index', compact('products'));
    // }

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
    public function store(Request $request)
    {
        try {

            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
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
                $hsnCode = HsnCode::on($branch->connection_name)->firstOrCreate(
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
                // 'gst_active' => isset($validate['gst_active']) ? 1 : 0,
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
    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
            ->where('id', $id)
            ->firstOrFail();

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
            ->where('id', $id)
            ->firstOrFail();

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
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
                'sgst' => 'nullable|numeric|min:0',
                'cgst1' => 'nullable|numeric|min:0',
                'cgst2' => 'nullable|numeric|min:0',
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
                $hsnCode = HsnCode::on($branch->connection_name)->firstOrCreate(
                    ['hsn_code' => $validate['hsn_code']],
                    ['hsn_code' => $validate['hsn_code']]
                );
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
                'cgst1' => $validate['cgst1'] ?? 0,
                'cgst2' => $validate['cgst2'] ?? 0,
                'cess' => $validate['cess'] ?? 0,
                'mrp' => $validate['mrp'] ?? 0,
                'purchase_rate' => $validate['purchase_rate'] ?? 0,
                'sale_rate_a' => $validate['sale_rate_a'] ?? 0,
                'sale_rate_b' => $validate['sale_rate_b'] ?? 0,
                'sale_rate_c' => $validate['sale_rate_c'] ?? 0,
                'sale_online' => isset($validate['sale_online']) ? 1 : 0,
                // 'gst_active' => isset($validate['gst_active']) ? 1 : 0,
                'converse_carton' => $validate['converse_carton'] ?? 0,
                'converse_box' => $validate['converse_boc'] ?? 0,
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

    private function importExcel($file, $branchDb)
    {
        if ($xlsx = SimpleXLSX::parse($file->getRealPath())) {

            $rows = $xlsx->rows();
            array_shift($rows);

            foreach ($rows as $row) {
                if (!empty($row[0]) || !empty($row[1])) { // Check if name exists
                    $data = [
                        'product_barcode' => $row[0] ?? '',
                        'product_name' => $row[1],
                        'price' => $row[11] ?? 0,
                        'min_quantity' => $row[19] ?? 0,
                        'category' => $row[5] ?? '',
                        'unit_type' => $row[3] ?? '',
                    ];
                    dd($data);
                    DB::connection($branchDb)->table('products')->insert($data);
                }
            }

        }

    }
}
