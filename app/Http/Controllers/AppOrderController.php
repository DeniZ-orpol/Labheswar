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
    public function index()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $orders = AppCartsOrderBill::on($branch->connection_name)->paginate(10);

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
            ->with('product')
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
