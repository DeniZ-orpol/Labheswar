<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\HsnCode;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['company','category','hsnCode'])->get();

        return view('products.index', compact('products'));
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
    public function store(Request $request)
    {
        try {
            //code...
            $validate = $request->validate([
                'product_barcode' => 'required|string|max:255',
                'product_name' => 'required|string|max:255',
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
    
            $companyId = null;
            if (!empty($validate['product_company'])) {
                $company = Company::firstOrCreate(
                    ['name' => $validate['product_company']],
                    ['name' => $validate['product_company'], 'status' => 1]
                );
                $companyId = $company->id;
            }
    
            $categoryId = null;
            if (!empty($validate['product_category'])) { // Add category_name to validation
                $category = Category::firstOrCreate(
                    ['name' => $validate['product_category']],
                    ['name' => $validate['product_category'], 'status' => 1]
                );
                $categoryId = $category->id;
            }
    
            $hsnCodeId = null;
            if (!empty($validate['hsn_code'])) {
                $hsnCode = HsnCode::firstOrCreate(
                    ['hsn_code' => $validate['hsn_code']],
                    ['hsn_code' => $validate['hsn_code'], ]
                );
                $hsnCodeId = $hsnCode->id;
            }
    
            $data = [
                'product_name' => $validate['product_name'],
                'barcode' => $validate['product_barcode'],
                'search_option' => $validate['search_option'],
                'unit_types' => $validate['unit_type'],
                'decimal_btn' => isset($validate['decimal_btn']) ? 1 : 0,
                'company' => $companyId,
                'category_id' => $categoryId,
                'hsn_code_id' => $hsnCodeId,
                'sgst' => $validate['sgst'],
                'cgst1' => $validate['cgst_1'],
                'cgst2' => $validate['cgst_2'],
                'cess' => $validate['cess'],
                'mrp' => $validate['mrp'],
                'purchase_rate' => $validate['purchase_rate'],
                'sale_rate_a' => $validate['sale_rate_a'],
                'sale_rate_b' => $validate['sale_rate_b'],
                'sale_rate_c' => $validate['sale_rate_c'],
                'sale_online' => isset($validate['sale_online']) ? 1 : 0,
                'gst_active' => isset($validate['gst_active']) ? 1 : 0,
                'converse_carton' => $validate['converse_carton'],
                'converse_box' => $validate['converse_boc'],
                'converse_pcs' => $validate['converse_pcs'],
                'negative_billing' => $validate['negative_billing'],
                'min_qty' => $validate['min_qty'],
                'reorder_qty' => $validate['reorder_qty'],
                'discount' => $validate['discount'],
                'max_discount' => $validate['max_discount'],
                'discount_scheme' => $validate['discount_scheme'],
                'bonus_use' => $validate['bonus_use'] == 'yes' ? 1 : 0
            ];
            // Create the product
            $product = Product::create($data);
    
            // Redirect to product index with success message and product data
            return redirect()->route('product.index')
                ->with('success', 'Product created successfully!');
                // ->with('product', $product);
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
