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
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $parties = PurchaseParty::orderBy('id', 'desc')->paginate(10);
        } else {
            $parties = PurchaseParty::on($branch->connection_name)->orderBy('id', 'desc')->paginate(10);
        }

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
        $role = $auth['role'];

        $validate = $request->validate([
            'party_name' => 'required|string',
            'company_name' => 'required|string',
            'gst_number' => 'required|string',
            'acc_no' => 'required|string',
            'ifsc_code' => 'required|string',
            'station' => 'required|string',
            'pincode' => 'string',
            'mobile_no' => 'string',
            'email' => 'string',
            'address' => 'string',
        ]);
        // dd($request->all());

        $data = [
            'party_name' => $validate['party_name'],
            'company_name' => $validate['party_name'],
            'gst_number' => $validate['party_name'],
            'acc_no' => $validate['party_name'],
            'ifsc_code' => $validate['party_name'],
            'station' => $validate['party_name'],
            'pincode' => $validate['party_name'],
            'mobile_no' => $validate['party_name'],
            'email' => $validate['party_name'],
            'address' => $validate['party_name'],
        ];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            PurchaseParty::create($data);
        } else {
            PurchaseParty::on($branch->connection_name)->create($data);
        }

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Find the purchase party using branch connection
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $party = PurchaseParty::findOrFail($id);
        } else {
            $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);
        }

        return view('purchase.party.show', compact('party'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $party = PurchaseParty::where('id', $id)->first();
        } else {
            $party = PurchaseParty::on($branch->connection_name)->where('id', $id)->first();
        }

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
        $role = $auth['role'];

        $validate = $request->validate([
            'party_name' => 'required|string',
            'company_name' => 'required|string',
            'gst_number' => 'required|string',
            'acc_no' => 'required|string',
            'ifsc_code' => 'required|string',
            'station' => 'required|string',
            'pincode' => 'string',
            'mobile_no' => 'string',
            'email' => 'string',
            'address' => 'string',
        ]);

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $party = PurchaseParty::findOrFail($id);
        } else {
            $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);
        }

        $party->update($validate);

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Find the product using branch connection
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $party = PurchaseParty::findOrFail($id);
        } else {
            $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);
        }

        // Delete the purchase party
        $party->delete();

        return redirect()->route('purchase.party.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Search parties for dropdown
     */
    public function partySearch(Request $request)
    {
        // Authenticate and get branch configuration
        $authResult = $this->authenticateAndConfigureBranch();

        if (is_array($authResult) && isset($authResult['success']) && !$authResult['success']) {
            return response()->json(['parties' => []]);
        }

        $user = $authResult['user'];
        $branch = $authResult['branch'];
        $role = $authResult['role'];

        try {
            // Get branch connection
            $branchConnection = $branch->connection_name;

            $search = $request->get('search', '');

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $parties = PurchaseParty::where('party_name', 'LIKE', "%{$search}%")
                    ->limit(10)
                    ->get();
            } else {
                $parties = PurchaseParty::on($branchConnection)
                    ->where('party_name', 'LIKE', "%{$search}%")
                    ->limit(10)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'parties' => $parties
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'parties' => [],
                'message' => 'Error searching parties: ' . $e->getMessage()
            ]);
        }
    }

    public function modalStore(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $validate = $request->validate([
            'party_name' => 'required|string',
            'company_name' => 'required|string',
            'gst_number' => 'required|string',
            'acc_no' => 'required|string',
            'ifsc_code' => 'required|string',
            'station' => 'required|string',
            'pincode' => 'string',
            'mobile_no' => 'string',
            'email' => 'string',
            'address' => 'string',
        ]);
        // dd($request->all());

        $data = [
            'party_name' => $validate['party_name'],
            'company_name' => $validate['party_name'],
            'gst_number' => $validate['party_name'],
            'acc_no' => $validate['party_name'],
            'ifsc_code' => $validate['party_name'],
            'station' => $validate['party_name'],
            'pincode' => $validate['party_name'],
            'mobile_no' => $validate['party_name'],
            'email' => $validate['party_name'],
            'address' => $validate['party_name'],
        ];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            PurchaseParty::create($data);
        } else {
            PurchaseParty::on($branch->connection_name)->create($data);
        }

        return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Created Successfully!');
    }
}
