<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\BranchAuthTrait;
use Exception;

class PackagingController extends Controller
{
    use BranchAuthTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        // Use correct DB connection based on role
        $query = (strtoupper($role->role_name) === 'SUPER ADMIN')
            ? Packaging::query()
            : Packaging::on($branch->connection_name);

        // Get only one packaging entry per group_id (to display group list)
        $packagings = $query
            ->select('group_id', 'group')
            ->distinct()
            ->orderByDesc('group_id')
            ->paginate(10);

        if ($request->ajax()) {
            return view('packaging.rows', compact('packagings'))->render();
        }

        return view('packaging.index', compact('packagings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('packaging.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $validated = $request->validate([
            'group' => 'required',

            // Ingredients fields validation
            'product_id' => 'array',
            'product_id.*' => 'required',
            'weight_from' => 'array',
            'weight_from.*' => 'required',
            'weight_to' => 'array',
            'weight_to.*' => 'required',
        ]);

        $latestGroupId = (strtoupper($role->role_name) === 'SUPER ADMIN')
            ? Packaging::max('group_id')
            : Packaging::on($branch->connection_name)->max('group_id');

        $newGroupId = ($latestGroupId ?? 0) + 1;

        // Save each row conditionally based on role
        $count = count($validated['product_id']);

        for ($i = 0; $i < $count; $i++) {
            $data = [
                'group_id'    => $newGroupId,
                'group'       => $validated['group'],
                'product_id'  => $validated['product_id'][$i],
                'weight_from' => $validated['weight_from'][$i],
                'weight_to'   => $validated['weight_to'][$i],
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Packaging::create($data);
            } else {
                Packaging::on($branch->connection_name)->create($data);
            }
        }

        return redirect()->route('packaging.index')
            ->with('success', 'Packaging created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Packaging $packaging)
    {
        return view('packaging.show', compact('packaging'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($group_id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $role = $auth['role'];
        $branch = $auth['branch'];

        $query = (strtoupper($role->role_name) === 'SUPER ADMIN')
            ? Packaging::query()
            : Packaging::on($branch->connection_name);

        $packagings = $query->where('group_id', $group_id)->with('product')->get();

        if ($packagings->isEmpty()) {
            return redirect()->route('packaging.index')
                ->with('error', 'Packaging group not found.');
        }

        return view('packaging.edit', compact('packagings', 'group_id'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $group_id)
    {

        $auth = $this->authenticateAndConfigureBranch();
        $role = $auth['role'];
        $branch = $auth['branch'];
        
        $validated = $request->validate([
            'group' => 'required',
            'product_id' => 'array',
            'product_id.*' => 'required',
            'weight_from' => 'array',
            'weight_from.*' => 'required',
            'weight_to' => 'array',
            'weight_to.*' => 'required',
        ]);
        
        // Use correct connection
        $query = (strtoupper($role->role_name) === 'SUPER ADMIN')
            ? Packaging::where('group_id', $group_id)
            : Packaging::on($branch->connection_name)->where('group_id', $group_id);

        // Delete old entries
        $query->delete();

        // Re-insert updated entries
        $count = count($validated['product_id']);

        for ($i = 0; $i < $count; $i++) {
            $data = [
                'group_id'    => $group_id,
                'group'       => $validated['group'],
                'product_id'  => $validated['product_id'][$i],
                'weight_from' => $validated['weight_from'][$i],
                'weight_to'   => $validated['weight_to'][$i],
            ];

            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Packaging::create($data);
            } else {
                Packaging::on($branch->connection_name)->create($data);
            }
        }

        return redirect()->route('packaging.index')
            ->with('success', 'Packaging group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($group_id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $role = $auth['role'];
        $branch = $auth['branch'];

        try {
            $query = (strtoupper($role->role_name) === 'SUPER ADMIN')
                ? Packaging::where('group_id', $group_id)
                : Packaging::on($branch->connection_name)->where('group_id', $group_id);

            $deletedCount = $query->delete();

            if ($deletedCount > 0) {
                return redirect()->route('packaging.index')
                    ->with('success', 'Packaging group deleted successfully.');
            } else {
                return redirect()->route('packaging.index')
                    ->with('error', 'No records found for this group.');
            }

        } catch (\Throwable $e) {
            return redirect()->route('packaging.index')
                ->with('error', 'Error deleting packaging group.');
        }
    }
}
