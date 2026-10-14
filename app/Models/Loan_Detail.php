<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan_Detail extends Model
{


    protected $fillable = [
        'loan_id',
        'tool_id',
        'qty'
    ];
}
