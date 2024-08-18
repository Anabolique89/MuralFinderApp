<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WallLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'wall_id', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wall()
    {
        return $this->belongsTo(Wall::class);
    }

}
