<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
    use HasFactory;


    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class);
    }
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }
}
