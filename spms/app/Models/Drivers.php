<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Vehicles;

class Drivers extends Model
{
    //

    protected $fillable = [
        'full_name',
        'company',
        'phone',
        'license_number',
        'address',
    ];
}
