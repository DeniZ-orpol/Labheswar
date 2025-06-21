<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::where('role_name', '!=', 'Super Admin')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.create'); // Ensure you have a view at resources/views/roles/create.blade.php
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'role_name' => 'required|string|max:255',
        ]);

        $roleName = $validatedData['role_name'];
        try {
            Role::create(['role_name' => $roleName]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Role creation failed.');
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);


        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'role_name' => 'required|string|max:255',
        ]);

        $roleName = $validatedData['role_name'];

        Role::where('id', $id)->update(['role_name' => $roleName]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $role = Role::where('id', $id)->first();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'role deleted successfully!');
    }
}
