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
            // Get authenticated user
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Get user's branch
            $branch = Branch::where('id', $user->branch_id)
                ->where('status', 'active')
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No accessible branch found for user'
                ], 404);
            }

            // Configure branch database connection
            configureBranchConnection($branch);

            // Validate request
            $request->validate([
                'product_id' => 'required|integer',
                'product_price' => 'required|numeric',
                'product_qty' => 'nullable|integer|min:1',
                'product_weight' => 'nullable',
                'cart_id' => 'nullable|integer', // Optional cart_id
                'new_cart' => 'sometimes|boolean' // Force new cart for same user
            ]);

            $productId = $request->input('product_id');
            $productPrice = $request->input('product_price');
            $requestedQuantity = $request->input('product_qty');
            $productWeight = $request->input('product_weight');
            $requestedCartId = $request->input('cart_id'); // Optional
            $newCart = $request->input('new_cart', false); // Default false

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

                // Check inventory availability
                $inventory = Inventory::on($branch->connection_name)
                    ->where('product_id', $productId)
                    ->first();

                if (!$inventory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not available in inventory'
                    ], 400);
                }

                if ($inventory->quantity < $requestedQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available quantity: ' . $inventory->quantity
                    ], 400);
                }

                // Handle cart selection based on request
                if ($requestedCartId) {
                    // Case 3: User wants to add to a specific cart
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
                        // Cart is available, assign to user
                        $targetCart->update([
                            'user_id' => $user->id,
                            'status' => 'unavailable'
                        ]);
                        $cart = $targetCart;
                    } elseif ($targetCart->user_id === $user->id) {
                        // Cart already belongs to this user
                        $cart = $targetCart;
                    } else {
                        // Cart belongs to another user, find any available cart
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
                            'status' => 'unavailable'
                        ]);

                        $cart = $availableCart;
                    }
                } elseif ($newCart) {
                    // Case 2: User wants a new cart (multiple carts for same user)
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
                    // Case 1: Default - use user's existing cart or get first available
                    $userCart = Cart::on($branch->connection_name)
                        ->where('user_id', $user->id)
                        ->where('status', 'unavailable')
                        ->first();

                    if ($userCart) {
                        // User has existing cart, use it
                        $cart = $userCart;
                    } else {
                        // User has no cart, find first available cart
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

                // Check if product already in this cart
                // $existingCartItem = AppCartsOrders::on($branch->connection_name)
                //     ->where('cart_id', $cart->id)
                //     ->where('product_id', $productId)
                //     ->where('user_id', $user->id)
                //     ->first();

                // if ($existingCartItem) {
                //     // Update existing cart item
                //     $newQuantity = $existingCartItem->product_quantity + $requestedQuantity;

                //     // Check if total quantity exceeds inventory
                //     if ($newQuantity > $inventory->quantity) {
                //         return response()->json([
                //             'success' => false,
                //             'message' => 'Total quantity exceeds available stock. Available: ' . $inventory->quantity . ', Current in cart: ' . $existingCartItem->product_quantity
                //         ], 400);
                //     }

                //     // Calculate new totals using frontend price
                //     $subTotal = $newQuantity * $productPrice;
                //     $gstAmount = ($subTotal * ($product->gst_percentage ?? 0)) / 100;
                //     $totalAmount = $subTotal + $gstAmount;

                //     $existingCartItem->update([
                //         'product_quantity' => $newQuantity,
                //         'product_price' => $productPrice,
                //         'product_weight' => $productWeight,
                //         'sub_total' => $subTotal,
                //         'gst' => $gstAmount,
                //         'gst_p' => $product->gst_percentage ?? 0,
                //         'total_amount' => $totalAmount
                //     ]);

                //     $cartItem = $existingCartItem;
                // } else {
                // Create new cart item using frontend data
                $subTotal = $requestedQuantity * $productPrice;
                $gstAmount = ($subTotal * ($product->gst_percentage ?? 0)) / 100;
                $totalAmount = $subTotal + $gstAmount;

                $cartItem = AppCartsOrders::on($branch->connection_name)->create([
                    'user_id' => $user->id,
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'firm_id' => $product->firm_id ?? null,
                    'product_weight' => $productWeight,
                    'product_price' => $productPrice,
                    'product_quantity' => $requestedQuantity,
                    'taxes' => $gstAmount,
                    'sub_total' => $subTotal,
                    'total_amount' => $totalAmount,
                    'gst' => $gstAmount,
                    'gst_p' => $product->gst_percentage ?? 0,
                    'return_product' => 0
                ]);

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
                // }

                // Update inventory (reduce quantity)
                $inventory->decrement('quantity', $requestedQuantity);

                // Commit transaction
                DB::connection($branch->connection_name)->commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'data' => [
                        'cart_id' => $cart->id,
                        'cart_status' => $cart->status,
                        'cart_item' => $cartItem,
                        'remaining_inventory' => $inventory->quantity - $requestedQuantity,
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
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createCartOrderReceipt(Request $request)
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
                'cart_id' => 'required|integer',
                // 'bill_due_date' => 'sometimes|date',
                // 'payment_status' => 'sometimes|in:pending,paid,failed',
                // 'discount_rs' => 'sometimes|numeric|min:0',
                // 'discount_percentage' => 'sometimes|numeric|min:0|max:100',
                // 'is_delivery' => 'sometimes|boolean',
                // 'address_id' => 'sometimes|integer',
                // 'ship_to_name' => 'sometimes|string|max:255',
                // 'expected_delivery_date' => 'sometimes|date',
                // 'razorpay_payment_id' => 'sometimes|string'
            ]);

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

            DB::connection($branch->connection_name)->beginTransaction();

            try {
                $cartId = $request->input('cart_id');

                // Get cart and verify it exists
                $cart = Cart::on($branch->connection_name)
                    ->where('id', $cartId)
                    ->first();

                if (!$cart) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart not found'
                    ], 404);
                }

                // Get cart items to calculate totals
                $cartItems = AppCartsOrders::on($branch->connection_name)
                    ->where('cart_id', $cartId)
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
                $discountRs = $request->input('discount_rs', 0);
                $discountPercentage = $request->input('discount_percentage', 0);

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

                // Optional: Clear cart after bill creation
                if ($request->input('clear_cart', true)) { // Default to true
                    // Make cart available again
                    $cart->update([
                        'user_id' => null,
                        'status' => 'available'
                    ]);
                }

                DB::connection($branch->connection_name)->commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order bill created successfully',
                    'data' => [
                        'bill_id' => $orderBill->id,
                        'cart_id' => $cartId,
                        'customer_name' => $orderBill->customer_name,
                        'sub_total' => $orderBill->sub_total,
                        'total_taxes' => $orderBill->total_texes,
                        'discount_rs' => $orderBill->discount_rs,
                        'total' => $orderBill->total,
                        'payment_status' => $orderBill->payment_status,
                        'is_delivery' => $orderBill->is_delivery,
                        'branch' => $branch->name
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

    public function addToCart(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $branch = $user->branch;
            if (!$branch || $branch->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'No accessible branch found'], 404);
            }

            // Step 1: Configure branch DB connection
            $connection = configureBranchConnection($branch);
            if (!$connection) {
                return response()->json(['success' => false, 'message' => 'Branch connection configuration failed'], 500);
            }

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
                'success'   => true,
                'message'   => 'Product added to cart',
                'cart_id'   => $cart->id,
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
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->validate([
                'cart_item_id' => 'required|integer',
                'quantity'     => 'required|integer|min:1'
            ]);

            $branch = $user->branch;
            if (!$branch || $branch->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'No accessible branch found'
                ], 404);
            }

            $connection = configureBranchConnection($branch);
            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch connection configuration failed'
                ], 500);
            }

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
}
