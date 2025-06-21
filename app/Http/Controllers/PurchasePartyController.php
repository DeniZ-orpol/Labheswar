<?php

namespace App\Http\Controllers;

use App\Models\PurchaseParty;
use App\Traits\BranchAuthTrait;
use Illuminate\Http\Request;

class PurchasePartyController extends Controller
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

        $parties = PurchaseParty::on($branch->connection_name)->orderBy('id', 'desc')->paginate(10);

        return view('purchase.party.index', compact('parties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('purchase.party.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $validate = $request->validate([
            'party_name' => 'required|string'
        ]);

        $data = [
            'party_name' => $validate['party_name']
        ];

        PurchaseParty::on($branch->connection_name)->create($data);

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $party = PurchaseParty::on($branch->connection_name)->where('id', $id)->first();

        return view('purchase.party.edit', compact('party'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $validate = $request->validate([
            'party_name' => 'required|string'
        ]);

        $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);

        $party->update($validate);

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        // Find the product using branch connection
        $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);

        // Delete the purchase party
        $party->delete();

        return redirect()->route('purchase.party.index')->with('success', 'Product deleted successfully!');
    }
}
