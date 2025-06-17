<?php

namespace App\Http\Controllers;

use App\Models\PurchaseParty;
use Illuminate\Http\Request;

class PurchasePartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $parties = PurchaseParty::on($branchConnection)->paginate(10);

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
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $validate = $request->validate([
            'party_name' => 'required|string'
        ]);

        $data = [
            'party_name' => $validate['party_name']
        ];

        PurchaseParty::on($branchConnection)->create($data);

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
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $party = PurchaseParty::on($branchConnection)->where('id', $id)->first();

        return view('purchase.party.edit', compact('party'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (session('user_type') !== 'branch' || !session('branch_connection')) {
            return redirect()->back()->with('error', 'Branch session not found. Please login again.');
        }

        // Get branch connection name from session
        $branchConnection = session('branch_connection');

        $validate = $request->validate([
            'party_name' => 'required|string'
        ]);

        $party = PurchaseParty::on($branchConnection)->findOrFail($id);

        $party->update($validate);

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
