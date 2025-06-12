<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $branches = Branch::all();
        $users = User::all();
        $branches = Branch::leftJoin('users', 'branches.branch_admin', '=', 'users.id')
            ->select(
                'branches.*',
                'users.name as admin_name'
            )
            ->get();
        return view('branch.index', compact('branches', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('branch.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'gst_no' => 'nullable|string|max:15',
            'branch_admin' => 'nullable|exists:users,id',
        ]);


        Branch::create([
            'user_id' => auth()->user()->id,
            'name' => $validate['name'],
            'location' => $validate['address'],
            'latitude' => $validate['latitude'],
            'longitude' => $validate['longitude'],
            'gst_no' => $validate['gst_no'],
            'branch_admin' => $validate['branch_admin'],
        ]);

        return redirect()->route('branch.index')->with('success', 'Branch created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $branch)
    {
        // $branch = Branch::findOrFail($branch); // or use route model binding
        $branch = Branch::leftJoin('users', 'branches.branch_admin', '=', 'users.id')
            ->where('branches.id', $branch)
            ->select(
                'branches.*',
                'users.name as admin_name'
            )
            ->first();
        return view('branch.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $branch)
    {
        $branch = Branch::findOrFail($branch);
        $branch->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
