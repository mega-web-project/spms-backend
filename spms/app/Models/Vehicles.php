<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drivers;

class Vehicles extends Model
{
    //
    use HasFactory;
    
    protected $fillable = [
        'driver_id',
        'image',
        'plate_number',
        'vehicle_type',
        'make',
        'model',
        'color',
        'company',
    ];

    public function driver()
    
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
}
