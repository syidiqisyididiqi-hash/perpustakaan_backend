<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoanDetail;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'book_id' => 'required|exists:books,id',
            'qty' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($validated) {

            $book = Book::lockForUpdate()->findOrFail($validated['book_id']);

            if ($book->stock < $validated['qty']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stok tidak cukup'
                ], 400);
            }

            $book->decrement('stock', $validated['qty']);

            $loanDetail = LoanDetail::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Detail berhasil ditambahkan & stok berkurang',
                'data' => $loanDetail->load(['loan', 'book'])
            ], 201);
        });
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanDetail $loanDetail)
    {
        $validated = $request->validate([
            'loan_id' => 'sometimes|exists:loans,id',
            'book_id' => 'sometimes|exists:books,id',
            'qty' => 'sometimes|integer|min:1'
        ]);

        try {
            return DB::transaction(function () use ($validated, $loanDetail) {

                if (isset($validated['book_id']) && $validated['book_id'] != $loanDetail->book_id) {

                    $loanDetail->book()->increment('stock', $loanDetail->qty);

                    $newBook = Book::lockForUpdate()->findOrFail($validated['book_id']);

                    $newQty = $validated['qty'] ?? $loanDetail->qty;

                    if ($newBook->stock < $newQty) {
                        throw new \Exception("Stok buku baru tidak mencukupi");
                    }

                    $newBook->decrement('stock', $newQty);
                } elseif (isset($validated['qty']) && $validated['qty'] != $loanDetail->qty) {

                    $book = Book::lockForUpdate()->findOrFail($loanDetail->book_id);

                    $selisih = $validated['qty'] - $loanDetail->qty;

                    if ($selisih > 0) {
                        if ($book->stock < $selisih) {
                            throw new \Exception("Stok tidak mencukupi");
                        }
                        $book->decrement('stock', $selisih);
                    } else {
                        $book->increment('stock', abs($selisih));
                    }
                }

                $loanDetail->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Detail berhasil diupdate',
                    'data' => $loanDetail->load(['loan', 'book'])
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanDetail $loanDetail)
    {
        return DB::transaction(function () use ($loanDetail) {

            $book = Book::lockForUpdate()->find($loanDetail->book_id);

            if ($book) {
                $book->increment('stock', $loanDetail->qty);
            }

            $loanDetail->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data detail dihapus & stok dikembalikan',
            ], 200);
        });
    }
}