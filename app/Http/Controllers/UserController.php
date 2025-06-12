<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('role', '!=', 'Superadmin')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // dd(13);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:users',
                'mobile' => 'nullable|string|max:15',
                'role' => 'required|string|in:Admin,Manager,Cashier',
                'dob' => 'nullable|date',
                'password' => 'nullable|string|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Check what is failing
        }


        // dd($request->all());

        $data = $request->only([
            'name',
            'email',
            'mobile',
            'role',
            'dob',
            'password',
        ]);

        // dd($data);

        // Hash the password if it's provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User added successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id); // or use route model binding
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validatedData = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'mobile'   => 'nullable|string|max:15',
                'role'     => 'required|string|in:Admin,Manager,Cashier',
                'dob'      => 'nullable|date',
                'password' => 'nullable|string|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Check what is failing
        }

        // Hash the password if provided
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Don't overwrite if not changing
        }

        $user->update($validatedData);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
