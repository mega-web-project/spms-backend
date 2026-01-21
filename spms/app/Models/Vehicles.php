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

    public function visits()
    {
        return $this->hasMany(Visit::class, 'vehicle_id');
    }

        protected $appends = ['image_url'];

    protected $hidden = ['image'];

   public function getImageUrlAttribute()
{
    if (!$this->image) {
        return null; // or return default avatar URL if you want
    }

    // Remove any leading slashes from stored path
    $path = ltrim($this->image, '/');

    // Build proper URL
    return asset("storage/{$path}");
}
}
