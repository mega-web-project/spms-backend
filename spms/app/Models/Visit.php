<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'purpose',
        'assigned_bay',
        'status',
        'check_in_at',
        'check_out_at',
        'goods_verified',
        'weight_checked',
        'photo_documented',
        'notes',
    ];

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
