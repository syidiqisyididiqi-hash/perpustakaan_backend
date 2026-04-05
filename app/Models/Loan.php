<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_date',
        'due_date',
        'status'
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanDetails()
    {
        return $this->hasMany(LoanDetail::class);
    }


    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }


    public function isOverdue(): bool
    {
        return now()->gt($this->due_date)
            && $this->loanDetails()->whereNull('returned_at')->exists();
    }
    public function isFullyReturned(): bool
    {
        return !$this->loanDetails()->whereNull('returned_at')->exists();
    }
}