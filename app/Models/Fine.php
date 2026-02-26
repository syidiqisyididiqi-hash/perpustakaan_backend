<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_id',
        'rack_code',
        'overdue_days',
        'total_fine',
        'status',
    ];
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function getRackCodeAttribute($value)
    {
        if ($value)
            return $value;

        return $this->loan?->loanDetails?->first()?->book?->rack_code ?? '-';
    }

    public function getTotalFineAttribute($value)
    {
        if ($value)
            return $value;

        return $this->overdue_days * 5000;
    }
}
