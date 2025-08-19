<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\HsnCode;
use App\Models\Product;
use App\Models\Packaging;
use App\Models\Inventory;
use App\Traits\BranchAuthTrait;
use Exception;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Illuminate\Support\Facades\Response;

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
        $categoryId = $request->get('category_id');
        $companyId = $request->get('company_id');
        $hsnCodeId = $request->get('hsn_code_id');

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $query = Product::with(['category', 'hsnCode', 'pCompany']);
            $categories = Category::all();
            $companies = Company::all();
            $hsnCodes = HsnCode::all();
        } else {
            $query = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany']);
            $categories = Category::on($branch->connection_name)->get();
            $companies = Company::on($branch->connection_name)->get();
            $hsnCodes = HsnCode::on($branch->connection_name)->get();
        }

        // Apply filters if present
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($companyId) {
            $query->where('company', $companyId);
        }
        if ($hsnCodeId) {
            $query->where('hsn_code_id', $hsnCodeId);
        }

        if ($search) {
            // Perform LIKE search on product_name, barcode, and search_option
            $likeQuery = clone $query;
            $likeQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('barcode', 'LIKE', '%' . $search . '%')
                  ->orWhere('search_option', 'LIKE', '%' . $search . '%');
            });
            $likeResults = $likeQuery->get();

            // Fetch all products to filter acronym matches on product_name
            $allProducts = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Product::with(['category', 'hsnCode', 'pCompany'])->get()
                : Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])->get();

            $acronymMatches = $allProducts->filter(function ($prod) use ($search) {
                $words = preg_split('/\s+/', strtolower($prod->product_name));
                $initials = implode('', array_map(function ($word) {
                    return $word[0] ?? '';
                }, $words));
                return stripos($initials, strtolower($search)) !== false;
            });

            // Merge and remove duplicates
            $merged = $likeResults->merge($acronymMatches)->unique('id');

            // Paginate merged results manually
            $page = $request->get('page', 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $paginated = $merged->slice($offset, $perPage)->values();

            // Create LengthAwarePaginator instance
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginated,
                $merged->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $products = $query->orderByDesc('id')->paginate(20);
        }

        // Preserve search and filter parameters in pagination links
        $products->appends($request->only(['search', 'category_id', 'company_id', 'hsn_code_id']));

        if ($request->ajax()) {
            return view('products.product-list', compact('products'))->render();
        }

        return view('products.index', compact('products', 'search', 'categories', 'companies', 'hsnCodes', 'categoryId', 'companyId', 'hsnCodeId'));
    }

    /* public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $companyId = $request->get('company_id');
        $hsnCodeId = $request->get('hsn_code_id');

        $perPage = 20;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Initialize empty collections
        $products = collect();
        $categories = collect();
        $companies = collect();
        $hsnCodes = collect();

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            // Get all branches with database connection names
            $branches = Branch::all();

            foreach ($branches as $b) {
                configureBranchConnection($b); 
                $branchProducts = Product::on($b->connection_name)
                    ->with(['category', 'hsnCode', 'pCompany'])
                    ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                    ->when($companyId, fn($q) => $q->where('company', $companyId))
                    ->when($hsnCodeId, fn($q) => $q->where('hsn_code_id', $hsnCodeId))
                    ->get();

                $products = $products->merge($branchProducts);

                // Optional: Collect filter data only once or from first branch
                $categories = $categories->merge(Category::on($b->connection_name)->get());
                $companies = $companies->merge(Company::on($b->connection_name)->get());
                $hsnCodes = $hsnCodes->merge(HsnCode::on($b->connection_name)->get());
            }

            // Remove duplicates from filters
            $categories = $categories->unique('id');
            $companies = $companies->unique('id');
            $hsnCodes = $hsnCodes->unique('id');
        } else {
            // Branch user logic
            $query = Product::on($branch->connection_name)
                ->with(['category', 'hsnCode', 'pCompany'])
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->when($companyId, fn($q) => $q->where('company', $companyId))
                ->when($hsnCodeId, fn($q) => $q->where('hsn_code_id', $hsnCodeId));

            $products = $query->get();
            $categories = Category::on($branch->connection_name)->get();
            $companies = Company::on($branch->connection_name)->get();
            $hsnCodes = HsnCode::on($branch->connection_name)->get();
        }

        // Handle search
        if ($search) {
            $products = $products->filter(function ($prod) use ($search) {
                $search = strtolower($search);
                $name = strtolower($prod->product_name);
                $barcode = strtolower($prod->barcode ?? '');
                $option = strtolower($prod->search_option ?? '');

                // Acronym logic
                $words = preg_split('/\s+/', $name);
                $initials = implode('', array_map(fn($word) => $word[0] ?? '', $words));
                $isAcronymMatch = stripos($initials, $search) !== false;

                return str_contains($name, $search) ||
                    str_contains($barcode, $search) ||
                    str_contains($option, $search) ||
                    $isAcronymMatch;
            });
        }

        // Paginate merged results
        $paginated = $products->sortByDesc('id')->values();
        $paginated = $paginated->slice($offset, $perPage)->values();

        $productsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $products->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $productsPaginator->appends($request->only(['search', 'category_id', 'company_id', 'hsn_code_id']));

        // Handle AJAX request
        if ($request->ajax()) {
            return view('products.product-list', ['products' => $productsPaginator])->render();
        }

        return view('products.index', [
            'products' => $productsPaginator,
            'search' => $search,
            'categories' => $categories,
            'companies' => $companies,
            'hsnCodes' => $hsnCodes,
            'categoryId' => $categoryId,
            'companyId' => $companyId,
            'hsnCodeId' => $hsnCodeId,
        ]);
    } */

    /**
     * Save product with given data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveProduct(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // dd($request->all());

            $validate = $request->validate([
                'product_name' => 'required|string|max:255',
                'product_barcode' => 'nullable|string|max:255',
                'unit_type' => 'required|string',
                'hsn_code_id' => 'nullable|integer',
                'mrp' => 'nullable|numeric|min:0',
                'purchase_rate' => 'nullable|numeric|min:0',
                'sale_rate_a' => 'nullable|numeric|min:0',
                'sale_rate_b' => 'nullable|numeric|min:0',
                'sale_rate_c' => 'nullable|numeric|min:0',
                'negative_billing' => 'nullable',
                'converse_carton' => 'nullable',
                'carton_barcode' => 'nullable',
                'converse_box' => 'nullable',
                'box_barcode' => 'nullable'
            ]);

            // Prepare product data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'] ?? '',
                'unit_types' => $validate['unit_type'],
                'hsn_code_id' => $validate['hsn_code_id'] ?? null,
                'mrp' => $validate['mrp'] ?? 0,
                'purchase_rate' => $validate['purchase_rate'] ?? 0,
                'sale_rate_a' => $validate['sale_rate_a'] ?? 0,
                'sale_rate_b' => $validate['sale_rate_b'] ?? 0,
                'sale_rate_c' => $validate['sale_rate_c'] ?? 0,
                'converse_carton' => $validate['converse_carton'] ?? null,
                'carton_barcode' => $validate['carton_barcode'] ?? null,
                'converse_box' => $validate['converse_box'] ?? null,
                'box_barcode' => $validate['box_barcode'] ?? null,
                'negative_billing' => $validate['negative_billing'] ?? 'YES',
            ];

            // Create the product using branch connection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $product = Product::create($data);
            } else {
                $product = Product::on($branch->connection_name)->create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product saved successfully',
                'data' => $product
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving product: ' . $ex->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $companyId = $request->get('company_id');
        $hsnCodeId = $request->get('hsn_code_id');

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $query = Product::with(['category', 'hsnCode', 'pCompany']);
        } else {
            $query = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany']);
        }

        // Apply filters if present
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($companyId) {
            $query->where('company', $companyId);
        }
        if ($hsnCodeId) {
            $query->where('hsn_code_id', $hsnCodeId);
        }

        if ($search) {
            // Perform LIKE search on product_name, barcode, and search_option
            $likeQuery = clone $query;
            $likeQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('barcode', 'LIKE', '%' . $search . '%')
                    ->orWhere('search_option', 'LIKE', '%' . $search . '%');
            });
            $likeResults = $likeQuery->get();

            // Fetch all products to filter acronym matches on product_name
            $allProducts = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Product::with(['category', 'hsnCode', 'pCompany'])->get()
                : Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])->get();

            $acronymMatches = $allProducts->filter(function ($prod) use ($search) {
                $words = preg_split('/\s+/', strtolower($prod->product_name));
                $initials = implode('', array_map(function ($word) {
                    return $word[0] ?? '';
                }, $words));
                return stripos($initials, strtolower($search)) !== false;
            });

            // Merge and remove duplicates
            $merged = $likeResults->merge($acronymMatches)->unique('id');
        } else {
            $merged = $query->orderByDesc('id')->get();
        }

        $columns = [
            'Product Name',
            'Barcode',
            'Search Option',
            'Unit Type',
            'Decimal Btn',
            'Company',
            'Category',
            'HSN Code',
            'GST',
            'HSN Short Name',
            'CESS',
            'MRP',
            'Purchase rate',
            'Sale Rate a',
            'Sale Rate b',
            'Sale Rate c',
            'Sale Online',
            'Converse Carton',
            'Carton Barcode',
            'Converse Box',
            'Box Barcode',
            'Negative Billing',
            'Min Qty',
            'Reorder Qty',
            'Discount',
            'Max Discount',
            'Discount Scheme',
            'Bonus Use',
        ];

        $data = [];
        $data[] = $columns;

        foreach ($merged as $product) {
            $data[] = [
                $product->product_name,
                $product->barcode,
                $product->search_option,
                $product->unit_types,
                $product->decimal_btn,
                $product->pCompany->name ?? '',
                $product->category->name ?? '',
                $product->hsnCode->hsn_code ?? '',
                $product->hsnCode->gst ?? '',
                $product->hsnCode->short_name ?? '',
                $product->cess ?? 0,
                $product->mrp ?? '',
                $product->purchase_rate ?? "",
                $product->sale_rate_a,
                $product->sale_rate_b,
                $product->sale_rate_c,
                $product->sale_online ?? '',
                $product->converse_carton ?? '',
                $product->carton_barcode ?? '',
                $product->converse_box ?? '',
                $product->box_barcode ?? '',
                $product->negative_billing ?? '',
                $product->min_qty ?? '',
                $product->reorder_qty ?? '',
                $product->discount ?? '',
                $product->max_discount ?? 0,
                $product->discount_scheme ?? '',
                $product->bonus_use ? ($product->bonus_use == 1 ? 'yes' : 'no') : 'no',
            ];
        }

        $filename = 'products_export.xlsx';

        $xlsx = SimpleXLSXGen::fromArray($data);
        ob_start();
        $xlsx->saveAs('php://output');
        $content = ob_get_clean();

        return Response::make($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'public',
        ]);
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
        // dd($request->all());
        try {

            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            $validate = $request->validate([
                'product_barcode' => 'nullable|string|max:255',
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
                'product_type' => 'required|nullable|string',
                'weight_to' => 'nullable',
                'weight_from' => 'nullable',
                'decimal_btn' => 'nullable',
                'sale_online' => 'nullable',
                // 'price_1' => 'nullable|numeric|min:0',
                // 'price_2' => 'nullable|numeric|min:0',
                // 'price_3' => 'nullable|numeric|min:0',
                // 'price_4' => 'nullable|numeric|min:0',
                // 'price_5' => 'nullable|numeric|min:0',
                // 'kg_1' => 'nullable|numeric|min:0',
                // 'kg_2' => 'nullable|numeric|min:0',
                // 'kg_3' => 'nullable|numeric|min:0',
                // 'kg_4' => 'nullable|numeric|min:0',
                // 'kg_5' => 'nullable|numeric|min:0',
                'loose_btn' => 'nullable',
                'loose_weight' => 'nullable',
                'loose_price' => 'nullable',
                'auto_generate_variants' => 'nullable',
                'packaging_btn' => 'nullable',
                'packaging' => 'nullable',
                'packaging_id' => 'nullable',
                'auto_variants_in_weight_btn' => 'nullable|string',
                'auto_variants_in_weight' => 'nullable|array',
                'auto_variants_in_weight.*' => 'nullable|numeric',
                'auto_variants_in_amount_btn' => 'nullable|string',
                'auto_variants_in_amount' => 'nullable|array',
                'auto_variants_in_amount.*' => 'nullable|numeric',
                'custom_price_btn' => 'nullable|string',
                'custom_price' => 'nullable|array',
                'custom_price.*.condition' => 'nullable|string',
                'custom_price.*.weight' => 'nullable|numeric',
                'custom_price.*.price' => 'nullable|numeric',
                // validation for custom variant creation
                'custom_variant_btn' => 'nullable|string', // checkbox
                'variant_name' => ['nullable', 'array'],
                'variant_name.*' => ['nullable', 'string'],
                'variant_price' => ['nullable', 'array'],
                'variant_price.*' => ['nullable', 'numeric'],
                'variant_barcode' => ['nullable', 'array'],
                'variant_barcode.*' => ['nullable', 'string'],
            ]);


            // Upload product image (optional)
            $path = null;
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                // Create branch-specific directory
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $uploadPath = public_path('uploads/sweetler/products');
                } else {
                    $uploadPath = public_path('uploads/' . $branch->connection_name . '/products');
                }
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file to branch-specific folder
                $file->move($uploadPath, $filename);

                // Store path as: branch_connection/products/filename.jpg
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $path = 'uploads/sweetler/products/' . $filename;
                } else {
                    $path = 'uploads/' . $branch->connection_name . '/products/' . $filename;
                }
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

            $price1 = $price2 = $price3 = $price4 = 0;
            $weight1 = $weight2 = $weight3 = $weight4 = null;

            // Get unit type
            $unitType = strtoupper($validate['unit_type'] ?? '');

            // Prepare product data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'] ?? '',
                'image' => $path, // nullable image
                'search_option' => $validate['search_option'] ?? null,
                'unit_types' => $validate['unit_type'] ?? null,
                'decimal_btn' => isset($validate['decimal_btn']) && $validate['decimal_btn'] ? 1 : 0,
                'company' => $companyId,
                'category_id' => $categoryId,
                'hsn_code_id' => $validate['hsn_code_id'] ?? null,
                // 'sgst' => $validate['sgst'] ?? 0,
                // 'cgst1' => $validate['cgst'] ?? 0,
                // 'cgst2' => $validate['igst'] ?? 0, //igst
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
                'product_type' => $validate['product_type'] ?? null,
                'weight_to' => $validate['weight_to'] ?? null,
                'weight_from' => $validate['weight_from'] ?? null,
                'auto_variants_in_weight_btn' => isset($validate['auto_variants_in_weight_btn']) && $validate['auto_variants_in_weight_btn'] ? 'yes' : 'no',
                'auto_variants_in_weight' => isset($validate['auto_variants_in_weight']) && $validate['auto_variants_in_weight'] ? $validate['auto_variants_in_weight'] : [],
                'auto_variants_in_amount_btn' => isset($validate['auto_variants_in_amount_btn']) && $validate['auto_variants_in_amount_btn'] ? 'yes' : 'no',
                'auto_variants_in_amount' => isset($validate['auto_variants_in_amount']) && $validate['auto_variants_in_amount'] ? $validate['auto_variants_in_amount'] : [],
                'custom_price_btn' => isset($validate['custom_price_btn']) && $validate['custom_price_btn'] ? 'yes' : 'no',
                'custom_price' => isset($validate['custom_price_btn']) && $validate['custom_price_btn'] ? ($validate['custom_price'] ?? []) : [],
                // 'price_1' => $price1 ?? 0,
                // 'price_2' => $price2 ?? 0,
                // 'price_3' => $price3 ?? 0,
                // 'price_4' => $price4 ?? 0,
                // 'kg_1' => $weight1 ?? null,
                // 'kg_2' => $weight2 ?? null,
                // 'kg_3' => $weight3 ?? null,
                // 'kg_4' => $weight4 ?? null,
                'use_static_variant' => isset($validate['auto_generate_variants']) ? 'yes' : 'no',
                'packaging_btn' => isset($validate['packaging_btn']) ? $validate['packaging_btn'] : 0,
                'packaging' => isset($validate['packaging_id']) ? $validate['packaging_id'] : null,
                'custom_variant_btn' => isset($validate['custom_variant_btn']) && $validate['custom_variant_btn'] ? 'yes' : 'no',
                'loose_below_weight' => $validate['loose_weight'] ?? null,
                'loose_below_price' => $validate['loose_price'] ?? null,
            ];

            // Create the product using branch connection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $product = Product::create($data);
            } else {
                $product = Product::on($branch->connection_name)->create($data);
            }

            if (isset($validate['custom_variant_btn'])) {
                $names = $validate['variant_name'] ?? [];
                $prices = $validate['variant_price'] ?? [];
                $barcodes = $validate['variant_barcode'] ?? [];

                foreach ($names as $index => $name) {
                    if (!empty($name)) {
                        $variantData = $product->replicate()->toArray(); // clone base fields

                        // Override necessary fields
                        $variantData['product_name'] = $variantData['product_name'] . " " . $name;
                        $variantData['barcode'] = $barcodes[$index] ?? '';
                        $variantData['sale_rate_a'] = $prices[$index] ?? 0;
                        $variantData['reference_id'] = $product->id;
                        $variantData['is_variant'] = 'yes'; // not a parent itself
                        $variantData['negative_billing'] = "YES";
                        $variantData['decimal_btn'] = 0;

                        unset($variantData['id']); // to avoid primary key conflict
                        unset($variantData['custom_variant_btn']);

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Product::create($variantData);
                        } else {
                            Product::on($branch->connection_name)->create($variantData);
                        }
                    }
                }
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
            $product = Product::with(['category', 'hsnCode', 'pCompany', 'decimalPackaging'])
                ->where('id', $id)
                ->firstOrFail();
            $customVariants = Product::where('reference_id', $product->id)->get(['id', 'product_name', 'barcode', 'sale_rate_a']);
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany', 'decimalPackaging'])
                ->where('id', $id)
                ->firstOrFail();
            $customVariants = Product::on($branch->connection_name)->where('reference_id', $product->id)->get(['id', 'product_name', 'barcode', 'sale_rate_a']);
        }

        // Pass page and search parameters to the view
        $page = $request->get('page');
        $search = $request->get('search');

        return view('products.edit', compact('product', 'page', 'search', 'customVariants'));
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
                'product_barcode' => 'nullable|string|max:255',
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
                'product_type' => 'required|nullable|string',
                'weight_to' => 'nullable',
                'weight_from' => 'nullable',
                'decimal_btn' => 'nullable',
                'sale_online' => 'nullable',
                // 'price_1' => 'nullable|numeric|min:0',
                // 'price_2' => 'nullable|numeric|min:0',
                // 'price_3' => 'nullable|numeric|min:0',
                // 'price_4' => 'nullable|numeric|min:0',
                // 'price_5' => 'nullable|numeric|min:0',
                // 'kg_1' => 'nullable|numeric|min:0',
                // 'kg_2' => 'nullable|numeric|min:0',
                // 'kg_3' => 'nullable|numeric|min:0',
                // 'kg_4' => 'nullable|numeric|min:0',
                // 'kg_5' => 'nullable|numeric|min:0',
                'loose_btn' => 'nullable',
                'loose_weight' => 'nullable',
                'loose_price' => 'nullable',
                'auto_generate_variants' => 'nullable',
                'packaging_btn' => 'nullable',
                'packaging' => 'nullable',
                'packaging_id' => 'nullable',
                'auto_variants_in_weight_btn' => 'nullable|string',
                'auto_variants_in_weight' => 'nullable|array',
                'auto_variants_in_weight.*' => 'nullable|numeric',
                'auto_variants_in_amount_btn' => 'nullable|string',
                'auto_variants_in_amount' => 'nullable|array',
                'auto_variants_in_amount.*' => 'nullable|numeric',
                'custom_price_btn' => 'nullable|string',
                'custom_price' => 'nullable|array',
                'custom_price.*.condition' => 'nullable|string',
                'custom_price.*.weight' => 'nullable|numeric',
                'custom_price.*.price' => 'nullable|numeric',

                // validation for custom variant creation
                'custom_variant_btn' => 'nullable', // checkbox
                'variant_name' => ['nullable', 'array'],
                'variant_name.*' => ['nullable', 'string'],
                'variant_price' => ['nullable', 'array'],
                'variant_price.*' => ['nullable', 'numeric'],
                'variant_barcode' => ['nullable', 'array'],
                'variant_barcode.*' => ['nullable', 'string'],
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
            if ($request->has('remove_product_image') && $request->remove_product_image == 1) {
                if ($product->image) {
                    $oldImagePath = public_path($product->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $path = null;
            }
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
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $uploadPath = public_path('uploads/sweetler/products');
                } else {
                    $uploadPath = public_path('uploads/' . $branch->connection_name . '/products');
                }
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file to branch-specific folder
                $file->move($uploadPath, $filename);

                // Store path as: branch_connection/products/filename.jpg
                if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                    $path = 'uploads/sweetler/products/' . $filename;
                } else {
                    $path = 'uploads/' . $branch->connection_name . '/products/' . $filename;
                }
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

            $price1 = $price2 = $price3 = $price4 = 0;
            $weight1 = $weight2 = $weight3 = $weight4 = null;

            // Get unit type
            $unitType = strtoupper($validate['unit_type'] ?? '');


            // Prepare update data
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'] ?? '',
                'image' => $path,
                'search_option' => $validate['search_option'] ?? null,
                'unit_types' => $validate['unit_type'] ?? null,
                'decimal_btn' => isset($validate['decimal_btn']) && $validate['decimal_btn'] ? 1 : 0,
                'company' => $validate['company_id'],
                'category_id' => $validate['category_id'],
                'hsn_code_id' => $validate['hsn_code_id'],
                // 'sgst' => $validate['sgst'] ?? 0,
                // 'cgst1' => $validate['cgst'] ?? 0,
                // 'cgst2' => $validate['igst'] ?? 0,
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
                'product_type' => $validate['product_type'] ?? null,
                'weight_to' => $validate['weight_to'] ?? null,
                'weight_from' => $validate['weight_from'] ?? null,
                'updated_by' => session('branch_user_id'), // Track who updated the product
                // 'price_1' => $price1 ?? 0,
                // 'price_2' => $price2 ?? 0,
                // 'price_3' => $price3 ?? 0,
                // 'price_4' => $price4 ?? 0,
                // 'kg_1' => $weight1 ?? null,
                // 'kg_2' => $weight2 ?? null,
                // 'kg_3' => $weight3 ?? null,
                // 'kg_4' => $weight4 ?? null,
                'auto_variants_in_weight_btn' => isset($validate['auto_variants_in_weight_btn']) && $validate['auto_variants_in_weight_btn'] ? 'yes' : 'no',
                'auto_variants_in_weight' => isset($validate['auto_variants_in_weight']) && $validate['auto_variants_in_weight'] ? $validate['auto_variants_in_weight'] : [],
                'auto_variants_in_amount_btn' => isset($validate['auto_variants_in_amount_btn']) && $validate['auto_variants_in_amount_btn'] ? 'yes' : 'no',
                'auto_variants_in_amount' => isset($validate['auto_variants_in_amount']) && $validate['auto_variants_in_amount'] ? $validate['auto_variants_in_amount'] : [],
                'custom_price_btn' => isset($validate['custom_price_btn']) && $validate['custom_price_btn'] ? 'yes' : 'no',
                'custom_price' => isset($validate['custom_price_btn']) && $validate['custom_price_btn'] ? ($validate['custom_price'] ?? []) : [],
                'use_static_variant' => isset($validate['auto_generate_variants']) ? 'yes' : 'no',
                'packaging_btn' => isset($validate['packaging_btn']) ? $validate['packaging_btn'] : 0,
                'packaging' => isset($validate['packaging_id']) ? $validate['packaging_id'] : null,
                'custom_variant_btn' => isset($validate['custom_variant_btn']) && $validate['custom_variant_btn'] ? 'yes' : 'no',
                'loose_below_weight' => $validate['loose_weight'] ?? null,
                'loose_below_price' => $validate['loose_price'] ?? null,
            ];

            // Update the product using branch connection
            $product->update($data);

            $redirectParams = array_filter([
                'page' => $request->get('page'),
                'search' => $request->get('search')
            ]);

            if (isset($validate['custom_variant_btn'])) {
                if ($validate['custom_variant_btn']) {
                    $names = $validate['variant_name'] ?? [];
                    $prices = $validate['variant_price'] ?? [];
                    $barcodes = $validate['variant_barcode'] ?? [];
                    $variantIds = $request->input('variant_id', []);

                    // Fetch all existing variants for this product
                    $existingVariants = Product::on($branch->connection_name)
                        ->where('reference_id', $product->id)
                        ->get()
                        ->keyBy('id');

                    $processedIds = [];

                    foreach ($names as $index => $name) {
                        $variantId = $variantIds[$index] ?? null;

                        $variantData = [
                            'product_name' => $name ?? '',
                            'barcode' => $barcodes[$index] ?? '',
                            'sale_rate_a' => $prices[$index] ?? 0,
                            'reference_id' => $product->id,
                            'is_variant' => 'yes',
                            'negative_billing' => 'YES',
                            'decimal_btn' => 0,
                            'updated_by' => session('branch_user_id'),
                        ];

                        if (!empty($variantId) && $existingVariants->has($variantId)) {
                            // Update existing variant
                            $variant = $existingVariants[$variantId];
                            $variant->update($variantData);
                            $processedIds[] = $variantId;
                        } elseif (!empty($name)) {
                            $variantData = $product->replicate()->toArray();

                            $variantData['product_name'] = trim($variantData['product_name'] . ' ' . $name);
                            $variantData['barcode'] = $barcodes[$index] ?? '';
                            $variantData['sale_rate_a'] = $prices[$index] ?? 0;
                            $variantData['reference_id'] = $product->id;
                            $variantData['is_variant'] = 'yes';
                            $variantData['negative_billing'] = "YES";
                            $variantData['decimal_btn'] = 0;
                            $variantData['updated_by'] = session('branch_user_id');

                            // Remove fields that should not be duplicated
                            unset($variantData['id']);
                            unset($variantData['custom_variant_btn']);
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                Product::create($variantData);
                            } else {
                                Product::on($branch->connection_name)->create($variantData);
                            }
                        }
                    }

                    // Delete variants not submitted
                    $toDelete = $existingVariants->keys()->diff($processedIds);
                    if ($toDelete->isNotEmpty()) {
                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Product::whereIn('id', $toDelete)
                                ->delete();
                        } else {
                            Product::on($branch->connection_name)
                                ->whereIn('id', $toDelete)
                                ->delete();
                        }
                    }
                } else {
                    if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                        Product::where('reference_id', $product->id)->delete();
                    } else {
                        Product::on($branch->connection_name)
                            ->where('reference_id', $product->id)
                            ->delete();
                    }
                }
            }


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
            // Delete related inventory records first
            \App\Models\Inventory::where('product_id', $id)->delete();
        } else {
            $product = Product::on($branch->connection_name)->with(['category', 'hsnCode', 'pCompany'])
                ->where('id', $id)
                ->first();
            // Delete related inventory records first on branch connection
            \App\Models\Inventory::on($branch->connection_name)->where('product_id', $id)->delete();
        }

        // Delete product image if exists
        if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
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
                        if (!empty($row[5])) {
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $company = Company::where('name', $row[5])->first();

                                if (!$company) {
                                    $companyId = Company::insertGetId([
                                        'name' => $row[5],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $companyId = $company->id;
                                }
                            } else {
                                $company = Company::on($branch->connection_name)
                                    ->where('name', $row[5])
                                    ->first();

                                if (!$company) {
                                    $companyId = Company::on($branch->connection_name)->insertGetId([
                                        'name' => $row[5],
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
                        if (!empty($row[6])) {
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $category = Category::where('name', $row[6])->first();

                                if (!$category) {
                                    $categoryId = Category::insertGetId([
                                        'name' => $row[6],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                } else {
                                    $categoryId = $category->id;
                                }
                            } else {
                                $category = Category::on($branch->connection_name)
                                    ->where('name', $row[6])
                                    ->first();

                                if (!$category) {
                                    $categoryId = Category::on($branch->connection_name)->insertGetId([
                                        'name' => $row[6],
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
                        if (!empty($row[7])) {
                            $gst = $row[8] ?? '';
                            $shortName = $row[9] ?? '';
                            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                                $hsnCode = HsnCode::where('hsn_code', $row[7])
                                    ->where('gst', $gst)
                                    ->first();

                                if (!$hsnCode) {
                                    $hsnCodeId = HsnCode::insertGetId([
                                        'hsn_code' => $row[7],
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
                                    ->where('hsn_code', $row[7])
                                    ->where('gst', $gst)
                                    ->first();

                                if (!$hsnCode) {
                                    $hsnCodeId = HsnCode::on($branch->connection_name)->insertGetId([
                                        'hsn_code' => $row[7],
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
                            'search_option' => $row[2] ?? '',
                            'unit_types' => $row[3] ?? '',
                            'decimal_btn' => $row[4] ?? '',
                            'company' => $companyId ?? null,
                            'category_id' => $categoryId ?? null,
                            'hsn_code_id' => $hsnCodeId ?? null,
                            // 'sgst' => $row[9] ?? '',
                            // 'cgst1' => $row[10] ?? '',
                            // 'cgst2' => $row[11] ?? '',
                            'cess' => $row[10] ?? 0,
                            'mrp' => $row[11] ?? 0,
                            'purchase_rate' => $row[12] != "" ? $row[12] : 0,
                            'sale_rate_a' => $row[13] != "" ? $row[13] : null,
                            'sale_rate_b' => $row[14] != "" ? $row[14] : null,
                            'sale_rate_c' => $row[15] != "" ? $row[15] : null,
                            'sale_online' => $row[16] != "" ?? 0,
                            'converse_carton' => $row[17] ?? null,
                            'carton_barcode' => $row[18] ?? null,
                            'converse_box' => $row[19] ?? null,
                            'box_barcode' => $row[20] ?? null,
                            'negative_billing' => $row[21] ?? "NO",
                            'min_qty' => $row[22] ?? 0,
                            'reorder_qty' => $row[23] ?? 0,
                            'discount' => $row[24] ?? "not_applicable",
                            'max_discount' => $row[25] ?? 0,
                            'discount_scheme' => $row[26] ?? null,
                            'bonus_use' => $row[27] != "" ? ($row[27] == "yes" ? 1 : 0) : "0",
                        ];

                        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                            Product::insert($data);
                        } else {
                            Product::on($branch->connection_name)->insert($data);
                        }
                    }
                }
            }

            return back()->with('success', 'Product created successfully!');

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
        $authResult = $this->authenticateAndConfigureBranch();

        if (is_array($authResult) && isset($authResult['success']) && !$authResult['success']) {
            return response()->json(['products' => []]);
        }

        $user = $authResult['user'];
        $branch = $authResult['branch'];
        $role = $authResult['role'];

        try {
            $search = $request->get('search', '');
            $type = $request->get('type', ''); // Default to 'product'

            // Define the base query builder function to reuse
            $getQuery = function ($connection = null) use ($search, $type) {
                $query = $connection
                    ? Product::on($connection)->with('hsnCode')->withSum('inventories as available_qty', 'quantity')
                    : Product::with('hsnCode')->withSum('inventories as available_qty', 'quantity');

                if ($type !== '') {
                    // Only fetch categories with the specified type
                    $query->where('product_type', $type);
                } else {
                    // Fetch categories where type is either 'product' or null
                    $query->where(function ($q) {
                        $q->whereNull('product_type')->orWhere('product_type', 'product');
                    });
                }
                return $query;
            };

            // First try to match exact barcode
            $barcodeQuery = $getQuery(strtoupper($role->role_name) !== 'SUPER ADMIN' ? $branch->connection_name : null);
            $product = $barcodeQuery->where('barcode', trim($search))->first();

            if ($product) {
                return response()->json([
                    'success' => true,
                    'products' => [$product],
                    'exact_match' => true,
                    'auto_select' => true
                ]);
            }

            // Then search by name or partial barcode
            $productQuery = $getQuery(strtoupper($role->role_name) !== 'SUPER ADMIN' ? $branch->connection_name : null);
            $products = $productQuery
                ->where(function ($q) use ($search) {
                    $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get();

            // Word-based acronym search
            $allProductsQuery = $getQuery(strtoupper($role->role_name) !== 'SUPER ADMIN' ? $branch->connection_name : null);
            $allProducts = $allProductsQuery->get();

            $acronymMatches = $allProducts->filter(function ($prod) use ($search) {
                $words = preg_split('/\s+/', strtolower($prod->product_name));
                $initials = implode('', array_map(function ($word) {
                    return $word[0] ?? '';
                }, $words));
                return stripos($initials, strtolower($search)) !== false;
            })->take(10);

            // Merge results and normalize quantities
            $finalResults = $acronymMatches->merge($products)->unique('id')->take(10)->map(function ($product) {
                $product->available_qty = $product->available_qty ?? 0;
                return $product;
            });

            return response()->json([
                'success' => true,
                'products' => $finalResults->values(),
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

    public function searchPackaging(Request $request)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return response()->json(['packagings' => []]);
        }

        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // Define query builder with connection logic
            $query = strtoupper($role->role_name) === 'SUPER ADMIN'
                ? Packaging::query()
                : Packaging::on($branch->connection_name);

            $packagings = $query->where('group', 'LIKE', "%{$search}%")
                ->select('group_id', 'group') // Only selecting group_id and name
                ->groupBy('group_id', 'group')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->group_id,
                        'name' => $item->group
                    ];
                })
                ->toArray();

            return response()->json(['packagings' => $packagings]);

        } catch (\Throwable $th) {
            return response()->json([
                'packagings' => [],
                'success' => false,
                'message' => 'Error searching packaging: ' . $th->getMessage()
            ], 500);
        }
    }
}
