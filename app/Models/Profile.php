<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image_url',
        "first_name",
        "last_name",
        "bio",
        "dob",
        "country",
        "proffession"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
