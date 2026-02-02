<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoanDetail;
use Illuminate\Http\Request;

class LoanDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => LoanDetail::with(['loan', 'book'])->latest()->get()
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|integer|exists:loans,id',
            'book_id' => 'required|integer|exists:books,id',
            'rack_code' => 'required|string|max:20',
            'qty' => 'required|integer|min:1'
        ]);

        $loanDetail = LoanDetail::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $loanDetail->load(['loan', 'book'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanDetail $loanDetail)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $loanDetail->load(['loan', 'book'])
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanDetail $loanDetail)
    {
        $validated = $request->validate([
            'loan_id' => 'sometimes|integer|exists:loans,id',
            'book_id' => 'sometimes|integer|exists:books,id',
            'rack_code' => 'sometimes|string|max:20',
            'qty' => 'sometimes|integer|min:1'
        ]);

        $loanDetail->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $loanDetail->load(['loan', 'book'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanDetail $loanDetail)
    {
        $loanDetail->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'data' => $loanDetail
        ], 200);
    }
}
