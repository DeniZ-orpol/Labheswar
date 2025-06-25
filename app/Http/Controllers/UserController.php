<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchUsers;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['role_data','branch'])
            ->whereHas('role_data', function ($query) {
                $query->where('role_name', '!=', 'Super Admin');
            })->where('is_active', '1')
            ->orderByDesc('id')->get();

        return view('users.index', compact('users'));
    }

    // private function getAllUsersFromAllDatabases()
    // {
    //     $allUsers = collect();

    //     // Get all branch users only
    //     $branches = Branch::all();
        
    //     foreach ($branches as $branch) {
    //         try {
    //             $databaseName = $branch->getDatabaseName();
                
    //             $branchUsers = BranchUsers::forDatabase($databaseName)->get();

    //             $formattedBranchUsers = $branchUsers->map(function ($user) use ($branch, $databaseName) {

    //                 $role = Role::forDatabase($databaseName)->find($user->role_id);
                    
    //                 return (object) [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'email' => $user->email,
    //                     'role' => $role, // This will be the role object if with('role') works, or role field if not
    //                     'mobile' => $user->mobile,
    //                     'dob' => $user->dob ? $user->dob->format('d-m-Y') : null,
    //                     'is_active' => $user->is_active,
    //                     'database_type' => 'branch',
    //                     'branch_name' => $branch->name,
    //                     'branch_id' => $branch->id,
    //                     'branch_code' => $branch->code,
    //                     'created_at' => $user->created_at,
    //                 ];
    //             });

    //             $allUsers = $allUsers->merge($formattedBranchUsers);
    //         } catch (\Exception $e) {
    //             // Skip if branch database connection fails
    //             \Log::warning("Could not access branch {$branch->name}: " . $e->getMessage());
    //         }
    //     }

    //     return $allUsers->sortByDesc('created_at');
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('role_name', '!=', 'Super Admin')->get();
        $branches = Branch::all();

        return view('users.create', compact('roles', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // dd($request->all()); // Debugging line to check the request data
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'mobile' => 'nullable|string|max:15',
                'role_id' => 'required|integer',
                'dob' => 'nullable|date',
                'password' => 'required|nullable|string|min:6',
                'branch_id' => 'required|exists:branches,id',
            ]);
        } catch (Exception $e) {
            dd($e->getMessage()); // Check what is failing
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return redirect()->back()
                ->withErrors(['email' => 'Email already exists in this branch'])
                ->withInput();
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role_id' => $request->role_id,
            'dob' => $request->dob,
            'branch_id' => $request->branch_id,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        // Create user in selected branch database
        User::create($userData);

        return redirect()->route('users.index')->with('success', 'User added successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $branch = Branch::where('id', $user->branch_id)->first();
        $user->role = Role::find($user->role_id);

        return view('users.show', compact('user', 'branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $branch = Branch::where('id', $user->branch_id)->first();
        // Get roles from this branch
        $roles = Role::where('role_name', '!=', 'Super Admin')->get();

        return view('users.edit', compact('user', 'roles'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all()); // Debugging line to check the request data
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|nullable|email|max:255',
                'mobile' => 'nullable|string|max:15',
                'role_id' => 'required|integer',
                'dob' => 'nullable|date',
                'password' => 'nullable|string|min:6',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Check what is failing
        }
        // $branch = Branch::findOrFail($branchId);
        $user = User::findOrFail($id);

        $existingUser = User::where('email', $request->email)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUser) {
            return redirect()->back()
                ->withErrors(['email' => 'Email already exists in this branch'])
                ->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role_id' => $request->role_id,
            'dob' => $request->dob,
        ];
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $branch = Branch::findOrFail($branchId);
        $user = User::findOrFail($id);

        // $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
