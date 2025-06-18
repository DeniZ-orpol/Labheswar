<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchConnection = session('branch_connection');

        $categories = DB::connection($branchConnection)->table('categories')->orderBy('created_at', 'desc')->get();

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
    public function store(Request $request)
    {
        $branchConnection = session('branch_connection');
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('category', 'public');
        }

        $data = [
            'name' => $request->name,
            'image' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into branch DB
        DB::connection($branchConnection)->table('categories')->insert($data);

        // Insert into labheswar (master) only if category name not exists
        $exists = DB::connection('master')->table('categories')->where('name', $request->name)->exists();

        if (!$exists) {
            DB::connection('master')->table('categories')->insert($data);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!session('branch_connection')) {
            return redirect()->route('categories.index')->with('error', 'No branch connection found.');
        }
        $branchConnection = session('branch_connection');
        $category = DB::connection($branchConnection)->table('categories')->where('id', $id)->first();
        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $branchConnection = session('branch_connection');

        $category = DB::connection($branchConnection)->table('categories')->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $branchConnection = session('branch_connection');

        $category = DB::connection($branchConnection)->table('categories')->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'Category not found.');
        }

        $imagePath = $category->image;

        if ($request->hasFile('image')) {
            // Optionally delete old image here if needed
            $imagePath = $request->file('image')->store('category', 'public');
        }

        DB::connection($branchConnection)->table('categories')->where('id', $id)->update([
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
        $branchConnection = session('branch_connection');

        // Delete the category from the current branch connection
        DB::connection($branchConnection)->table('categories')->where('id', $id)->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
