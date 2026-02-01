<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use Illuminate\Http\Request;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => Fine::with(['loan'])->latest()->get()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'rack_code' => 'required|string',
            'overdue_days' => 'required|integer',
            'total_fine' => 'required|numeric|min:0',
            'status' => 'required|in:paid,unpaid',
        ]);

        $fine = Fine::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $fine->load('loan')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fine $fine)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $fine->load(['loan'])
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'rack_code' => 'required|string',
            'overdue_days' => 'required|integer',
            'total_fine' => 'required|numeric|min:0',
            'status' => 'required|in:paid,unpaid',
        ]);

        $fine->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $fine->load(['loan'])
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fine $fine)
    {
        $fine->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'data' => $fine
        ], 200);
    }
}
