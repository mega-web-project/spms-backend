<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Drivers extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'full_name',
        'license_number',
        'phone',
        'company',
        'address',
    ];

    // IMPORTANT: only append image_url, NOT image
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

public function vehicles(){
    return $this->hasMany(Vehicle::class, 'driver_id');
}

public function visits(){
    return $this->hasMany(Visit::class);
}

}
