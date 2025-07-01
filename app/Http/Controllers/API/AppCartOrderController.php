<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppCartsOrderBill;
use App\Models\AppCartsOrders;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\PopularProducts;
use App\Models\Product;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AppCartOrderController extends Controller
{
    use BranchAuthTrait;
    public function addProductToCart(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // Validate request - UPDATED for unit-aware product_weight
            $request->validate([
                'product_id' => 'required|integer',
                'product_price' => 'required|numeric',
                'product_qty' => 'nullable|numeric|min:0.001',
                'product_weight' => 'nullable|string', // Store as string with unit (e.g., "0.5kg", "500g")
                'cart_id' => 'nullable|integer',
                'new_cart' => 'sometimes|boolean'
            ]);

            $productId = $request->input('product_id');
            $productPrice = $request->input('product_price');
            $requestedQuantity = $request->input('product_qty', 1);
            $productWeightInput = $request->input('product_weight'); // Raw input like "500g"
            $requestedCartId = $request->input('cart_id');
            $newCart = $request->input('new_cart', false);

            // Start database transaction
            DB::connection($branch->connection_name)->beginTransaction();

            try {
                // Get product from branch database
                $product = Product::on($branch->connection_name)
                    ->where('id', $productId)
                    ->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }

                // Check inventory availability - GET ALL INVENTORY ENTRIES
                $inventoryEntries = Inventory::on($branch->connection_name)
                    ->where('product_id', $productId)
                    ->where('quantity', '>', 0) // Only get entries with positive quantity
                    ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                    ->get();

                if ($inventoryEntries->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not available in inventory'
                    ], 400);
                }

                // Calculate total available stock
                $totalAvailableStock = $inventoryEntries->sum('quantity');

                // NEW: Parse and convert product_weight for inventory management only
                $inventoryCheckQuantity = $requestedQuantity; // Default for fixed quantity
                $isLooseQuantity = false;
                $inventoryDeductionQuantity = 0;

                // Check if this is a loose quantity product and has weight input
                if ($product->decimal_btn == 1 && !empty($productWeightInput)) {
                    $weightConversion = $this->parseAndConvertWeight($productWeightInput, $product->unit_types);

                    if ($weightConversion['success']) {
                        $inventoryDeductionQuantity = $weightConversion['base_quantity']; // For inventory deduction
                        $inventoryCheckQuantity = $inventoryDeductionQuantity; // For stock checking
                        $isLooseQuantity = true;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => $weightConversion['error']
                        ], 400);
                    }
                }

                // Check stock availability using the correct quantity
                if ($totalAvailableStock < $inventoryCheckQuantity) {
                    $unit = $isLooseQuantity ? strtoupper($product->unit_types) : 'PCS';
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available quantity: ' . $totalAvailableStock . ' ' . $unit
                    ], 400);
                }

                // Handle cart selection (keeping your existing logic)
                if ($requestedCartId) {
                    $targetCart = Cart::on($branch->connection_name)
                        ->where('id', $requestedCartId)
                        ->first();

                    if (!$targetCart) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Requested cart not found'
                        ], 404);
                    }

                    if ($targetCart->status === 'available') {
                        $targetCart->update([
                            'user_id' => $user->id,
                            'status' => 'unavailable'
                        ]);
                        $cart = $targetCart;
                    } elseif ($targetCart->user_id === $user->id) {
                        $cart = $targetCart;
                    } else {
                        $availableCart = Cart::on($branch->connection_name)
                            ->where('status', 'available')
                            ->first();

                        if (!$availableCart) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Requested cart not available and no other carts available'
                            ], 400);
                        }

                        $availableCart->update([
                            'user_id' => $user->id,
                            'status' => 'unavailable'  // Cart now has products, so unavailable
                        ]);

                        $cart = $availableCart;
                    }
                } elseif ($newCart) {
                    $availableCart = Cart::on($branch->connection_name)
                        ->where('status', 'available')
                        ->first();

                    if (!$availableCart) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No carts available for new cart request'
                        ], 400);
                    }

                    $availableCart->update([
                        'user_id' => $user->id,
                        'status' => 'unavailable'
                    ]);

                    $cart = $availableCart;
                } else {
                    $userCart = Cart::on($branch->connection_name)
                        ->where('user_id', $user->id)
                        ->where('status', 'unavailable')
                        ->first();

                    if ($userCart) {
                        $cart = $userCart;
                    } else {
                        $availableCart = Cart::on($branch->connection_name)
                            ->where('status', 'available')
                            ->first();

                        if (!$availableCart) {
                            return response()->json([
                                'success' => false,
                                'message' => 'No carts available'
                            ], 400);
                        }

                        $availableCart->update([
                            'user_id' => $user->id,
                            'status' => 'unavailable'
                        ]);

                        $cart = $availableCart;
                    }
                }

                // Check if product is already in cart
                $existingCartItem = AppCartsOrders::on($branch->connection_name)
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingCartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product already exists in cart. Please remove the existing item first or update its quantity.'
                    ], 400);
                }

                // Calculate cart item values based on product type
                if ($isLooseQuantity) {
                    // For loose quantity: use frontend calculated price directly
                    $subTotal = $productPrice;
                    $finalQuantity = 1; // Always 1 line item for loose products
                    $finalWeight = $productWeightInput; // Store converted base unit value
                } else {
                    // For fixed quantity: calculate normally
                    $subTotal = $requestedQuantity * $productPrice;
                    $finalQuantity = $requestedQuantity;
                    $finalWeight = null; // No loose quantity for fixed products
                }

                $gstAmount = ($subTotal * ($product->gst_percentage ?? 0)) / 100;
                $totalAmount = $subTotal + $gstAmount;

                // Create new cart item using calculated values
                $cartItem = AppCartsOrders::on($branch->connection_name)->create([
                    'user_id' => $user->id,
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'firm_id' => $product->firm_id ?? null,
                    'product_weight' => $finalWeight, // Store base unit value (e.g., 0.5 for 500g)
                    'product_price' => $productPrice, // base price without gst
                    'product_quantity' => $finalQuantity,
                    'taxes' => $gstAmount,
                    'sub_total' => $subTotal,
                    'total_amount' => $totalAmount,
                    'gst' => $gstAmount,
                    'gst_p' => $product->gst_percentage ?? 0,
                    'return_product' => 0
                ]);

                // Update popular products
                $selection = PopularProducts::on($branch->connection_name)
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($selection) {
                    $selection->increment('count');
                } else {
                    PopularProducts::on($branch->connection_name)->create([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                        'count' => 1
                    ]);
                }

                // UPDATED: Deduct inventory using FIFO method
                $remainingToDeduct = $inventoryCheckQuantity;

                foreach ($inventoryEntries as $inventoryEntry) {
                    if ($remainingToDeduct <= 0) {
                        break; // All quantity has been deducted
                    }

                    $availableInThisEntry = $inventoryEntry->quantity;
                    $deductFromThisEntry = min($remainingToDeduct, $availableInThisEntry);

                    // Update this inventory entry
                    $inventoryEntry->decrement('quantity', $deductFromThisEntry);

                    $remainingToDeduct -= $deductFromThisEntry;
                }

                // Calculate remaining total inventory for response
                $remainingTotalStock = Inventory::on($branch->connection_name)
                    ->where('product_id', $productId)
                    ->sum('quantity');

                // Commit transaction
                DB::connection($branch->connection_name)->commit();

                // Enhanced response with converted weight info
                $displayQuantity = '';
                if ($isLooseQuantity) {
                    $displayQuantity = $productWeightInput; // Original format like "500g", "0.5kg"
                } else {
                    $displayQuantity = $requestedQuantity . ' PCS';
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'data' => [
                        'cart_id' => $cart->id,
                        'cart_status' => $cart->status,
                        'cart_item' => $cartItem,
                        'remaining_inventory' => $remainingTotalStock,
                        'branch' => $branch->name,
                        'is_loose_quantity' => $isLooseQuantity,
                        'display_quantity' => $displayQuantity,
                        'stored_weight' => $finalWeight, // What was stored in product_weight column
                        'inventory_deducted' => $inventoryCheckQuantity . ' ' . ($isLooseQuantity ? strtoupper($product->unit_types) : 'PCS')
                    ]
                ]);
            } catch (Exception $e) {
                DB::connection($branch->connection_name)->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse and convert weight input to base unit
     * @param string $weightInput (e.g., "500g", "0.5kg", "250ml", "2L")
     * @param string $productBaseUnit (e.g., "KG", "LITER")
     * @return array
     */
    private function parseAndConvertWeight($weightInput, $productBaseUnit)
    {
        try {
            // Remove spaces and convert to lowercase for parsing
            $input = strtolower(trim($weightInput));
            $baseUnit = strtoupper($productBaseUnit);

            // Extract number and unit using regex
            if (!preg_match('/^([0-9]*\.?[0-9]+)\s*([a-z]+)$/i', $input, $matches)) {
                return [
                    'success' => false,
                    'error' => 'Invalid weight format. Use format like "500g", "0.5kg", "250ml", "2L"'
                ];
            }

            $quantity = (float) $matches[1];
            $unit = strtolower($matches[2]);

            // Convert to base unit based on product type
            switch ($baseUnit) {
                case 'KG':
                    $convertedQuantity = $this->convertToKilograms($quantity, $unit);
                    break;

                case 'LITER':
                case 'L':
                    $convertedQuantity = $this->convertToLiters($quantity, $unit);
                    break;

                default:
                    // For other units, assume direct conversion
                    $convertedQuantity = $quantity;
            }

            if ($convertedQuantity === false) {
                return [
                    'success' => false,
                    'error' => "Invalid unit '{$unit}' for product type '{$baseUnit}'"
                ];
            }

            if ($convertedQuantity <= 0) {
                return [
                    'success' => false,
                    'error' => 'Quantity must be greater than 0'
                ];
            }

            return [
                'success' => true,
                'base_quantity' => $convertedQuantity,
                'display' => $weightInput, // Original input for display
                'unit' => $unit,
                'converted_unit' => $baseUnit
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error parsing weight: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Convert various weight units to kilograms
     */
    private function convertToKilograms($quantity, $unit)
    {
        switch ($unit) {
            case 'kg':
            case 'kgs':
                return $quantity;

            case 'g':
            case 'gm':
            case 'gram':
            case 'grams':
                return $quantity / 1000;

            case 'lb':
            case 'lbs':
            case 'pound':
            case 'pounds':
                return $quantity * 0.453592;

            case 'oz':
            case 'ounce':
            case 'ounces':
                return $quantity * 0.0283495;

            default:
                return false; // Invalid unit
        }
    }

    /**
     * Convert various volume units to liters
     */
    private function convertToLiters($quantity, $unit)
    {
        switch ($unit) {
            case 'l':
            case 'ltr':
            case 'liter':
            case 'liters':
            case 'litre':
            case 'litres':
                return $quantity;

            case 'ml':
            case 'mlt':
            case 'milliliter':
            case 'milliliters':
            case 'millilitre':
            case 'millilitres':
                return $quantity / 1000;

            case 'cl':
            case 'centiliter':
            case 'centiliters':
                return $quantity / 100;

            case 'dl':
            case 'deciliter':
            case 'deciliters':
                return $quantity / 10;

            case 'gal':
            case 'gallon':
            case 'gallons':
                return $quantity * 3.78541; // US gallon

            case 'pt':
            case 'pint':
            case 'pints':
                return $quantity * 0.473176;

            case 'qt':
            case 'quart':
            case 'quarts':
                return $quantity * 0.946353;

            case 'fl oz':
            case 'floz':
            case 'fluid ounce':
            case 'fluid ounces':
                return $quantity * 0.0295735;

            default:
                return false; // Invalid unit
        }
    }

    /**
     * Get selected cart items
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCartItems(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Validate request
            $request->validate([
                'cart_id' => 'required|integer'
            ]);

            $cartId = $request->input('cart_id');

            $branch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No accessible branch found'
                ], 404);
            }

            configureBranchConnection($branch);

            // Get selected cart
            $cart = Cart::on($branch->connection_name)
                ->where('id', $cartId)
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            // Get cart items with product details
            $cartItems = AppCartsOrders::on($branch->connection_name)
                ->with(['product'])
                ->where('cart_id', $cartId)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart is empty',
                    'data' => [
                        'cart_id' => $cart->id,
                        'cart_status' => $cart->status,
                        'cart_items' => [],
                        'total_items' => 0,
                        'cart_total' => 0,
                        'branch' => $branch->name
                    ]
                ]);
            }

            $cartTotal = $cartItems->sum('total_amount');
            $totalItems = $cartItems->sum('product_quantity');

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_id' => $cart->id,
                    'cart_status' => $cart->status,
                    'user_id' => $cart->user_id,
                    'cart_items' => $cartItems,
                    'total_items' => $totalItems,
                    'cart_total' => $cartTotal,
                    'branch' => $branch->name
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Cart order bill
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCartOrderReceipt(Request $request)
    {
        try {
            // Get authenticated user using trait
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // Validate request
            $request->validate([
                'cart_id' => 'required|integer',
                'customer_name' => 'sometimes|string|max:255',
                'customer_contact' => 'sometimes|string|max:20',
                'bill_due_date' => 'sometimes|date',
                'payment_status' => 'sometimes|in:pending,paid,failed',
                'discount_rs' => 'sometimes|numeric|min:0',
                'discount_percentage' => 'sometimes|numeric|min:0|max:100',
                'is_delivery' => 'sometimes|boolean',
                'address_id' => 'sometimes|integer',
                'ship_to_name' => 'sometimes|string|max:255',
                'expected_delivery_date' => 'sometimes|date',
                'razorpay_payment_id' => 'sometimes|string',
                'clear_cart' => 'sometimes|boolean'
            ]);

            DB::connection($branch->connection_name)->beginTransaction();

            try {
                $cartId = $request->input('cart_id');

                // Get cart and verify it exists and belongs to user
                $cart = Cart::on($branch->connection_name)
                    ->where('id', $cartId)
                    ->where('user_id', $user->id) // Ensure user owns the cart
                    ->first();

                if (!$cart) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart not found or access denied'
                    ], 404);
                }

                // Get cart items with product details for loose quantity info
                $cartItems = AppCartsOrders::on($branch->connection_name)
                    ->with([
                        'product' => function ($query) {
                            $query->select('id', 'product_name', 'barcode', 'unit_types', 'decimal_btn');
                        }
                    ])
                    ->where('cart_id', $cartId)
                    ->where('user_id', $user->id) // Ensure user owns the items
                    ->get();

                if ($cartItems->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart is empty, cannot create bill'
                    ], 400);
                }

                // Calculate totals
                $subTotal = $cartItems->sum('sub_total');
                $totalTaxes = $cartItems->sum('gst');
                $discountRs = (float) $request->input('discount_rs', 0);
                $discountPercentage = (float) $request->input('discount_percentage', 0);

                // Apply percentage discount to subtotal
                if ($discountPercentage > 0) {
                    $discountRs += ($subTotal * $discountPercentage) / 100;
                }

                $total = $subTotal + $totalTaxes - $discountRs;

                // Create order bill
                $orderBill = AppCartsOrderBill::on($branch->connection_name)->create([
                    'cart_id' => $cartId,
                    'total_texes' => $totalTaxes,
                    'sub_total' => $subTotal,
                    'total' => $total,
                    'customer_name' => $request->input('customer_name'),
                    'customer_contact' => $request->input('customer_contact'),
                    'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                    'bill_due_date' => $request->input('bill_due_date'),
                    'payment_status' => $request->input('payment_status', 'pending'),
                    'status' => 'active',
                    'user_id' => $user->id,
                    'discount_rs' => $discountRs,
                    'discount_percentage' => $request->input('discount_percentage', 0),
                    'return_order' => 0,
                    'is_delivery' => $request->input('is_delivery', false),
                    'address_id' => $request->input('address_id'),
                    'ship_to_name' => $request->input('ship_to_name'),
                    'expected_delivery_date' => $request->input('expected_delivery_date')
                ]);

                // Update cart items with the order receipt ID
                foreach ($cartItems as $item) {
                    $item->update([
                        'order_receipt_id' => $orderBill->id
                    ]);
                }

                // Prepare detailed items information with loose quantity details
                $itemsDetails = [];
                $totalItemsCount = 0;
                $looseQuantityItems = 0;
                $fixedQuantityItems = 0;

                foreach ($cartItems as $item) {
                    $product = $item->product;

                    // Determine if this is a loose quantity item
                    $isLooseQuantity = ($product && $product->decimal_btn == 1 && !empty($item->product_weight));

                    if ($isLooseQuantity) {
                        $looseQuantityItems++;
                        // Use the stored value directly (e.g., "0.5kg", "500g")
                        $displayQuantity = $item->product_weight;
                        $quantityDescription = "Loose quantity: {$displayQuantity}";
                    } else {
                        $fixedQuantityItems++;
                        $displayQuantity = $item->product_quantity . ' PCS';
                        $quantityDescription = "Fixed quantity: {$displayQuantity}";
                    }

                    $itemDetail = [
                        'cart_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $product ? $product->product_name : 'Unknown Product',
                        'product_barcode' => $product ? $product->barcode : null,
                        'product_unit_type' => $product ? $product->unit_types : 'PCS',
                        'is_loose_quantity' => $isLooseQuantity,
                        'product_quantity' => $item->product_quantity,
                        'product_weight' => $item->product_weight, // Keep original string value
                        'display_quantity' => $displayQuantity,
                        'quantity_description' => $quantityDescription,
                        'unit_price' => $item->product_price,
                        'sub_total' => $item->sub_total,
                        'tax_amount' => $item->gst,
                        'tax_percentage' => $item->gst_p,
                        'total_amount' => $item->total_amount,
                    ];

                    $itemsDetails[] = $itemDetail;
                    $totalItemsCount += $item->product_quantity;
                }

                // Optional: Clear cart after bill creation
                $cartCleared = false;
                if ($request->input('clear_cart', true)) { // Default to true
                    // Make cart available again (empty cart = available)
                    $cart->update([
                        'user_id' => null,
                        'status' => 'available'  // Empty cart is available
                    ]);
                    $cartCleared = true;
                }

                DB::connection($branch->connection_name)->commit();

                // Enhanced response with loose quantity information
                $responseData = [
                    'bill_id' => $orderBill->id,
                    'order_number' => 'ORD-' . str_pad($orderBill->id, 6, '0', STR_PAD_LEFT), // Generate order number
                    'cart_id' => $cartId,
                    'customer_name' => $orderBill->customer_name,
                    'customer_contact' => $orderBill->customer_contact,
                    'bill_summary' => [
                        'sub_total' => $orderBill->sub_total,
                        'total_taxes' => $orderBill->total_texes,
                        'discount_rs' => $orderBill->discount_rs,
                        'discount_percentage' => $orderBill->discount_percentage,
                        'total' => $orderBill->total
                    ],
                    'payment_info' => [
                        'payment_status' => $orderBill->payment_status,
                        'payment_method' => $orderBill->razorpay_payment_id ? 'online' : 'cash',
                        'razorpay_payment_id' => $orderBill->razorpay_payment_id,
                        'bill_due_date' => $orderBill->bill_due_date
                    ],
                    'delivery_info' => [
                        'is_delivery' => $orderBill->is_delivery,
                        'address_id' => $orderBill->address_id,
                        'ship_to_name' => $orderBill->ship_to_name,
                        'expected_delivery_date' => $orderBill->expected_delivery_date
                    ],
                    'items_summary' => [
                        'total_items' => count($itemsDetails),
                        'total_quantity' => $totalItemsCount,
                        'loose_quantity_items' => $looseQuantityItems,
                        'fixed_quantity_items' => $fixedQuantityItems
                    ],
                    'items' => $itemsDetails,
                    'cart_status' => [
                        'cart_cleared' => $cartCleared,
                        'cart_status' => $cartCleared ? 'available' : 'unavailable' // available when empty, unavailable when has products
                    ],
                    'branch_info' => [
                        'branch_name' => $branch->name,
                        'branch_id' => $branch->id
                    ],
                    'timestamps' => [
                        'created_at' => $orderBill->created_at,
                        'order_date' => now()->format('Y-m-d H:i:s')
                    ]
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Order bill created successfully',
                    'data' => $responseData
                ]);
            } catch (Exception $e) {
                DB::connection($branch->connection_name)->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format loose quantity for display
     * @return string
     */
    private function formatLooseQuantityDisplay($quantity, $unitType): string
    {
        if (empty($quantity) || empty($unitType)) {
            return $quantity . ' UNIT';
        }

        $unit = strtoupper($unitType);

        switch ($unit) {
            case 'KG':
                if ($quantity < 1) {
                    return ($quantity * 1000) . 'g';
                }
                return $quantity . 'kg';

            case 'LITER':
            case 'L':
                if ($quantity < 1) {
                    return ($quantity * 1000) . 'ml';
                }
                return $quantity . 'L';

            default:
                return $quantity . ' ' . $unit;
        }
    }

    /**
     * Get List of occupied carts
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCartList(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $branch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No accessible branch found'
                ], 404);
            }

            configureBranchConnection($branch);

            // Get all unavailable carts with user details and cart items count
            $unavailableCarts = Cart::on($branch->connection_name)
                ->with(['user:id,name,email'])
                ->where('status', 'unavailable')
                ->get();

            // Add cart items count and total amount for each cart
            $cartsWithDetails = $unavailableCarts->map(function ($cart) use ($branch) {
                $cartItems = AppCartsOrders::on($branch->connection_name)
                    ->where('cart_id', $cart->id)
                    ->get();

                return [
                    'cart_id' => $cart->id,
                    'user_id' => $cart->user_id,
                    'user_name' => $cart->user ? $cart->user->name : null,
                    'user_email' => $cart->user ? $cart->user->email : null,
                    'status' => $cart->status,
                    'total_items' => $cartItems->sum('product_quantity'),
                    'total_amount' => $cartItems->sum('total_amount'),
                    'items_count' => $cartItems->count(),
                    'created_at' => $cart->created_at,
                    'updated_at' => $cart->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_list' => $cartsWithDetails,
                    'total_carts' => $cartsWithDetails->count(),
                    'branch' => $branch->name
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            $connection = $branch->connection_name;

            // Step 2: Validate request input
            $request->validate([
                'barcode' => 'required|string',
                'quantity' => 'nullable|integer|min:1',
                'cart_id' => 'nullable|integer',
                'new_cart' => 'sometimes|boolean'
            ]);

            $barcode = $request->barcode;
            $quantity = $request->quantity ?? 1;
            $requestedCartId = $request->input('cart_id');
            $newCart = $request->input('new_cart', false);

            // Step 3: Fetch product from DB
            $product = Product::on($connection)->where('barcode', $barcode)->first();
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $price = $product->price ?? 0;
            $weight = $product->product_weight ?? null;
            $firmId = $product->firm_id ?? null;
            $gstPercent = $product->gst_percentage ?? 0;

            $subTotal = $price * $quantity;
            $gstAmount = ($subTotal * $gstPercent) / 100;
            $totalAmount = $subTotal + $gstAmount;

            // Step 4: Cart logic
            if ($requestedCartId) {
                $targetCart = Cart::on($connection)->find($requestedCartId);
                if (!$targetCart) {
                    return response()->json(['success' => false, 'message' => 'Requested cart not found'], 404);
                }

                if ($targetCart->status === 'available') {
                    $targetCart->update(['user_id' => $user->id, 'status' => 'unavailable']);
                    $cart = $targetCart;
                } elseif ($targetCart->user_id === $user->id) {
                    $cart = $targetCart;
                } else {
                    $availableCart = Cart::on($connection)->where('status', 'available')->first();
                    if (!$availableCart) {
                        return response()->json(['success' => false, 'message' => 'No available cart for reassignment'], 400);
                    }
                    $availableCart->update(['user_id' => $user->id, 'status' => 'unavailable']);
                    $cart = $availableCart;
                }
            } elseif ($newCart) {
                $availableCart = Cart::on($connection)->where('status', 'available')->first();
                if (!$availableCart) {
                    return response()->json(['success' => false, 'message' => 'No carts available for new cart request'], 400);
                }
                $availableCart->update(['user_id' => $user->id, 'status' => 'unavailable']);
                $cart = $availableCart;
            } else {
                $cart = Cart::on($connection)
                    ->where('user_id', $user->id)
                    ->where('status', 'unavailable')
                    ->first();

                if (!$cart) {
                    $availableCart = Cart::on($connection)->where('status', 'available')->first();
                    if (!$availableCart) {
                        return response()->json(['success' => false, 'message' => 'No carts available'], 400);
                    }
                    $availableCart->update(['user_id' => $user->id, 'status' => 'unavailable']);
                    $cart = $availableCart;
                }
            }

            // Step 5: Add or update cart item
            $cartItem = AppCartsOrders::on($connection)
                ->where('cart_id', $cart->id)
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            // Step 6: Update popular products
            $selection = PopularProducts::on($connection)
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            if ($selection) {
                $selection->increment('count');
            } else {
                PopularProducts::on($connection)->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'count' => 1
                ]);
            }

            // Step 7: Save or update cart item
            if ($cartItem) {
                $cartItem->product_quantity += $quantity;
                $cartItem->sub_total += $subTotal;
                $cartItem->gst += $gstAmount;
                $cartItem->taxes += $gstAmount;
                $cartItem->total_amount += $totalAmount;
                $cartItem->save();
            } else {
                $cartItem = new AppCartsOrders();
                $cartItem->setConnection($connection);
                $cartItem->user_id = $user->id;
                $cartItem->cart_id = $cart->id;
                $cartItem->product_id = $product->id;
                $cartItem->firm_id = $firmId;
                $cartItem->product_weight = $weight;
                $cartItem->product_price = $price;
                $cartItem->product_quantity = $quantity;
                $cartItem->sub_total = $subTotal;
                $cartItem->gst = $gstAmount;
                $cartItem->gst_p = $gstPercent;
                $cartItem->taxes = $gstAmount;
                $cartItem->total_amount = $totalAmount;
                $cartItem->return_product = 0;
                $cartItem->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_id' => $cart->id,
                'cart_item' => $cartItem
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateQuantity(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            $connection = $branch->connection_name;

            $cartItem = AppCartsOrders::on($connection)
                ->where('id', $request->cart_item_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $product = Product::on($connection)->find($cartItem->product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $newQty = $request->quantity;
            $price = $product->price ?? 0;
            $subTotal = $newQty * $price;
            $gstPercent = $product->gst_percentage ?? 0;
            $gstAmount = ($subTotal * $gstPercent) / 100;
            $totalAmount = $subTotal + $gstAmount;

            $cartItem->product_quantity = $newQty;
            $cartItem->product_price = $price;
            $cartItem->sub_total = $subTotal;
            $cartItem->gst_p = $gstPercent;
            $cartItem->gst = $gstAmount;
            $cartItem->taxes = $gstAmount;
            $cartItem->total_amount = $totalAmount;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItem
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of order bills(Order receipts)
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getOrderBills()
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];
            $orders = AppCartsOrderBill::on($branch->connection_name)->get();
            return response()->json([
                'success' => true,
                'data' => [
                    'total_bills' => $orders->count(),
                    'order_bills' => $orders,
                    'branch' => $branch->name
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from cart and add it intro inventory
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function removeProductFromCart(Request $request)
    {
        try {
            // Get authenticated user using trait
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $role = $auth['role'];
            $branch = $auth['branch'];

            // Validate request
            $request->validate([
                'cart_item_id' => 'required|integer',
                'cart_id' => 'sometimes|integer', // Optional for extra validation
            ]);

            $cartItemId = $request->input('cart_item_id');
            $cartId = $request->input('cart_id'); // Optional

            // Start database transaction
            DB::connection($branch->connection_name)->beginTransaction();

            try {
                // Get cart item with product details
                $cartItem = AppCartsOrders::on($branch->connection_name)
                    ->where('id', $cartItemId)
                    ->where('user_id', $user->id) // Ensure user owns this cart item
                    ->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item not found or access denied'
                    ], 404);
                }

                // Optional: Validate cart_id if provided
                if ($cartId && $cartItem->cart_id != $cartId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart item does not belong to specified cart'
                    ], 400);
                }

                // Get product details
                $product = Product::on($branch->connection_name)
                    ->where('id', $cartItem->product_id)
                    ->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }

                // Get inventory record
                $inventory = Inventory::on($branch->connection_name)
                    ->where('product_id', $cartItem->product_id)
                    ->first();

                if (!$inventory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Inventory record not found'
                    ], 404);
                }

                // Determine quantity to add back to inventory
                $quantityToAddBack = 0;
                $displayQuantity = '';

                // Check if product supports loose quantity (decimal_btn = 1) and has product_weight
                if ($product->decimal_btn == 1 && !empty($cartItem->product_weight)) {
                    // For loose quantity items, parse the stored weight string and convert to base unit
                    $weightConversion = $this->parseAndConvertWeight($cartItem->product_weight, $product->unit_types);
                    if ($weightConversion['success']) {
                        $quantityToAddBack = $weightConversion['base_quantity'];
                        $displayQuantity = $cartItem->product_weight; // Use stored string directly
                    } else {
                        // Fallback to product_quantity if parsing fails
                        $quantityToAddBack = $cartItem->product_quantity;
                        $displayQuantity = $cartItem->product_weight;
                    }
                } else {
                    // For fixed quantity items, use product_quantity
                    $quantityToAddBack = $cartItem->product_quantity;
                    $displayQuantity = $cartItem->product_quantity . ' PCS';
                }

                // Store cart item details for response before deletion
                $removedItemDetails = [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $product->product_name,
                    'product_price' => $cartItem->product_price,
                    'product_quantity' => $cartItem->product_quantity,
                    'product_weight' => $cartItem->product_weight,
                    'sub_total' => $cartItem->sub_total,
                    'total_amount' => $cartItem->total_amount,
                    'taxes' => $cartItem->taxes,
                    'display_quantity' => $displayQuantity,
                    'quantity_added_back' => $quantityToAddBack,
                    'cart_id' => $cartItem->cart_id,
                    'is_loose_quantity' => ($product->decimal_btn == 1 && !empty($cartItem->product_weight))
                ];

                // Add quantity back to inventory
                $inventory->increment('quantity', $quantityToAddBack);

                // Remove the cart item
                $cartItem->delete();

                // Check if cart is now empty
                $remainingItems = AppCartsOrders::on($branch->connection_name)
                    ->where('cart_id', $cartItem->cart_id)
                    ->count();

                $cartStatus = 'unavailable'; // Cart still has products
                if ($remainingItems == 0) {
                    // Cart is empty, make it available
                    $cart = Cart::on($branch->connection_name)
                        ->where('id', $cartItem->cart_id)
                        ->first();

                    if ($cart) {
                        $cart->update([
                            'user_id' => null,
                            'status' => 'available'  // Empty cart is available
                        ]);
                        $cartStatus = 'available';  // Cart is now empty and available
                    }
                }

                // Update popular products (decrement count)
                $popularProduct = PopularProducts::on($branch->connection_name)
                    ->where('user_id', $user->id)
                    ->where('product_id', $cartItem->product_id)
                    ->first();

                if ($popularProduct && $popularProduct->count > 0) {
                    $popularProduct->decrement('count');

                    // Remove popular product record if count reaches 0
                    if ($popularProduct->fresh()->count <= 0) {
                        $popularProduct->delete();
                    }
                }

                // Commit transaction
                DB::connection($branch->connection_name)->commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from cart successfully',
                    'data' => [
                        'removed_item' => $removedItemDetails,
                        'inventory_updated' => [
                            'product_id' => $cartItem->product_id,
                            'product_name' => $product->product_name,
                            'quantity_added_back' => $quantityToAddBack,
                            'new_inventory_quantity' => $inventory->quantity,
                            'unit' => strtoupper($product->unit_types)
                        ],
                        'cart_status' => $cartStatus,
                        'remaining_items_in_cart' => $remainingItems,
                        'branch' => $branch->name
                    ]
                ]);
            } catch (Exception $e) {
                // Rollback transaction
                DB::connection($branch->connection_name)->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order bill details by ID
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function orderBill($id)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];
            $role = $auth['role'];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $order = $order = AppCartsOrderBill::where('id', $id)->first();

                $orderItems = AppCartsOrders::join('products', 'products.id', '=', 'app_cart_order.product_id')
                    ->where('app_cart_order.order_receipt_id', $id)
                    ->select(
                        'app_cart_order.*',
                        'products.product_name',
                        'products.barcode',
                        'products.image',
                        'products.unit_types'
                    )
                    ->get();

                $totalItems = $orderItems->count();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'order' => $order,
                        'order_items' => $orderItems,
                        'total_items' => $totalItems
                    ]
                ]);
            } else {
                $order = AppCartsOrderBill::on($branch->connection_name)
                    ->where('id', $id)
                    ->first();

                $orderItems = AppCartsOrders::on($branch->connection_name)
                    ->join('products', 'products.id', '=', 'app_cart_order.product_id')
                    ->where('app_cart_order.order_receipt_id', $id)
                    ->select(
                        'app_cart_order.*',
                        'products.product_name',
                        'products.barcode',
                        'products.image',
                        'products.unit_types'
                    )
                    ->get();

                $totalItems = $orderItems->count();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'order' => $order,
                        'order_items' => $orderItems,
                        'total_items' => $totalItems
                    ]
                ]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $ex->getMessage()
            ], 500);
        }
    }
    public function assignCartToUser(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer',
        ]);

        // ✅ Authenticated user, branch and connection
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $connection = $branch->connection_name;

        DB::connection($connection)->beginTransaction();

        try {
            // 🔍 Get cart from correct branch DB
            $cart = Cart::on($connection)
                ->where('id', $request->cart_id)
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => "Cart ID {$request->cart_id} not found in branch DB: {$connection}"
                ], 404);
            }

            $cart->setConnection($connection);

            // 🔄 Unassign all other carts assigned to this user
            Cart::on($connection)
                ->where('user_id', $user->id)
                ->where('id', '!=', $cart->id)
                ->update([
                    'user_id' => null,
                    'status' => 'available'
                ]);

            // 🔁 Unassign if already assigned to someone else
            if ($cart->user_id !== null && $cart->user_id !== $user->id) {
                $cart->user_id = null;
                $cart->status = 'available';
                $cart->save();
            }

            // ✅ Assign to current user and mark as unavailable
            $cart->user_id = $user->id;
            $cart->status = 'unavailable';
            $cart->save();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart assigned successfully',
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'status' => $cart->status,
                'branch_connection' => $connection
            ]);
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error while assigning cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function getAssignedCartId()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $connection = $branch->connection_name; // or $branch->db_connection

        // 🔍 Fetch cart assigned to this user in this branch DB
        $cart = DB::connection($connection)
            ->table('carts')
            ->where('user_id', $user->id)
            ->where('status', 'unavailable') // ✅ status you use when assigned
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'No cart assigned to this user in this branch'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assigned cart found',
            'cart_id' => $cart->id,
            'status' => $cart->status,
            'branch_connection' => $connection
        ]);
    }
}
