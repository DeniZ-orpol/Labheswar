<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Traits\BranchAuthTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
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

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            // Get the selected branch ID from request
            $selectedBranchId = $request->get('branch_id');
            $availableBranches = $branch; // All active branches for dropdown

            if (!$selectedBranchId) {
                // No branch selected - return empty collection with message
                $categories = collect();
                $selectedBranch = null;
                $showNoBranchMessage = true;

                return view('categories.index', compact(
                    'categories',
                    'role',
                    'availableBranches',
                    'selectedBranch',
                    'showNoBranchMessage'
                ));
            }

            // Find the selected branch
            $selectedBranch = $branch->where('id', $selectedBranchId)->first();

            if (!$selectedBranch) {
                // Invalid branch ID - redirect with error
                return redirect()->route('categories.index')
                    ->with('error', 'Invalid branch selected');
            }

            // Configure connection for selected branch
            configureBranchConnection($selectedBranch);

            // Get categories for the selected branch with pagination
            $categories = Category::on($selectedBranch->connection_name)
                ->orderByDesc('id')
                ->paginate(10);

            // Append branch_id to pagination links
            $categories->appends($request->query());

            $showNoBranchMessage = false;

            return view('categories.index', compact(
                'categories',
                'role',
                'availableBranches',
                'selectedBranch',
                'showNoBranchMessage'
            ));
        } else {
            $categories = Category::on($branch->connection_name)->orderBy('created_at', 'desc')->paginate(10);
        }


        return view('categories.index', compact('categories', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branch = Branch::all();
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        return view('categories.create', compact('branch', 'role'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ?string $branch = null)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;

            if (!$branchId) {
                return redirect()->back()->with('error', 'Branch ID is required for Super Admin.');
            }

            $branch = Branch::findOrFail($branchId);
            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Create branch-specific directory
            $uploadPath = public_path('uploads/' . $branch->connection_name . '/category');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file to branch-specific folder
            $file->move($uploadPath, $filename);

            // Store path as: branch_connection/products/filename.jpg
            $imagePath = 'uploads/' . $branch->connection_name . '/category/' . $filename;
        }
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('category', 'public');
        // }

        $data = [
            'name' => $request->name,
            'image' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into branch DB
        Category::on($branch->connection_name)->insert($data);

        // Insert into labheswar (master) only if category name not exists
        $exists = Category::where('name', $request->name)->exists();

        if (!$exists) {
            Category::insert($data);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }



    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;
            $branch = Branch::findOrFail($branchId);

            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $category = Category::on($branch->connection_name)->where('id', $id)->first();
        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }
        return view('categories.show', compact('category', 'branch', 'role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;
            $branch = Branch::findOrFail($branchId);

            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $category = Category::on($branch->connection_name)->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }

        return view('categories.edit', compact('category', 'branch', 'role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ?string $branchId = null)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            if (!$branchId) {
                return redirect()->back()->with('error', 'Branch ID is required for Super Admin.');
            }

            $branch = Branch::findOrFail($branchId);
            configureBranchConnection($branch);
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $category = Category::on($branch->connection_name)->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                $oldImagePath = public_path($category->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Create branch-specific directory
            $uploadPath = public_path('uploads/' . $branch->connection_name . '/category');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file to branch-specific folder
            $file->move($uploadPath, $filename);

            // Store path as: branch_connection/products/filename.jpg
            $imagePath = 'uploads/' . $branch->connection_name . '/category/' . $filename;
        }
        // if ($request->hasFile('image')) {
        //     // Optionally delete old image here if needed
        //     $imagePath = $request->file('image')->store('category', 'public');
        // }

        Category::on($branch->connection_name)->where('id', $id)->update([
            'name' => $request->name,
            'image' => $imagePath,
            'updated_at' => now(),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branch = Branch::all();
        } else {
            // Normal user — get branch from auth
            $branch = $auth['branch'];
        }

        // Delete the category from the current branch connection
        Category::on($branch->connection_name)->where('id', $id)->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function modalstore(Request $request, ?string $branch = null)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $role = $auth['role'];

        if (strtolower($role->role_name) === 'super admin') {
            $branchId = $request->branch;

            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch ID is required for Super Admin.',
                ], 422);
            }

            $branch = Branch::findOrFail($branchId);
            configureBranchConnection($branch);
        } else {
            $branch = $auth['branch'];
        }

        // Check if category already exists (either in branch DB or master)
        $categoryName = trim($request->name);

        $existsInBranch = Category::on($branch->connection_name)->where('name', $categoryName)->exists();
        $existsInMaster = Category::where('name', $categoryName)->exists();

        if ($existsInBranch || $existsInMaster) {
            return response()->json([
                'success' => false,
                'message' => 'Category already exists.',
            ], 409);
        }

        // Upload image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            $uploadPath = public_path('uploads/' . $branch->connection_name . '/category');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $imagePath = 'uploads/' . $branch->connection_name . '/category/' . $filename;
        }

        $data = [
            'name' => $categoryName,
            'image' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into both branch and master
        Category::on($branch->connection_name)->insert($data);
        Category::insert($data); // master DB

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => [
                'id' => Category::on($branch->connection_name)->where('name', $categoryName)->latest()->value('id'),
                'name' => $categoryName,
            ]
        ]);
    }
}
