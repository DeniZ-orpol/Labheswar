<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\PurchaseParty;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class PurchasePartyController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $query = strtoupper($role->role_name) === 'SUPER ADMIN'
            ? PurchaseParty::query()
            : PurchaseParty::on($branch->connection_name);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('party_name', 'like', "%{$search}%");
        }

        $parties = $query->orderBy('id', 'desc')->paginate(20);

        if ($request->ajax()) {
            return view('purchase.party.rows', compact('parties'))->render();
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
                'ledger_group' => 'required|string',
                'party_name' => 'required|string',
                'company_name' => 'nullable|string',
                'gst_number' => 'nullable|string',
                'gst_heading' => 'nullable|string',
                'acc_no' => 'nullable|string',
                'ifsc_code' => 'nullable|string',
                // 'station' => 'nullable|string',
                'state' => 'nullable|string',
                'pincode' => 'nullable|string',
                'mobile_no' => 'nullable|string',
                'email' => 'nullable|string',
                'address' => 'nullable|string',
                'balancing_method' => 'nullable|string|max:255',
                // 'mail_to' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'contact_person_no' => 'nullable|max:255',
                'designation' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:255',
                'ledger_category' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'pan_no' => 'nullable|string|max:255',
            ]);

            $data = [
                'ledger_group' => $validate['ledger_group'],
                'party_name' => $validate['party_name'],
                'company_name' => $validate['company_name'] ?? null,
                'gst_number' => $validate['gst_number'] ?? null,
                'gst_heading' => $validate['gst_heading'] ?? null,
                'mobile_no' => $validate['mobile_no'] ?? null,
                'email' => $validate['email'] ?? null,
                'address' => $validate['address'] ?? null,
                // 'station' => $validate['station'] ?? null,
                'state' => $validate['state'] ?? null,
                'acc_no' => $validate['acc_no'] ?? null,
                'ifsc_code' => $validate['ifsc_code'] ?? null,
                'pincode' => $validate['pincode'] ?? null,
                'balancing_method' => $validate['balancing_method'] ?? null,
                // 'mail_to' => $validate['mail_to'] ?? null,
                'contact_person' => $validate['contact_person'] ?? null,
                'contact_person_no' => $validate['contact_person_no'] ?? null,
                'designation' => $validate['designation'] ?? null,
                'note' => $validate['note'] ?? null,
                'ledger_category' => $validate['ledger_category'] ?? null,
                'country' => $validate['country'] ?? null,
                'pan_no' => $validate['pan_no'] ?? null,
            ];
        try {


            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                PurchaseParty::create($data);
            } else {
                PurchaseParty::on($branch->connection_name)->create($data);
            }

            return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Created Successfully!');
        } catch (Exception $ex) {
             return redirect()->route('purchase.party.index')->with('error', 'Failed to create Purchase Party. Please try again.');
        }
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
            'ledger_group' => 'required|string',
            'party_name' => 'required|string',
            'company_name' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'gst_heading' => 'nullable|string',
            'acc_no' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            // 'station' => 'nullable|string',
            'state' => 'nullable|string',
            'pincode' => 'nullable|string',
            'mobile_no' => 'nullable|string',
            'email' => 'nullable|string',
            'address' => 'nullable|string',
            'balancing_method' => 'nullable|string|max:255',
            // 'mail_to' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_no' => 'nullable|max:255',
            'designation' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'ledger_category' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pan_no' => 'nullable|string|max:255',
        ]);

        $data = [
            'ledger_group' => $validate['ledger_group'],
            'party_name' => $validate['party_name'],
            'company_name' => $validate['company_name'] ?? null,
            'gst_number' => $validate['gst_number'] ?? null,
            'gst_heading' => $validate['gst_heading'] ?? null,
            'mobile_no' => $validate['mobile_no'] ?? null,
            'email' => $validate['email'] ?? null,
            'address' => $validate['address'] ?? null,
            // 'station' => $validate['station'] ?? null,
            'state' => $validate['state'] ?? null,
            'acc_no' => $validate['acc_no'] ?? null,
            'ifsc_code' => $validate['ifsc_code'] ?? null,
            'pincode' => $validate['pincode'] ?? null,
            'balancing_method' => $validate['balancing_method'] ?? null,
            // 'mail_to' => $validate['mail_to'] ?? null,
            'contact_person' => $validate['contact_person'] ?? null,
            'contact_person_no' => $validate['contact_person_no'] ?? null,
            'designation' => $validate['designation'] ?? null,
            'note' => $validate['note'] ?? null,
            'ledger_category' => $validate['ledger_category'] ?? null,
            'country' => $validate['country'] ?? null,
            'pan_no' => $validate['pan_no'] ?? null,
        ];

        try {

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $party = PurchaseParty::findOrFail($id);
            } else {
                $party = PurchaseParty::on($branch->connection_name)->findOrFail($id);
            }

            $party->update($data);

            return redirect()->route('ledger.index')->with('success', 'Purchase Party Updated Successfully!');
        } catch (Exception $ex) {
             return redirect()->route('ledger.index')->with('error', 'Failed to update Purchase Party. Please try again.');
        }
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
            $search = $request->get('search', '');

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $parties = PurchaseParty::where('party_name', 'LIKE', "%{$search}%")
                    ->limit(10)
                    ->get();
            } else {
                $parties = PurchaseParty::on($branch->connection_name)
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
            // 'company_name' => 'required|string',
            // 'gst_number' => 'nullable|string',
            // 'acc_no' => 'nullable|string',
            // 'ifsc_code' => 'nullable|string',
            // 'station' => 'required|string',
            'pincode' => 'nullable',
            'party_phone' => 'nullable',
            'party_email' => 'nullable',
            'party_address' => 'nullable',
        ]);
        // dd($request->all());

        $data = [
            'party_name' => $validate['party_name'],
            // 'company_name' => $validate['party_name'],
            'gst_number' => $request->gst_number ?? "",    
            'acc_no' => $request->acc_no ?? "",
            'ifsc_code' => $request->ifsc_code ?? "",
            // 'station' => $validate['party_name'],
            'pincode' => $validate['pincode'] ?? "",
            'mobile_no' => $validate['party_phone'] ?? "",
            'email' => $validate['party_email'] ?? "",
            'address' => $validate['party_address'] ?? "",
        ];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $party = PurchaseParty::create($data);
        } else {
            $party = PurchaseParty::on($branch->connection_name)->create($data);
        }
        // return redirect()->route('purchase.party.index')->with('success', 'Purchase Party Created Successfully!');
        return response()->json([
            'success' => true,
            'message' => 'Purchase Party Created Successfully!',
            'data' => $party,
        ]);
    }
}
