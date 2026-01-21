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
        // 'purpose_of_visit',
        // 'person_to_visit',
        // 'department',
        // 'additional_notes',
        // 'status',
        // 'check_in_time',
        // 'check_out_time'
    ];


}
