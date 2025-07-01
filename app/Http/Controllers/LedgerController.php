<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    use BranchAuthTrait;

    public function getLedgersByType()
    {
        return view('ledger.list');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $ledgers = Ledger::where('type', $type)->get();
            } else {
                $ledgers = Ledger::on($branch->connection_name)
                    ->where('type', $type)
                    ->get();
            }
            return view('ledger.index', ['type' => $type], compact('ledgers'));

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $type = $request->query('type');

        try {
            return view('ledger.create', ['type' => $type]);

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $type = $request->query('type');

        try {
            $validated = $request->validate([
                'ledger_name' => 'nullable|string|max:255',
                'pin_code' => 'nullable|numeric',
                'email' => 'nullable|email|max:255',
                'phone_no' => 'nullable|numeric',
                'station' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'balancing_method' => 'nullable|string|max:255',
                'mail_to' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'contact_person' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:255',
                'gst_no' => 'nullable|string',
                'gst_heading' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:255',
                'ledger_category' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'pan_no' => 'nullable|string|max:255',
            ]);

            $data = [
                'name' => $validated['ledger_name'] ?? null,
                'station' => $validated['station'] ?? null,
                'acc_group' => $validated['acc_group'] ?? $type,
                'balancing_method' => $validated['balancing_method'] ?? null,
                'mail_to' => $validated['mail_to'] ?? null,
                'address' => $validated['address'] ?? null,
                'pin_code' => $validated['pin_code'] ?? null,
                'email' => $validated['email'] ?? null,
                'website' => $validated['website'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'phone_no' => $validated['phone_no'] ?? null,
                'gst_no' => $validated['gst_no'] ?? null,
                'state' => $validated['state'] ?? null,
                'gst_heading' => $validated['gst_heading'] ?? null,
                'note' => $validated['note'] ?? null,
                'ledger_category' => $validated['ledger_category'] ?? null,
                'country' => $validated['country'] ?? null,
                'pan_no' => $validated['pan_no'] ?? null,
                'type' => $type,
            ];

            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Ledger::create($data);
            } else {
                Ledger::on($branch->connection_name)->create($data);
            }
            return redirect()->route('ledger.index', ['type' => $type]);

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
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
    public function edit(Request $request, $id)
    {
        $type = $request->query('type');
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $ledger = Ledger::where('type', $type)->where('id', $id)->firstOrFail();
            } else {
                $ledger = Ledger::on($branch->connection_name)
                    ->where('type', $type)
                    ->where('id', $id)
                    ->firstOrFail();
            }
            return view('ledger.edit', ['type' => $type], compact('ledger'));

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
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

        $type = $request->query('type');


        try {
            $validated = $request->validate([
                'ledger_name' => 'nullable|string|max:255',
                'pin_code' => 'nullable|numeric',
                'email' => 'nullable|email|max:255',
                'phone_no' => 'nullable|numeric',
                'station' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'balancing_method' => 'nullable|string|max:255',
                'mail_to' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'contact_person' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:255',
                'gst_no' => 'nullable|string',
                'gst_heading' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:255',
                'ledger_category' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'pan_no' => 'nullable|string|max:255',
            ]);

            $data = [
                'name' => $validated['ledger_name'] ?? null,
                'station' => $validated['station'] ?? null,
                'acc_group' => $validated['acc_group'] ?? $type,
                'balancing_method' => $validated['balancing_method'] ?? null,
                'mail_to' => $validated['mail_to'] ?? null,
                'address' => $validated['address'] ?? null,
                'pin_code' => $validated['pin_code'] ?? null,
                'email' => $validated['email'] ?? null,
                'website' => $validated['website'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'phone_no' => $validated['phone_no'] ?? null,
                'gst_no' => $validated['gst_no'] ?? null,
                'state' => $validated['state'] ?? null,
                'gst_heading' => $validated['gst_heading'] ?? null,
                'note' => $validated['note'] ?? null,
                'ledger_category' => $validated['ledger_category'] ?? null,
                'country' => $validated['country'] ?? null,
                'pan_no' => $validated['pan_no'] ?? null,
                'type' => $type,
            ];
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $ledger = Ledger::where('type', $type)->where('id', $id)->firstOrFail();

                $ledger->update($data);
            } else {
                $ledger = Ledger::on($branch->connection_name)
                    ->where('type', $type)
                    ->where('id', $id)
                    ->firstOrFail();

                $ledger->update($data);
            }

            return redirect()->route('ledger.index', ['type' => $type]);

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
