@extends('app')

@section('content')
    @php
        // $isSuperAdmin = strtolower($role->role_name) === 'super admin';

        // Get gst from HSN
        $hsnGst = null;
        if ($product->hsnCode && $product->hsnCode->gst) {
            $hsnGst = json_decode($product->hsnCode->gst, true);
        }
    @endphp
    <div class="content">
        <div class="intro-y grid grid-cols-12 gap-5 mt-5">
            <div class="col-span-12">
                <div class="box p-5 rounded-md">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium text-base truncate">Product Details</div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <!-- LEFT COLUMN -->
                        <div class="p-5 rounded-md bg-slate-100">
                            <div class="font-medium text-lg mb-3">Product Information</div>
                            <p><strong>Name:</strong> {{ $product->product_name }}</p>
                            <p><strong>Barcode:</strong> {{ $product->barcode }}</p>
                            <p><strong>Unit Type:</strong> {{ $product->unit_types }}</p>
                            <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                            <p><strong>Company:</strong> {{ $product->pCompany->name ?? 'N/A' }}</p>
                            <p><strong>HSN Code:</strong> {{ $product->hsnCode->hsn_code ?? 'N/A' }}</p>
                            <p><strong>Decimal Button:</strong> {{ $product->decimal_btn ? 'Yes' : 'No' }}</p>
                            <p><strong>Sale Online:</strong> {{ $product->sale_online ? 'Yes' : 'No' }}</p>
                            <p><strong>GST Active:</strong> {{ $product->gst_active ? 'Yes' : 'No' }}</p>
                            <p><strong>MRP:</strong> ₹{{ $product->mrp }}</p>
                            <p><strong>Purchase Rate:</strong> ₹{{ $product->purchase_rate }}</p>
                            <p><strong>Sale Rate A:</strong> ₹{{ $product->sale_rate_a }}</p>
                            <p><strong>Sale Rate B:</strong> ₹{{ $product->sale_rate_b }}</p>
                            <p><strong>Sale Rate C:</strong> ₹{{ $product->sale_rate_c }}</p>
                            <p><strong>SGST:</strong> {{ isset($hsnGst['SGST']) ? $hsnGst['SGST'].'%' : '-' }}</p>
                            <p><strong>CGST:</strong> {{ isset($hsnGst['CGST']) ? $hsnGst['CGST'].'%' : '-' }}</p>
                            <p><strong>IGST:</strong> {{ isset($hsnGst['IGST']) ? $hsnGst['IGST'].'%' : '-' }}</p>
                            <p><strong>Cess:</strong> {{ isset($hsnGst['CESS']) ? $hsnGst['CESS'].'%' : '-' }}</p>
                            <p><strong>Converse (Carton):</strong> {{ $product->converse_carton }}</p>
                            <p><strong>Carton Barcode:</strong> {{ $product->carton_barcode }}</p>
                            <p><strong>Converse (Box):</strong> {{ $product->converse_box }}</p>
                            <p><strong>Box Barcode:</strong> {{ $product->box_barcode }}</p>
                            {{-- <p><strong>Converse (Pcs):</strong> {{ $product->converse_pcs }}</p> --}}
                            <p><strong>Min Qty:</strong> {{ $product->min_qty }}</p>
                            <p><strong>Reorder Qty:</strong> {{ $product->reorder_qty }}</p>
                            <p><strong>Negative Billing:</strong> {{ $product->negative_billing ?? 'No' }}</p>
                            <p><strong>Discount:</strong> {{ $product->discount ?? 'N/A' }}</p>
                            <p><strong>Max Discount:</strong> {{ $product->max_discount }}%</p>
                            <p><strong>Discount Scheme:</strong> {{ $product->discount_scheme ?? 'N/A' }}</p>
                            <p><strong>Bonus Use:</strong> {{ $product->bonus_use ? 'Yes' : 'No' }}</p>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="p-5 rounded-md bg-slate-100">
                            @if ($product->image)
                                <div class="mt-5">
                                    <div class="font-medium text-lg mb-2">Product Image</div>
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image"
                                        style="max-width: 250px; border-radius: 10px;" />
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Image Preview -->


                    <!-- Navigation -->
                    <div class="mt-5">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Product List</a>
                        <a href="{{ route('products.edit', $product->id) }}"
                            class="btn btn-primary ml-2">Edit Product</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
