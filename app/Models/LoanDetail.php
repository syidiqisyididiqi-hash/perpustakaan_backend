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
        'qty'
    ];
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
