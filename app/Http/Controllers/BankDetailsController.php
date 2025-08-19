<?php

namespace App\Http\Controllers;

use App\Models\BankDetails;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class BankDetailsController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];

        try {
            // Decide query based on role
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $query = BankDetails::query();
            } else {
                $query = BankDetails::on($branch->connection_name);
            }

            // Paginate for infinite scroll (10 per load)
            $banks = $query->orderBy('id', 'desc')->paginate(10);

            // Check if AJAX request for scroll
            if ($request->ajax()) {
                return view('bank.rows', compact('banks'))->render();
            }

            // First page normal load
            return view('bank.index', compact('banks'));

        } catch (Exception $e) {
            dd('Error fetching Banks: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank.create');
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

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:11',
            'opening_balance' => 'nullable|numeric',
            'close_on' => 'nullable',
        ]);

        $data = [
            'bank_name' => $validated['bank_name'],
            'account_no' => $validated['account_no'],
            'ifsc_code' => $validated['ifsc_code'],
            'close_on' => $validated['close_on'] ?? null,
            'opening_bank_balance' => $validated['opening_balance'] ?? 0
        ];
        try {
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                BankDetails::create($data);
            } else {
                BankDetails::on($branch->connection_name)->create($data);
            }

            return redirect()->route('bank.index')->with('success', 'Bank details created successfully.');
        } catch (Exception $e) {
            return redirect()->route('bank.index')->with('error', 'Failed to create Bank details. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BankDetails $bankDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $bank = BankDetails::where('id', $id)->firstOrFail();
        } else {
            $bank = BankDetails::on($branch->connection_name)->where('id', $id)->firstOrFail();
        }

        return view('bank.edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:11',
            'opening_balance' => 'nullable|numeric',
            'close_on' => 'nullable',
        ]);

        $data = [
            'bank_name' => $validated['bank_name'],
            'account_no' => $validated['account_no'],
            'ifsc_code' => $validated['ifsc_code'],
            'close_on' => $validated['close_on'] ?? null,
            'opening_bank_balance' => $validated['opening_balance'] ?? 0
        ];

        try {
            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $bank = BankDetails::where('id', $id)->firstOrFail();
            } else {
                $bank = BankDetails::on($branch->connection_name)->where('id', $id)->firstOrFail();
            }

            $bank->update($data);

            return redirect()->route('bank.index')->with('success', 'Bank details Updated Successfully!');
        } catch (Exception $e) {
            return redirect()->route('bank.index')->with('error', 'Failed to update bank details. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankDetails $bankDetails)
    {
        //
    }
}
