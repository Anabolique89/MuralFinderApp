<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wall extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_text',
        'image_path',
        'added_by',
        'latitude',
        'longitude',
        'is_verified',
    ];

    public function artworks()
    {
        return $this->belongsToMany(Artwork::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function likes()
    {
        return $this->hasMany(WallLike::class);
    }


    public function comments()
    {
        return $this->hasMany(WallComment::class);
    }
}
