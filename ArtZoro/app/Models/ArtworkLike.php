<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkLike extends Model
{

    protected $fillable = ['user_id', 'artwork_id'];

    use HasFactory;
    public function artwork()
    {
        return $this->belongsTo(Artwork::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
