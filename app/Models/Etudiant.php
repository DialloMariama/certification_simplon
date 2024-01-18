<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'paysOrigine',
        'universite',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
