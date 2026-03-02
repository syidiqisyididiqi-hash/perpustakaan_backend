<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_detail_id',
        'overdue_days',
        'daily_rate',
        'total_fine',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function loanDetail()
    {
        return $this->belongsTo(LoanDetail::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    public function calculateTotal(): void
    {
        $this->total_fine = $this->overdue_days * $this->daily_rate;
    }
}