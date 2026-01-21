<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'visit_type',

        'visitor_id',
        'vehicle_id',
        'driver_id',

        //vehicle info
        'assigned_bay',

        //visitor info
        'person_to_visit',
        'department',
        'additional_notes',

        //common
        'purpose',
        'checked_in_at',
        'checked_out_at',
        'status',
        'has_discrepancies',

        // Check-out Verification
        'goods_verified',
        'weight_checked',
        'photo_documented',
        'notes',
    ];

    protected $casts=[
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitors::class, 'visitor_id');
    }

    public function goods_items()
    {
        return $this->hasMany(GoodsItem::class, 'visit_id');
    }
    public function vehicle()
    { 
        return $this->belongsTo(Vehicles::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
    
}
