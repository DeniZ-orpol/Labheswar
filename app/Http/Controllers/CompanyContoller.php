<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Traits\BranchAuthTrait;
use Illuminate\Http\Request;

class CompanyContoller extends Controller
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
            $companies = Company::orderBy('id', 'desc')->paginate(10);
        } else {
            $companies = Company::on($branch->connection_name)->orderBy('id', 'desc')->paginate(10);
        }

        return view('company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company.create');
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

        // Validate the request - including calculated fields from frontend
        $validate = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        // dd($request->all());
        $data = [
            'name' => $validate['name'],
        ];
        
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Company::create($data);
        } else {
            Company::on($branch->connection_name)->create($data);
        }

        return redirect()->route('company.index')->with('success', 'company Created Successfully!');
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
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $companies = Company::where('id', $id)->first();
        } else {
            $companies = Company::on($branch->connection_name)->where('id', $id)->first();
        }

        if (!$companies) {
            return redirect()->route('company.index')->with('error', 'Company not found.');
        }

        return view('company.edit', compact('companies'));
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
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $validate['name'],
        ];
        
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $company = Company::where('id', $id)->first();
        } else {
            $company = Company::on($branch->connection_name)->where('id', $id)->first();
        }

        $company->update($data);

        return redirect()->route('company.index')->with('success', 'Company Updated Successfully!');
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

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Company::where('id', $id)->delete();
        } else {
            Company::on($branch->connection_name)->where('id', $id)->delete();
        }

        return redirect()->route('company.index')->with('success', 'Company Deleted Successfully!');
    }

    public function modalstore(Request $request)
    {
        // try {
        // Check if user is logged in as branch
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        // Validate the request - including calculated fields from frontend
        $validate = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        // dd($request->all());
        $data = [
            'name' => $validate['name'],
        ];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $company = Company::create($data);
        } else {
            $company = Company::on($branch->connection_name)->create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'HSN Code created successfully',
            'data' => [
                'id' => $company->id,
                'name' => $company->name,
            ]
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $companies = Company::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->get();
        // dd($companies);
        return response()->json($companies);
    }
}
