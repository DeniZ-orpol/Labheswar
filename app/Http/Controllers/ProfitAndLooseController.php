<?php

namespace App\Http\Controllers;

use App\Models\ProfitAndLoose;
use App\Traits\BranchAuthTrait;
use Exception;
use Illuminate\Http\Request;

class ProfitAndLooseController extends Controller
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
            $profitsLooses = ProfitAndLoose::all();
        } else {
            $profitsLooses = ProfitAndLoose::on($branch->connection_name)->get();
        }

        return view('profitAndLoose.index', compact('profitsLooses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('profitAndLoose.create');
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

        try {
            $validated = $request->validate([
                'profit_loose' => 'required|string|max:255',
                'amount' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $data = [
                'amount' => $validated['amount'],
                'type' => $validated['profit_loose'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? null,
            ];

            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                ProfitAndLoose::create($data);
            } else {
                ProfitAndLoose::on($branch->connection_name)->create($data);
            }

            return redirect()->route('profit-loose.index')->with('success', 'Bank details created successfully.');

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProfitAndLoose $profitAndLoose)
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
            $profitLoose = ProfitAndLoose::where('id', $id)->firstOrFail();
        } else {
            $profitLoose = ProfitAndLoose::on($branch->connection_name)->where('id', $id)->firstOrFail();
        }

        return view('profitAndLoose.edit', compact('profitLoose'));
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

        try {
            $validated = $request->validate([
                'profit_loose' => 'required|string|max:255',
                'amount' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $data = [
                'amount' => $validated['amount'],
                'type' => $validated['profit_loose'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? null,
            ];

            // For Super Admin, you need to handle differently since $branch is a collection
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                $profitLoose = ProfitAndLoose::where('id', $id)->firstOrFail();
            } else {
                $profitLoose = ProfitAndLoose::on($branch->connection_name)->where('id', $id)->firstOrFail();
            }

            $profitLoose->update($data);

            return redirect()->route('profit-loose.index')->with('success', 'Profit/Loose updated successfully.');

        } catch (Exception $e) {
            dd('Error fetching ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProfitAndLoose $profitAndLoose)
    {
        //
    }
}
