<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $fillable = [
        'category_id',
        'type',
        'name',
        'photo',
        'description',
        'stock',
        'max_loan'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function Loan()
    {
        return $this->hasMany(Loan::class);
    }
}
