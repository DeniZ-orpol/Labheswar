<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
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
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $parties = PurchaseParty::get();
            $purchaseReceipt = PurchaseReceipt::with(['purchaseParty', 'createUser', 'updateUser'])
                ->orderByDesc('id')->paginate(10);
        } else {
            $parties = PurchaseParty::on($branch->connection_name)->get();
            $purchaseReceipt = PurchaseReceipt::on($branch->connection_name)
                ->with(['purchaseParty', 'createUser', 'updateUser'])
                ->orderByDesc('id')->paginate(10);
        }

        return view('stock.index', compact(['parties', 'purchaseReceipt']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $branches = Branch::where('id' , '!=', $branch->id)->get();

        return view('stock.create', compact(['branches']));
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
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        //
    }
}
