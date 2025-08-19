<?php

namespace App\Http\Controllers;

use App\Models\AppCartsOrderBill;
use App\Models\AppCartsOrders;
use App\Traits\BranchAuthTrait;
use Illuminate\Http\Request;

class AppOrderController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $perPage = 20;

        $query = AppCartsOrderBill::on($branch->connection_name);

        // Apply search if present
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate($perPage);
        // Return AJAX response for infinite scroll
        if ($request->ajax()) {
            return view('appOrders.rows', compact('orders'))->render();
        }

        return view('appOrders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $order = AppCartsOrderBill::on($branch->connection_name)
            ->where('id',$id)
            ->first();

        $orderItems = AppCartsOrders::on($branch->connection_name)
            ->with('product.hsnCode')
            ->where('order_receipt_id', $id)->get();

        $totalItems = $orderItems->count();

        return view('appOrders.receipt', compact('order', 'orderItems', 'totalItems', 'branch'));
    }

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
