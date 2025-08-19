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

        $perPage = 20;

        $search = $request->input('search');

        $query = (strtoupper($role->role_name) === 'SUPER ADMIN') 
            ? Category::query()
            : Category::on($branch->connection_name);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('position')->paginate($perPage);

        // Return AJAX response for infinite scroll
        if ($request->ajax()) {
            return view('categories.rows', compact('categories'))->render();
        }

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ?string $branch = null)
    {
        $auth = $this->authenticateAndConfigureBranch();

        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Create branch-specific directory
            $uploadPath = public_path('uploads/' . $branch->connection_name . '/category');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            // dd($request->all(), $branch, $auth, $role, $validate, $uploadPath);
            // Move file to branch-specific folder
            $file->move($uploadPath, $filename);

            // Store path as: branch_connection/products/filename.jpg
            $imagePath = 'uploads/' . $branch->connection_name . '/category/' . $filename;
        }

        $data = [
            'name' => $validate['name'],
            'type' => $validate['type'],
            'image' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            if (strtoupper($role->role_name) === 'SUPER ADMIN') {
                Category::insert($data);
            } else {
                Category::on($branch->connection_name)->insert($data);
            }
            return redirect()->route('categories.index')->with('success', 'Category created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('company.index')->with('error', 'Failed to create Category. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $category = Category::with('products')->where('id', $id)->first();
        } else {
            $category = Category::on($branch->connection_name)->with('products')->where('id', $id)->first();
        }

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $category = Category::where('id', $id)->first();
        } else {
            $category = Category::on($branch->connection_name)->where('id', $id)->first();
        }

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ?string $branchId = null)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];


        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $category = Category::where('id', $id)->first();
        } else {
            $category = Category::on($branch->connection_name)->where('id', $id)->first();
        }

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

        $data = [
            'name' => $request->name,
            'type' => $request->type,
            'image' => $imagePath,
            'updated_at' => now(),
        ];

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Category::where('id', $id)->update($data);
        } else {
            Category::on($branch->connection_name)->where('id', $id)->update($data);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
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

        // Delete the category from the current branch connection
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Category::where('id', $id)->delete();
        } else {
            Category::on($branch->connection_name)->where('id', $id)->delete();
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function modalstore(Request $request, ?string $branch = null)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $user = $auth['user'];
        $branch = $auth['branch'];
        $role = $auth['role'];


        // Check if category already exists (either in branch DB or master)
        $categoryName = trim($request->name);

        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            $existsInBranch = Category::where('name', $categoryName)->exists();
        } else {
            $existsInBranch = Category::on($branch->connection_name)->where('name', $categoryName)->exists();
        }

        if ($existsInBranch) {
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

        // Insert into selected branch or master
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            Category::insert($data);
        } else {
            Category::on($branch->connection_name)->insert($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => [
                'id' => Category::on($branch->connection_name)->where('name', $categoryName)->latest()->value('id'),
                'name' => $categoryName,
            ]
        ]);
    }

    public function reorder(Request $request)
    {
        $auth = $this->authenticateAndConfigureBranch();
        $branch = $auth['branch'];
        $role = $auth['role'];
        // Check if category already exists (either in branch DB or master)
         if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            foreach ($request->order as $item) {
                Category::where('id', $item['id'])->update(['position' => $item['position']]);
            }
         }
         else{
            foreach ($request->order as $item) {
                Category::on($branch->connection_name)->where('id', $item['id'])->update(['position' => $item['position']]);
            }
        }

        return response()->json(['message' => 'Order updated successfully.']);
    }
}
