<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'book_id',
        'rack_code',
        'qty',
        'returned_at'
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

 
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }

    public function isReturned(): bool
    {
        return !is_null($this->returned_at);
    }

    public function isOverdue(): bool
    {
        if (!$this->returned_at) {
            return false;
        }

        return $this->returned_at->gt($this->loan->due_date);
    }

    public function overdueDays(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return $this->returned_at->diffInDays($this->loan->due_date);
    }
}