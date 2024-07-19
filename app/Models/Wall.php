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

    /**
     * The artworks that belong to the wall.
     */
    public function artworks()
    {
        return $this->belongsToMany(Artwork::class);
    }

    /**
     * Get the user who added the wall.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
