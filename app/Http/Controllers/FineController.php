<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\LoanDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fines = Fine::with([
            'loanDetail.loan.user',
            'loanDetail.book'
        ])->latest()->paginate(10);

        $data = collect($fines->items())->map(function ($fine) {
            $fine->total_fine = (int) $fine->total_fine;
            return $fine;
        });

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $data,
            'pagination' => [
                'current_page' => $fines->currentPage(),
                'last_page' => $fines->lastPage(),
                'per_page' => $fines->perPage(),
                'total' => $fines->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_detail_id' => 'required|exists:loan_details,id',
            'status' => 'required|in:paid,unpaid'
        ]);

        $loanDetail = LoanDetail::with('loan.user', 'book')
            ->findOrFail($validated['loan_detail_id']);

        if (Fine::where('loan_detail_id', $loanDetail->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Denda sudah pernah dibuat'
            ], 422);
        }

        if (!$loanDetail->returned_at) {
            return response()->json([
                'status' => false,
                'message' => 'Buku belum dikembalikan'
            ], 422);
        }

        $returnDate = Carbon::parse($loanDetail->returned_at);
        $dueDate = Carbon::parse($loanDetail->loan->due_date);

        if ($returnDate->lte($dueDate)) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak terlambat'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $daysLate = $dueDate->diffInDays($returnDate);
            $dailyRate = 5000;

            $total = (int) ($daysLate * $dailyRate);

            $fine = Fine::create([
                'loan_detail_id' => $loanDetail->id,
                'overdue_days' => $daysLate,
                'daily_rate' => $dailyRate,
                'total_fine' => $total,
                'status' => $validated['status'],
            ]);

            DB::commit();

            $fine->total_fine = (int) $fine->total_fine;

            return response()->json([
                'status' => true,
                'message' => 'Denda berhasil dibuat',
                'data' => $fine->load('loanDetail.loan.user', 'loanDetail.book')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat denda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fine $fine)
    {
        $fine->load([
            'loanDetail.book',
            'loanDetail.loan.user'
        ]);

        $fine->total_fine = (int) $fine->total_fine;

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $fine
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid'
        ]);

        $fine->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : null
        ]);

        $fine->total_fine = (int) $fine->total_fine;

        return response()->json([
            'status' => true,
            'message' => 'Status denda berhasil diupdate',
            'data' => $fine->load('loanDetail.loan.user', 'loanDetail.book')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fine $fine)
    {
        $fine->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}