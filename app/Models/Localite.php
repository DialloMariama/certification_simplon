<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomLocalite',
        'commune',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function logements()
    {
        return $this->hasMany(Logement::class);
    }
}
