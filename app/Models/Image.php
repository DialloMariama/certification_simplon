<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'nomImage',
    ];
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }
}
