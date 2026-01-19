<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Vehicles;

class Drivers extends Model
{
    //
    use HasFactory;
    
    protected $fillable = [
        'image',
        'full_name',
        'license_number',
        'phone',
        'company',
        'address',
    ];
}
