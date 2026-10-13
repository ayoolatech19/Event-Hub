<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
      use HasFactory;  
    protected $fillable = ['name'];

Public function events()
    {
        return $this->hasMany(Event::class);
    }
}