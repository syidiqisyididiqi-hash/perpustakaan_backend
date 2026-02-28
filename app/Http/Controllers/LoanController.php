<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Book;
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
            'data' => Loan::with(['user', 'loanDetails.book'])->latest()->get()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:loan_date',
            'details' => 'required|array',
            'details.*.book_id' => 'required|exists:books,id',
            'details.*.qty' => 'required|integer|min:1'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $loan = Loan::create([
                    'user_id' => $request->user_id,
                    'loan_date' => $request->loan_date,
                    'due_date' => $request->due_date,
                    'status' => 'borrowed'
                ]);

                foreach ($request->details as $detail) {
                    $book = Book::lockForUpdate()->findOrFail($detail['book_id']);

                    if ($book->stock < $detail['qty']) {
                        throw new \Exception("Stok buku '{$book->title}' tidak mencukupi. Sisa stok: {$book->stock}");
                    }

                    $book->decrement('stock', $detail['qty']);

                    $loan->loanDetails()->create($detail);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Data ditambahkan & stok berkurang',
                    'data' => $loan->load(['user', 'loanDetails.book'])
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $loan->load(['user', 'loanDetails.book'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        return DB::transaction(function () use ($request, $loan) {
            if ($request->status === 'returned' && $loan->status !== 'returned') {
                foreach ($loan->loanDetails as $detail) {
                    $detail->book()->increment('stock', $detail->qty);
                }
            }

            $loan->update($request->only(['status', 'due_date', 'user_id']));

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $loan->load(['user', 'loanDetails.book'])
            ], 200);
        });
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Loan $loan)
    {
        return DB::transaction(function () use ($loan) {
            if ($loan->status !== 'returned') {
                foreach ($loan->loanDetails as $detail) {
                    $detail->book()->increment('stock', $detail->qty);
                }
            }

            $loan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data dihapus & stok dikembalikan',
            ], 200);
        });
    }
}
