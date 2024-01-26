<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fellowship extends Model
{
    use HasFactory;

    protected $fillable = [
        'following_id',
        'follower_id',
    ];

    // Relationships
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
}
