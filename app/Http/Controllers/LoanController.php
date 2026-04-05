<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Fine;

class LoanController extends Controller
{
    /**
     * Display a listing of the loans
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => Loan::with([
                'user',
                'loanDetails.book',
                'loanDetails.fine'
            ])->latest()->get()
        ], 200);
    }

    /**
     * Store a newly created loan in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:loan_date',
            'details' => 'required|array|min:1',
            'details.*.book_id' => 'required|exists:books,id',
            'details.*.qty' => 'required|integer|min:1',
        ]);

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
                    throw new \Exception("Stok buku '{$book->title}' tidak mencukupi.");
                }

                $book->decrement('stock', $detail['qty']);

                $loan->loanDetails()->create([
                    'book_id' => $detail['book_id'],
                    'qty' => $detail['qty'],
                    'rack_code' => $book->rack_code,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Pinjaman berhasil dibuat',
                'data' => $loan->load([
                    'user',
                    'loanDetails.book',
                    'loanDetails.fine'
                ])
            ], 201);
        });
    }

    /**
     * Display the specified loan with details
     */
    public function show(Loan $loan)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil',
            'data' => $loan->load([
                'user',
                'loanDetails.book',
                'loanDetails.fine'
            ])
        ], 200);
    }

    /**
     * Update the specified loan in storage
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'due_date' => 'sometimes|date',
            'return_date' => 'sometimes|date'
        ]);

        DB::transaction(function () use ($request, $loan) {

            $loan->update($request->only(['user_id', 'due_date']));

            if ($request->has('return_date')) {

                $returnDate = Carbon::parse($request->return_date);
                $dueDate = Carbon::parse($loan->due_date);

                foreach ($loan->loanDetails as $detail) {

                    if (!$detail->returned_at) {

                        $book = Book::lockForUpdate()->find($detail->book_id);
                        $book->increment('stock', $detail->qty);

                        $detail->update([
                            'returned_at' => $request->return_date
                        ]);

                        if ($returnDate->gt($dueDate)) {

                            if (!Fine::where('loan_detail_id', $detail->id)->exists()) {

                                $daysLate = $dueDate->diffInDays($returnDate);
                                $dailyRate = 5000;

                                Fine::create([
                                    'loan_detail_id' => $detail->id,
                                    'overdue_days' => $daysLate,
                                    'daily_rate' => $dailyRate,
                                    'total_fine' => $daysLate * $dailyRate,
                                    'status' => 'unpaid'
                                ]);
                            }
                        }
                    }
                }

                $loan->update(['status' => 'returned']);
            }

        });

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $loan->load([
                'user',
                'loanDetails.book',
                'loanDetails.fine'
            ])
        ], 200);
    }

    /**
     * Return a specific book (partial return)
     */
    public function returnBook(LoanDetail $detail)
    {
        return DB::transaction(function () use ($detail) {

            if ($detail->returned_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buku sudah dikembalikan'
                ], 400);
            }

            $book = Book::lockForUpdate()->find($detail->book_id);
            $book->increment('stock', $detail->qty);

            $detail->update(['returned_at' => now()]);

            $loan = $detail->loan;
            if (!$loan->loanDetails()->whereNull('returned_at')->exists()) {
                $loan->update(['status' => 'returned']);
            }

            return response()->json([
                'status' => true,
                'message' => 'Buku berhasil dikembalikan'
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        return DB::transaction(function () use ($loan) {

            $loan->load('loanDetails.book');

            foreach ($loan->loanDetails as $detail) {
                if (!$detail->returned_at) {
                    $book = Book::lockForUpdate()->find($detail->book_id);
                    $book->increment('stock', $detail->qty);
                }
            }

            $loan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data dihapus & stok dikembalikan',
            ], 200);
        });
    }

    public function historyByUser($userId)
    {
        $loans = Loan::with([
            'loanDetails.book',
            'loanDetails.fine'
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'History peminjaman user berhasil diambil',
            'data' => $loans
        ], 200);
    }

    public function myLoans(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $loans = Loan::with([
            'loanDetails.book',
            'loanDetails.fine'
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $data = $loans->map(function ($loan) {

            return [
                'id' => $loan->id,
                'loan_date' => $loan->loan_date,
                'due_date' => $loan->due_date,
                'status' => $loan->status,

                'loan_details' => $loan->loanDetails->map(function ($detail) {

                    return [
                        'id' => $detail->id,
                        'book' => [
                            'title' => $detail->book->title ?? '-'
                        ],
                        'rack_code' => $detail->rack_code,
                        'returned_at' => $detail->returned_at,

                        // 🔥 FINE DATA
                        'fine_id' => $detail->fine->id ?? null,
                        'fine_total' => (int) ($detail->fine->total_fine ?? 0),
                        'fine_status' => $detail->fine->status ?? null,
                        'overdue_days' => (int) ($detail->fine->overdue_days ?? 0),
                    ];
                })
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}