<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitors extends Model
{
    protected $fillable = [
        'full_name',
        'ID_number',
        'phone_number',
        'company',
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class, 'visitor_id');
    }
}