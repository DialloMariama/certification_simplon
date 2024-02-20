<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proprietaire extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'actePropriete',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
    public function logements()
    {
        return $this->hasMany(Logement::class, 'user_id');
    }
    
}
