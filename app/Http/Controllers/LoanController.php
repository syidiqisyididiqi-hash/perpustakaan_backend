<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => Loan::with(['user'])->latest()->get()
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
            'user_id' => 'required|exists:users,id',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:loan_date',
            'return_date' => 'nullable|date|after_or_equal:loan_date'
        ]);

        $validated['status'] = 'borrowed';

        $loan = Loan::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $loan->load(['user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $loan->load(['user'])
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
    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'loan_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:loan_date',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
            'status' => 'sometimes|string'
        ]);

        if ($request->has('return_date')) {
            if ($request->return_date) {
                $validated['status'] = 'returned';
            } else {
                $validated['status'] = 'borrowed';
            }
        }

        $loan->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $loan->load(['user'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'data' => $loan
        ], 200);
    }
}
