<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsItem extends Model
{
    protected $fillable = [
        'visit_id',
        'description',
        'quantity',
        'unit',
        'reference_doc',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }
}
