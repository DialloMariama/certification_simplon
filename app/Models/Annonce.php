<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_id',
        'description',
        'budget',
        'caracteristiques',
        'prisEnCharge',
    ];
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
}
