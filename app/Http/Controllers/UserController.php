<?php

namespace App\Http\Controllers;

use App\Models\Role;
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
        $users = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('role_name', '!=', 'Superadmin');
            })
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('role_name', '!=', 'Superadmin')->get();
        return view('users.create', compact('roles'));
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
                'role_id' => 'required|exists:roles,id',
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
            'role_id',
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
        $roles = Role::where('role_name', '!=', 'Superadmin')->select('id', 'role_name')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */ public function update(Request $request, User $user)
    {
        try {
            $validatedData = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'mobile'   => 'nullable|string|max:15',
                'role_id'  => 'required|exists:roles,id',
                'dob'      => 'nullable|date',
                'password' => 'nullable|string|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Check what is failing
        }

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
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
