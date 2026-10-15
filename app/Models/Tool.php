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
        $this->belongsTo(Category::class);
    }
}
