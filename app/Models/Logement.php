<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
    use HasFactory;
    protected $fillable = [
        'adresse',
        'type',
        'prix',
        'description',
        'equipements',
        'localite_id',
        'disponibilite',
        'superficie',
        'nombreChambre',
        'image',


    ];

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

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }
}
