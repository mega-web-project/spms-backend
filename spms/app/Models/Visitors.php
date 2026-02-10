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
        'members',
        'image',
    ];

    protected $appends = ['image_url'];

    protected $hidden = ['image'];

    protected $casts = [
        'members' => 'array',
    ];

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        $path = ltrim($this->image, '/');
        return asset("storage/{$path}");
    }

}
