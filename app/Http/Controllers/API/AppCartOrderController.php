<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppCartsOrders;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppCartOrderController extends Controller
{
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
                'product_qty' => 'required|integer|min:1',
                'product_weight' => 'required',
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
                $existingCartItem = AppCartsOrders::on($branch->connection_name)
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingCartItem) {
                    // Update existing cart item
                    $newQuantity = $existingCartItem->product_quantity + $requestedQuantity;

                    // Check if total quantity exceeds inventory
                    if ($newQuantity > $inventory->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Total quantity exceeds available stock. Available: ' . $inventory->quantity . ', Current in cart: ' . $existingCartItem->product_quantity
                        ], 400);
                    }

                    // Calculate new totals using frontend price
                    $subTotal = $newQuantity * $productPrice;
                    $gstAmount = ($subTotal * ($product->gst_percentage ?? 0)) / 100;
                    $totalAmount = $subTotal + $gstAmount;

                    $existingCartItem->update([
                        'product_quantity' => $newQuantity,
                        'product_price' => $productPrice,
                        'product_weight' => $productWeight,
                        'sub_total' => $subTotal,
                        'gst' => $gstAmount,
                        'gst_p' => $product->gst_percentage ?? 0,
                        'total_amount' => $totalAmount
                    ]);

                    $cartItem = $existingCartItem;
                } else {
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
                }

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
}
