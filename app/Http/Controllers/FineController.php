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
            'overdue_days' => 'required|integer|min:0',
            'status' => 'required|in:paid,unpaid',
            'total_fine' => 'nullable|numeric'
        ]);

        $loan = \App\Models\Loan::with('loanDetails.book')->findOrFail($validated['loan_id']);

        $rackCode = $loan->loanDetails->first()?->book?->rack_code ?? '-';

        $amount = $validated['total_fine'] ?? 0;

        if (!$amount) {
            $today = now();
            $returnDate = \Carbon\Carbon::parse($loan->return_date);

            if ($today->greaterThan($returnDate)) {
                $daysLate = $returnDate->diffInDays($today);
                $amount = $daysLate * 5000;
            }
        }

        $fine = \App\Models\Fine::create([
            'loan_id' => $validated['loan_id'],
            'rack_code' => $rackCode,
            'overdue_days' => $validated['overdue_days'],
            'total_fine' => $amount,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Denda berhasil dibuat',
            'data' => $fine->load('loan.user')
        ]);
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
            'overdue_days' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:paid,unpaid',
        ]);

        $fine->update([
            'overdue_days' => $validated['overdue_days'],
            'status' => $validated['status'],
        ]);

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
