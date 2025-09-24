<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkCategory extends Model
{
    use HasFactory;
    protected $fillable =[
        'name',
        'description'
    ];

    public function artwork(){
        return $this->hasMany(Artwork::class);
    }

}
