<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Commentaire;

class Concour extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function comments()
    {
        return $this->hasMany(Commentaire::class);
    }
    
}

