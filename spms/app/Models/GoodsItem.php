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
        'has_discrepancy',
        'discrepancy_note',
    ];

    protected $casts = [
        'has_discrepancy' => 'boolean',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }
}
