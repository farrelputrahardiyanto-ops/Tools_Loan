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
}
