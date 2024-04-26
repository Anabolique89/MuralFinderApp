<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artwork extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'image_path', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(ArtworkLike::class);
    }

    public function comments()
    {
        return $this->hasMany(ArtworkComment::class);
    }

    public function images(){
        return $this->hasMany(ArtworkImage::class);
    }
}
