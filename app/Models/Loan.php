<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'approved_by',
        'loan_date',
        'due_date',
        'returned_at',
        'fine_amount',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedLoan()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loan_details()
    {
        return $this->hasMany(LoanDetail::class);
    }
}
