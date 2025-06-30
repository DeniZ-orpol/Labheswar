<?php

namespace App\Http\Controllers;

use App\Models\HsnCode;
use Illuminate\Http\Request;
use Exception;
use App\Traits\BranchAuthTrait;

class HsnController extends Controller
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

        $hsns = HsnCode::on($branch->connection_name)->orderBy('id', 'desc')->paginate(10);
        return view('hsn.index', compact('hsns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hsn.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // try {
        // Check if user is logged in as branch
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        // Validate the request - including calculated fields from frontend
        $validate = $request->validate([
            'hsn_code' => 'required|string|max:255',
            'gst' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
        ]);
        // dd($request->all());
        $data = [
            'hsn_code' => $validate['hsn_code'],
            'gst' => $validate['gst'],
            'short_name' => $validate['short_name']
        ];
        // dd($data);
        HsnCode::on($branch->connection_name)->create($data);
        // dd(123);

        return redirect()->route('hsn_codes.index')->with('success', 'Hsn Code Created Successfully!');
        // dd($hsn);
        // } catch (Exception $ex) {
        //     // dd($ex->getMessage());
        //     // \log::error('Hsn Code store error: ' . $ex->getMessage());
        //     return redirect()->back()
        //         ->with('error', 'Error creating Hsn: ' . $ex->getMessage())
        //         ->withInput();
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];
        $branch = $auth['branch'];

        $hsn = HsnCode::on($branch->connection_name)->where('id', $id)->first();
        if (!$hsn) {
            return redirect()->route('hsn_codes.index')->with('error', 'Hsn Code not found.');
        }
        return view('hsn.show', compact('hsn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $hsn = HsnCode::on($branch->connection_name)->where('id', $id)->first();

        if (!$hsn) {
            return redirect()->route('hsn_codes.index')->with('error', 'Hsn Code not found.');
        }

        return view('hsn.edit', compact('hsn'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        $validate = $request->validate([
            'hsn_code' => 'required|string|max:255',
            'gst' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
        ]);
        // dd($request->all());
        $data = [
            'hsn_code' => $validate['hsn_code'],
            'gst' => $validate['gst'],
            'short_name' => $validate['short_name']
        ];
        // dd($data);
        HsnCode::on($branch->connection_name)->where('id', $id)->first();

        HsnCode::on($branch->connection_name)->where('id', $id)->update([
            'hsn_code' => $request->hsn_code,
            'gst' => $request->gst,
            'short_name' => $request->short_name,
            'updated_at' => now(),
        ]);

        return redirect()->route('hsn_codes.index')->with('success', 'Hsn Code Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $hsn = HsnCode::findOrFail($id);
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];

        HsnCode::on($branch->connection_name)->where('id', $id)->delete();

        // $hsn->delete();

        return redirect()->route('hsn_codes.index')->with('success', 'Hsn Code Deleted Successfully!');
    }

    public function modalstore(Request $request)
    {
        try {
            $auth = $this->authenticateAndConfigureBranch();
            $user = $auth['user'];
            $branch = $auth['branch'];

            $validate = $request->validate([
                'hsn_code' => 'required|string|max:255',
                'gst' => 'required|string|max:255',
                'short_name' => 'required|string|max:255',
            ]);

            $data = [
                'hsn_code' => $validate['hsn_code'],
                'gst' => $validate['gst'],
                'short_name' => $validate['short_name']
            ];

            // Superadmin → main DB, others → branch DB
            if ($user->role === 'superadmin') {
                $hsnCode = HsnCode::create($data);
            } else {
                $hsnCode = HsnCode::on($branch->connection_name)->create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'HSN Code created successfully',
                'data' => [
                    'id' => $hsnCode->id,
                    'hsn_code' => $hsnCode->hsn_code,
                    'gst' => $hsnCode->gst,
                    'short_name' => $hsnCode->short_name
                ]
            ]);
        } catch (\Exception $ex) {
            dd($ex->getMessage());
            \Log::error('HSN Code store error: ' . $ex->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating HSN Code: ' . $ex->getMessage()
            ], 500);
        }
    }
}
