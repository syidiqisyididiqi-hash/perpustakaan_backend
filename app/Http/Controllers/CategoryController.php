<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => Category::latest()->get(),
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        $categori = Category::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $categori
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $categori)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $categori
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $categori)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string'
        ]);

        $categori->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $categori
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $categori)
    {
        $categori->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'data' => $categori
        ], 200);
    }
}
