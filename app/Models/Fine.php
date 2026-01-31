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
}
