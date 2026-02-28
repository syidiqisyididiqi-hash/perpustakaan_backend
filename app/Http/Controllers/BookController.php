<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with('category')
            ->select('id', 'category_id', 'isbn', 'title', 'author', 'publisher', 'published_year', 'stock', 'rack_code', 'cover', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $books
        ], 200);
    }

    /**
     * Dropdown data for books 
     */
    public function dropdown()
    {
        $books = Book::select('id', 'title', 'rack_code', 'stock')
            ->where('stock', '>', 0)
            ->orderBy('title')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $books
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'isbn' => 'required|string|max:100|unique:books,isbn',
            'title' => 'required|string|max:150',
            'author' => 'required|string|max:100',
            'publisher' => 'required|string|max:100',
            'published_year' => 'required|integer',
            'stock' => 'required|integer|min:0',
            'rack_code' => 'required|string|max:20',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $book->load('category')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $book->load('category')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'isbn' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('books', 'isbn')->ignore($book->id)
            ],
            'title' => 'sometimes|string|max:150',
            'author' => 'sometimes|string|max:100',
            'publisher' => 'sometimes|string|max:100',
            'published_year' => 'sometimes|integer',
            'stock' => 'sometimes|integer|min:0',
            'rack_code' => 'sometimes|string|max:20',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $book->load('category')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        DB::transaction(function () use ($book) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $book->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}